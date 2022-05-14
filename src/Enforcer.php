<?php

/**
 * Simple enforcer of plugins per environment type
 *
 * @package ThemePlate
 * @since   0.1.0
 */

namespace ThemePlate;

class Enforcer {

	private string $current_environment;
	private array $storage = array();


	public function __construct() {

		$this->current_environment = wp_get_environment_type();

		$this->storage[ $this->current_environment ] = array();

		add_filter( 'option_active_plugins', array( $this, 'maybe_insert_plugins' ) );
		add_filter( 'plugin_action_links', array( $this, 'maybe_hide_links' ), 10, 2 );

	}

	public function register( string $environment, string $plugin ): void {

		if ( empty( $this->storage[ $environment ] ) ) {
			$this->storage[ $environment ] = array();
		}

		if ( ! in_array( $plugin, $this->storage[ $environment ], true ) ) {
			$this->storage[ $environment ][] = $plugin;
		}

	}

	public function unregister( string $environment, string $plugin ): void {

		if ( empty( $this->storage[ $environment ] ) ) {
			return;
		}

		$index = array_search( $plugin, $this->storage[ $environment ], true );

		if ( false === $index ) {
			return;
		}

		unset( $this->storage[ $environment ][ $index ] );

	}


	public function maybe_insert_plugins( array $saved ): array {

		return array_merge( $saved, $this->storage[ $this->current_environment ] );

	}


	public function maybe_hide_links( array $displayed, string $plugin ): array {

		$registered_plugins = array_merge( ...array_values( $this->storage ) );

		if ( ! in_array( $plugin, $registered_plugins, true ) ) {
			return $displayed;
		}

		if ( in_array( $plugin, $this->storage[ $this->current_environment ], true ) ) {
			unset( $displayed['deactivate'] );
		}

		return $displayed;

	}

}
