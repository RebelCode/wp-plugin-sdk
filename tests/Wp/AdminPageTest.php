<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\MockObject\MockBuilder;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Wp\AdminPage;
use PHPUnit\Framework\TestCase;
use function Brain\Monkey\Functions\expect;

class AdminPageTest extends TestCase
{
    public function testCtorShouldSetProperties()
    {
        $page = new AdminPage($title = 'Foo', $fn = function () {
            return 'Lorem ipsum dolor sit amet';
        });

        $this->assertSame($title, $page->title);
        $this->assertSame($fn, $page->renderFn);
    }

    public function testItCanRender()
    {
        $content = 'Lorem ipsum dolor sit amet';
        $printFn = 'test_print_fn';
        $args = ['one', 'two'];

        expect($printFn)->once()->with(...$args)->andReturn($content);

        $page = new AdminPage('Foo', $printFn);
        $result = $page->render(...$args);

        $this->assertEquals($content, $result);
    }

    public function testItCanReturnEchoFunction()
    {
        $content = 'Lorem ipsum dolor sit amet';
        $printFn = 'test_print_fn';
        $args = ['one', 'two'];

        expect($printFn)->once()->with(...$args)->andReturn($content);

        $page = new AdminPage('Foo', $printFn);
        $fn = $page->getEchoFn();
        $fn(null, 'one', 'two');

        $this->expectOutputString($content);
    }

    public function testItCanCreateAFactory()
    {
        $title = 'Foo';
        $renderId = 'render_fn';
        $renderFn = function () {
        };

        $factory = AdminPage::factory($title, $renderId);

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->once())->method('get')->with($renderId)->willReturn($renderFn);

        $page = $factory($c);

        $this->assertSame($title, $page->title);
        $this->assertSame($renderFn, $page->renderFn);
    }
}
