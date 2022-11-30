<?php

namespace RebelCode\WpSdk\Tests;

use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Handler;

class HandlerTest extends TestCase
{
    use BrainMonkeyTest;

    public function testItShouldAttachHandler()
    {
        $deps = ['dep1', 'dep2'];
        $function = function (...$args) {
            // do something
        };
        $priority = 10;
        $numArgs = null;

        $handler = new Handler($deps, $function, $priority, $numArgs);
        $c = $this->createMock(ContainerInterface::class);

        $handler->attach('foo', $c);

        $this->assertTrue(has_filter('foo'));
    }

    public function testItShouldCountParams()
    {
        $function = function ($one, $two, $three) {
            // do something
        };

        $handler = new Handler([], $function);

        $this->assertSame(3, $handler->countParams($function));
    }
}
