# WordPress Plugin SDK

An opinionated SDK for building modular WordPress plugins.

This is primarily built for RebelCode plugins. Feel free to use it for your own projects, but kindly be aware that we
may not accept pull requests that hinder our own development.

## Installation

Install with Composer:

```
composer require rebelcode/wp-plugin-sdk
```

## Usage

Full documentation can be found in the [docs](docs) directory.

Here's a quick example:

```php
/**   
 * @wordpress-plugin
 * Plugin Name: My Plugin
 * Version: 0.1
 */
 
use RebelCode\WpSdk\Plugin;
 
add_action('plugins_loaded', function () {
    $plugin = Plugin::create(__FILE__);
    $plugin->run();
})
```

```php
// modules.php
return [
    'shortcode' => new MyShortcodeModule(),
];
```

```php
// MyShortcodeModule.php
use Dhii\Services\Factories\FuncService;
use Dhii\Services\Extensions\ArrayExtension;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Module;
use RebelCode\WpSdk\Wp\Shortcode;

class MyShortcodeModule extends Module
{
    public function getFactories(): array
    {
        return [
            // Services for the [rain] shortcode and its render function
            'shortcode' => Shortcode::factory('rain', 'render_fn'),
            'render_fn' => new FuncService(function () {
                return 'The rain in Spain stays mainly in the plain';
            }),
        ];
    }
    
    public function getExtensions() : array{
        return [
            // Extend WordPress's shortcode list
            'wp/shortcodes' => new ArrayExtension(['shortcode']),
        ];
    }
}
```

## License

GPL-3.0+ © RebelCode
