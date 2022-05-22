# ThemePlate Enforcer

## Usage

```php
use ThemePlate\Enforcer;

$enforcer = new Enforcer();

$enforcer->register( 'local', 'query-monitor/query-monitor.php' );
$enforcer->register( 'production', 'wordfence/wordfence.php' );


// or register all in one go
$plugins = array(
	'local' => array(
		'query-monitor/query-monitor.php',
	),
	'production' => array(
		'wordfence/wordfence.php',
	),
);

$enforcer->load( $plugins );


// set up the hooks
add_action( 'init', array( $enforcer, 'init' ) );
```
