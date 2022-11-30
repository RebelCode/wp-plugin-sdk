<?php

namespace Wp;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\Asset;
use RebelCode\WpSdk\Wp\Script;
use RebelCode\WpSdk\Wp\ScriptL10n;
use function Brain\Monkey\Functions\expect;

class ScriptTest extends TestCase
{
    use BrainMonkeyTest;

    public function testItExtendsAsset()
    {
        $this->assertInstanceOf(Asset::class, new Script('foo', 'bar'));
    }

    public function testCtorShouldSetProperties()
    {
        $handle = 'my-script';
        $url = 'path/to/script';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];
        $l10n = new ScriptL10n('Foo', [
            'bar' => 'baz',
            'qux' => 'quux',
        ]);

        $script = new Script($handle, $url, $ver, $deps, $l10n);

        $this->assertSame($handle, $script->id);
        $this->assertSame($url, $script->url);
        $this->assertSame($ver, $script->version);
        $this->assertSame($deps, $script->deps);
        $this->assertSame($l10n, $script->l10n);
    }

    public function testItCanRegister()
    {
        $handle = 'my-script';
        $url = 'https://example.com/script.js';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];

        $script = new Script($handle, $url, $ver, $deps);

        expect('wp_register_script')->once()->with($handle, $url, $deps, $ver, true)->andReturn(true);

        $this->assertTrue($script->register());
        $this->assertTrue($script->isRegistered);
    }

    public function testItCanRegisterWithL10n()
    {
        $handle = 'my-script';
        $url = 'https://example.com/script.js';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];
        $l10n = new ScriptL10n('Foo', [
            'bar' => 'baz',
            'qux' => 'quux',
        ]);

        $script = new Script($handle, $url, $ver, $deps, $l10n);

        expect('wp_register_script')->once()->with($handle, $url, $deps, $ver, true)->andReturn(true);
        expect('wp_localize_script')->once()->with($handle, $l10n->name, $l10n->data)->andReturn(true);

        $this->assertTrue($script->register());
        $this->assertTrue($script->isRegistered);
    }

    public function testItCanEnqueue()
    {
        $handle = 'my-script';
        $url = 'https://example.com/script.js';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];

        $script = new Script($handle, $url, $ver, $deps);

        expect('wp_register_script')->once()->with($handle, $url, $deps, $ver, true)->andReturn(true);
        expect('wp_enqueue_script')->once()->with($handle);

        $script->enqueue();
    }

    public function testItCanEnqueueWithL10n()
    {
        $handle = 'my-script';
        $url = 'https://example.com/script.js';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];
        $l10n = new ScriptL10n('Foo', [
            'bar' => 'baz',
            'qux' => 'quux',
        ]);

        $script = new Script($handle, $url, $ver, $deps, $l10n);

        expect('wp_register_script')->once()->with($handle, $url, $deps, $ver, true)->andReturn(true);
        expect('wp_localize_script')->once()->with($handle, $l10n->name, $l10n->data)->andReturn(true);
        expect('wp_enqueue_script')->once()->with($handle);

        $script->enqueue();
    }

    public function testItCanCreateFactory()
    {
        $handle = 'my-script';
        $url = 'path/to/script';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];
        $factory = Script::factory($handle, $url, $ver, $deps);

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->once())->method('get')->with('@plugin/dir_url')->willReturn('base/url/');

        $asset = $factory($c);

        $this->assertSame($handle, $asset->id);
        $this->assertSame('base/url/' . $url, $asset->url);
        $this->assertSame($ver, $asset->version);
        $this->assertSame($deps, $asset->deps);
    }

    public function testItCanCreateFactoryWithL10n()
    {
        $handle = 'my-script';
        $url = 'path/to/script';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];
        $factory = Script::factory($handle, $url, $ver, $deps);

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->once())->method('get')->with('@plugin/dir_url')->willReturn('base/url/');

        $asset = $factory($c);

        $this->assertSame($handle, $asset->id);
        $this->assertSame('base/url/' . $url, $asset->url);
        $this->assertSame($ver, $asset->version);
        $this->assertSame($deps, $asset->deps);
    }
}
