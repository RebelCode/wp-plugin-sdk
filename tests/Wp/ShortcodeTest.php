<?php

namespace RebelCode\WpSdk\Tests\Wp;

use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\Shortcode;
use PHPUnit\Framework\TestCase;
use function Brain\Monkey\Functions\expect;

class ShortcodeTest extends TestCase
{
    use BrainMonkeyTest;

    public function testCtorShouldSetProperties()
    {
        $tag = 'foo';
        $callback = function () {
        };

        $shortcode = new Shortcode($tag, $callback);

        $this->assertSame($tag, $shortcode->tag);
        $this->assertSame($callback, $shortcode->callback);
    }

    public function testItCanRegister()
    {
        $tag = 'foo';
        $callback = function () {
        };

        $shortcode = new Shortcode($tag, $callback);

        expect('add_shortcode')->with($tag, $callback);

        $shortcode->register();
    }

    public function testItCanCreateAFactory()
    {
        $tag = 'my_shortcode';
        $callbackId = 'callback_fn';
        $callback = function () {
        };

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->once())->method('get')->with($callbackId)->willReturn($callback);

        $factory = Shortcode::factory($tag, $callbackId);
        $shortcode = $factory($c);

        $this->assertInstanceOf(Shortcode::class, $shortcode);
        $this->assertSame($tag, $shortcode->tag);
        $this->assertSame($callback, $shortcode->callback);
    }
}
