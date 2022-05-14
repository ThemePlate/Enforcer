# ThemePlate Enforcer

## Usage

```php
use ThemePlate\Enforcer;

$enforcer = new Enforcer();

$enforcer->register( 'local', 'query-monitor/query-monitor.php' );
$enforcer->register( 'production', 'wordfence/wordfence.php' );
```
