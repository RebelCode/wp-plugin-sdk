<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\AbstractOption;
use RebelCode\WpSdk\Wp\Notice;
use function Brain\Monkey\Functions\expect;

class NoticeTest extends TestCase
{
    use BrainMonkeyTest;

    public function expectWpFunctionsForRender()
    {
        expect('esc_attr')->atLeast()->once()->andReturnFirstArg();
        expect('wpautop')->atLeast()->once()->andReturnFirstArg();
    }

    public function provideRenderTypeData(): array
    {
        return [
            Notice::INFO => [Notice::INFO],
            Notice::ERROR => [Notice::ERROR],
            Notice::SUCCESS => [Notice::SUCCESS],
            Notice::WARNING => [Notice::WARNING],
        ];
    }

    public function testCtorShouldSetProperties()
    {
        $type = Notice::INFO;
        $id = 'foo';
        $content = 'Lorem ipsum dolor sit amet';
        $isDismissible = false;
        $option = $this->createMock(AbstractOption::class);

        $notice = new Notice($type, $id, $content, $isDismissible, $option);

        $this->assertSame($type, $notice->type);
        $this->assertSame($id, $notice->id);
        $this->assertSame($content, $notice->content);
        $this->assertSame($isDismissible, $notice->isDismissible);
        $this->assertSame($option, $notice->option);
    }

    /** @dataProvider provideRenderTypeData */
    public function testItShouldRenderNonDismissible($type)
    {
        $this->expectWpFunctionsForRender();

        $notice = new Notice($type, 'foo', 'hello world');
        $expected = '<div id="foo" class="notice notice-' . $type . '">hello world</div>';

        $this->assertEquals($expected, $notice->render());
    }

    /** @dataProvider provideRenderTypeData */
    public function testItShouldRenderDismissible($type)
    {
        $this->expectWpFunctionsForRender();

        $notice = new Notice($type, 'foo', 'hello world', true);
        $expected = '<div id="foo" class="notice notice-' . $type . ' is-dismissible">hello world</div>';

        $this->assertEquals($expected, $notice->render());
    }

    /** @dataProvider provideRenderTypeData */
    public function testItShouldRenderWithNoType()
    {
        $this->expectWpFunctionsForRender();

        $notice = new Notice(Notice::NONE, 'foo', 'hello world');
        $expected = '<div id="foo" class="notice">hello world</div>';

        $this->assertEquals($expected, $notice->render());
    }

    public function testItShouldReturnAnEchoFunction()
    {
        $this->expectWpFunctionsForRender();

        $notice = new Notice(Notice::SUCCESS, 'foo', 'hello world');
        $expected = '<div id="foo" class="notice notice-success">hello world</div>';

        $fn = $notice->getEchoFn();
        $fn();

        $this->expectOutputString($expected);
    }

    public function testItShouldUpdateItsOptionWhenDismissed()
    {
        $option = $this->createMock(AbstractOption::class);
        $option->expects($this->once())->method('setValue')->with(true);

        $notice = new Notice(Notice::NONE, 'foo', '', true, $option);
        $notice->dismiss();
    }

    public function testItShouldNotUpdateItsOptionWhenNotDismissible()
    {
        $option = $this->createMock(AbstractOption::class);
        $option->expects($this->never())->method('setValue');

        $notice = new Notice(Notice::NONE, 'foo', '', false, $option);
        $notice->dismiss();
    }

    public function testItCanCreateAnInfoNotice()
    {
        $id = 'foo';
        $content = 'Lorem ipsum dolor sit amet';
        $option = $this->createMock(AbstractOption::class);

        $notice = Notice::info($id, $content, true, $option);

        $this->assertInstanceOf(Notice::class, $notice);
        $this->assertSame(Notice::INFO, $notice->type);
        $this->assertSame($id, $notice->id);
        $this->assertSame($content, $notice->content);
        $this->assertTrue($notice->isDismissible);
        $this->assertSame($option, $notice->option);
    }

    public function testItCanCreateASuccessNotice()
    {
        $id = 'foo';
        $content = 'Lorem ipsum dolor sit amet';
        $option = $this->createMock(AbstractOption::class);

        $notice = Notice::success($id, $content, true, $option);

        $this->assertInstanceOf(Notice::class, $notice);
        $this->assertSame(Notice::SUCCESS, $notice->type);
        $this->assertSame($id, $notice->id);
        $this->assertSame($content, $notice->content);
        $this->assertTrue($notice->isDismissible);
        $this->assertSame($option, $notice->option);
    }

    public function testItCanCreateAWarningNotice()
    {
        $id = 'foo';
        $content = 'Lorem ipsum dolor sit amet';
        $option = $this->createMock(AbstractOption::class);

        $notice = Notice::warning($id, $content, true, $option);

        $this->assertInstanceOf(Notice::class, $notice);
        $this->assertSame(Notice::WARNING, $notice->type);
        $this->assertSame($id, $notice->id);
        $this->assertSame($content, $notice->content);
        $this->assertTrue($notice->isDismissible);
        $this->assertSame($option, $notice->option);
    }

    public function testItCanCreateAnErrorNotice()
    {
        $id = 'foo';
        $content = 'Lorem ipsum dolor sit amet';
        $option = $this->createMock(AbstractOption::class);

        $notice = Notice::error($id, $content, true, $option);

        $this->assertInstanceOf(Notice::class, $notice);
        $this->assertSame(Notice::ERROR, $notice->type);
        $this->assertSame($id, $notice->id);
        $this->assertSame($content, $notice->content);
        $this->assertTrue($notice->isDismissible);
        $this->assertSame($option, $notice->option);
    }

    public function testItCanCreateAFactory()
    {
        $type = Notice::INFO;
        $id = 'foo';
        $content = 'Lorem ipsum dolor sit amet';
        $isDismissible = false;
        $optionId = 'my-option';
        $option = $this->createMock(AbstractOption::class);

        $factory = Notice::factory($type, $id, $content, $isDismissible, $optionId);

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->once())->method('get')->with($optionId)->willReturn($option);

        $notice = $factory($c);

        $this->assertSame($type, $notice->type);
        $this->assertSame($id, $notice->id);
        $this->assertSame($content, $notice->content);
        $this->assertSame($isDismissible, $notice->isDismissible);
        $this->assertSame($option, $notice->option);
    }

    public function testItCanCreateAFactoryWithoutAnOption()
    {
        $type = Notice::INFO;
        $id = 'foo';
        $content = 'Lorem ipsum dolor sit amet';
        $isDismissible = false;

        $factory = Notice::factory($type, $id, $content, $isDismissible);

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->never())->method('get');

        $notice = $factory($c);

        $this->assertSame($type, $notice->type);
        $this->assertSame($id, $notice->id);
        $this->assertSame($content, $notice->content);
        $this->assertSame($isDismissible, $notice->isDismissible);
        $this->assertNull($notice->option);
    }
}
