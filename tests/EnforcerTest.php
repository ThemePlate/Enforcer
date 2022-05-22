<?php

/**
 * @package ThemePlate
 */

namespace Tests;

use ThemePlate\Enforcer;
use WP_UnitTestCase;

class EnforcerTest extends WP_UnitTestCase {
	private Enforcer $enforcer;

	protected function setUp(): void {
		$this->enforcer = new Enforcer();

		$this->enforcer->init();
	}

	public function test_initial_storage_has_current_environment_empty_list(): void {
		$storage = $this->enforcer->dump();
		$wp_env  = wp_get_environment_type();

		$this->assertIsArray( $storage );
		$this->assertArrayHasKey( $wp_env, $storage );
		$this->assertSameSets(
			array(
				$wp_env => array(),
			),
			$storage
		);
	}

	public function test_firing_init_actually_add_hooks(): void {
		$enforcer = new Enforcer();

		$enforcer->init();

		$this->assertSame( 10, has_filter( 'option_active_plugins', array( $enforcer, 'maybe_insert_plugins' ) ) );
		$this->assertSame( 10, has_filter( 'plugin_action_links', array( $enforcer, 'maybe_hide_links' ) ) );
	}

	public function test_register_correctly_adds_to_wanted_environment_storage_list(): void {
		$wanted_environment = 'local';
		$wanted_plugin      = 'test/test.php';

		$this->enforcer->register( $wanted_environment, $wanted_plugin );

		$storage = $this->enforcer->dump();
		$wp_env  = wp_get_environment_type();

		$this->assertArrayHasKey( $wanted_environment, $storage );
		$this->assertIsArray( $storage[ $wanted_environment ] );
		$this->assertSameSets(
			array(
				$wp_env             => array(),
				$wanted_environment => array( $wanted_plugin ),
			),
			$storage
		);
	}

	public function test_unregister_with_unknown_plugin_does_nothing_to_initial_storage(): void {
		$wanted_environment = 'local';
		$wanted_plugin      = 'test/test.php';

		$this->enforcer->unregister( $wanted_environment, $wanted_plugin );
		$this->test_initial_storage_has_current_environment_empty_list();

		$storage = $this->enforcer->dump();

		$this->assertArrayNotHasKey( $wanted_environment, $storage );
	}

	public function test_unregister_with_unknown_plugin_does_nothing_to_current_environment_storage(): void {
		$wanted_environment = wp_get_environment_type();
		$wanted_plugin      = 'test/test.php';

		$this->enforcer->unregister( $wanted_environment, $wanted_plugin );
		$this->test_initial_storage_has_current_environment_empty_list();
	}

	public function test_unregister_correctly_removes_to_wanted_environment_storage_list(): void {
		$this->test_register_correctly_adds_to_wanted_environment_storage_list();

		$wanted_environment = 'local';
		$wanted_plugin      = 'test/test.php';

		$this->enforcer->unregister( $wanted_environment, $wanted_plugin );

		$storage = $this->enforcer->dump();
		$wp_env  = wp_get_environment_type();

		$this->assertSameSets(
			array(
				$wp_env             => array(),
				$wanted_environment => array(),
			),
			$storage
		);
	}

	public function for_maybe_insert_plugins(): array {
		return array(
			'ignored our registered plugins to incorrect environment' => array( 'development', true ),
			'has our registered plugins to correct environment'       => array( wp_get_environment_type(), false ),
		);
	}

	/**
	 * @dataProvider for_maybe_insert_plugins
	 */
	public function test_maybe_insert_plugins( string $wanted_environment, bool $is_empty ): void {
		$wanted_plugins = array(
			'try/try.php',
			'hard/hard.php',
		);

		$this->enforcer->load( array( $wanted_environment => $wanted_plugins ) );

		if ( $is_empty ) {
			$expected = array();
		} else {
			$expected = $wanted_plugins;
		}

		$this->assertSameSets(
			$expected,
			get_option( 'active_plugins' ),
		);
	}

	public function for_maybe_hide_links(): array {
		return array(
			'still has deactivate link if incorrect environment' => array(
				'staging',
				true,
				array(
					'one/one.php',
					'more/more.php',
				),
			),
			'dont have deactivate link if correct environment' => array(
				wp_get_environment_type(),
				false,
				array(
					'another/another.php',
					'chance/chance.php',
				),
			),
		);
	}

	/**
	 * @dataProvider for_maybe_hide_links
	 */
	public function test_maybe_hide_links( string $wanted_environment, bool $has_key, array $wanted_plugins ): void {
		$actions = array(
			'deactivate' => '',
			'activate'   => '',
			'details'    => '',
			'delete'     => '',
		);

		$this->enforcer->load( array( $wanted_environment => $wanted_plugins ) );

		foreach ( $wanted_plugins as $wanted_plugin ) {
			$output = apply_filters( 'plugin_action_links', $actions, $wanted_plugin );

			if ( $has_key ) {
				$this->assertArrayHasKey( 'deactivate', $output );
			} else {
				$this->assertArrayNotHasKey( 'deactivate', $output );
			}
		}
	}
}
