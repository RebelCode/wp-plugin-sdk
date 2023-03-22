<?php

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Module;
use RebelCode\WpSdk\Plugin;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use function Brain\Monkey\Functions\expect;

class PluginTest extends TestCase
{
    use BrainMonkeyTest;

    public function testStandardUsage()
    {
        $pluginFile = __DIR__ . '/Stubs/plugin.php';

        expect('get_plugin_data')->once()->andReturn([]);
        expect('plugin_dir_url')->once()->with($pluginFile)->andReturn('/not/important');
        expect('register_activation_hook')->once()->with($pluginFile, Mockery::type('callable'));
        expect('register_deactivation_hook')->once()->with($pluginFile, Mockery::type('callable'));

        $this->expectOutputString('baz');

        $plugin = Plugin::create(__DIR__ . '/Stubs/plugin.php');
        $plugin->run();

        $this->assertEquals('baz', $plugin->get('foo/bar'));
    }

    public function testCustomDelimiter()
    {
        $pluginFile = __DIR__ . '/Stubs/plugin.php';

        expect('plugin_dir_url')->once()->with($pluginFile)->andReturn('/not/important');

        $plugin = Plugin::create(__DIR__ . '/Stubs/plugin.php', '.');
        $this->assertEquals('baz', $plugin->get('foo.bar'));
    }

    public function testProxiesGetToContainer()
    {
        $c = $this->createMock(ContainerInterface::class);
        $plugin = new Plugin(__DIR__, $c, []);

        $c->expects($this->once())->method('get')->with('foo')->willReturn('bar');

        $this->assertEquals('bar', $plugin->get('foo'));
    }

    public function testProxiesHasToContainer()
    {
        $c = $this->createMock(ContainerInterface::class);
        $plugin = new Plugin(__DIR__, $c, []);

        $c->expects($this->once())->method('has')->with('foo')->willReturn(true);

        $this->assertTrue($plugin->has('foo'));
    }

    public function testRunsModules()
    {
        $c = $this->createMock(ContainerInterface::class);
        $modules = [
            $this->createMock(Module::class),
            $this->createMock(Module::class),
            $this->createMock(Module::class),
        ];

        $plugin = new Plugin(__DIR__, $c, $modules);

        foreach ($modules as $module) {
            $module->expects($this->once())->method('run')->with($c, $plugin);
        }

        $plugin->run();
    }
}
