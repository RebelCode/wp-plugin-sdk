<?php

namespace Wp;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\Asset;
use RebelCode\WpSdk\Wp\Style;
use function Brain\Monkey\Functions\expect;

class StyleTest extends TestCase
{
    use BrainMonkeyTest;

    public function testItExtendsAsset()
    {
        $this->assertInstanceOf(Asset::class, new Style('foo', 'bar'));
    }

    public function testCtorShouldSetProperties()
    {
        $handle = 'my-style';
        $url = 'path/to/script';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];

        $style = new Style($handle, $url, $ver, $deps);

        $this->assertSame($handle, $style->id);
        $this->assertSame($url, $style->url);
        $this->assertSame($ver, $style->version);
        $this->assertSame($deps, $style->deps);
    }

    public function testItCanRegister()
    {
        $handle = 'my-style';
        $url = 'https://example.com/style.css';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];

        $style = new Style($handle, $url, $ver, $deps);

        expect('wp_register_style')->once()->with($handle, $url, $deps, $ver)->andReturn(true);

        $this->assertTrue($style->register());
        $this->assertTrue($style->isRegistered);
    }

    public function testItCanEnqueue()
    {
        $handle = 'my-style';
        $url = 'https://example.com/style.css';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];

        $style = new Style($handle, $url, $ver, $deps);

        expect('wp_register_style')->once()->with($handle, $url, $deps, $ver)->andReturn(true);
        expect('wp_enqueue_style')->once()->with($handle);

        $style->enqueue();
    }

    public function testItCanCreateFactory()
    {
        $handle = 'my-style';
        $url = 'path/to/style';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];
        $factory = Style::factory($handle, $url, $ver, $deps);

        $pluginUrl = 'url/to/plugin/';
        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->once())->method('get')->with('@plugin/dir_url')->willReturn($pluginUrl);

        $style = $factory($c);

        $this->assertSame($handle, $style->id);
        $this->assertSame($pluginUrl . $url, $style->url);
        $this->assertSame($ver, $style->version);
        $this->assertSame($deps, $style->deps);
    }
}
