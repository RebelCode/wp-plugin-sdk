<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\Notice;
use RebelCode\WpSdk\Wp\NoticeManager;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

class NoticeManagerTest extends TestCase
{
    use BrainMonkeyTest;

    public function testItShouldGetNotices()
    {
        $foo = $this->createMock(Notice::class);
        $foo->id = 'foo';

        $bar = $this->createMock(Notice::class);
        $bar->id = 'bar';

        $manager = new NoticeManager('', '', [$foo, $bar]);

        $this->assertSame($foo, $manager->get('foo'));
        $this->assertSame($bar, $manager->get('bar'));
    }

    public function testItShouldReturnNullIfNoticeDoesNotExist()
    {
        $manager = new NoticeManager('', '', []);

        $this->assertNull($manager->get('foo'));
    }

    public function testIsShouldBeAbleToAddNewNotices()
    {
        $manager = new NoticeManager('', '', []);

        $notice = new Notice(Notice::NONE, 'foo', '');
        $manager->add($notice);

        $this->assertSame($notice, $manager->get('foo'));
    }

    public function testItShouldAddAHookToShowANotice()
    {
        $notice = new Notice(Notice::NONE, 'foo', '');
        $manager = new NoticeManager('', '', [$notice]);

        $manager->show('foo');

        $this->assertTrue(has_action('admin_notices'));
    }

    public function testItShouldDismissANotice()
    {
        $notice = $this->createMock(Notice::class);
        $notice->expects($this->once())->method('dismiss');
        $notice->id = 'foo';

        $manager = new NoticeManager('', '', [$notice]);

        $manager->dismiss('foo');
    }

    public function testItShouldReturnScript()
    {
        expect('esc_attr')->atLeast()->once()->andReturnFirstArg();
        expect('admin_url')->once()->andReturn('');
        expect('wp_create_nonce')->once()->andReturn('123abc');

        $manager = new NoticeManager('', '', []);

        $script = $manager->getScript('my-notice');

        $this->assertStringStartsWith('<script type="text/javascript">', $script);
        $this->assertStringEndsWith('</script>', $script);
    }

    public function testItShouldHandleDismissAjax()
    {
        $notice = $this->createMock(Notice::class);
        $notice->expects($this->once())->method('dismiss');
        $notice->id = 'foo';

        $manager = new NoticeManager('test_nonce', 'test_ajax', [$notice]);

        expect('wp_verify_nonce')->once()->andReturn(true);
        when('__')->returnArg();
        when('status_header')->justReturn(true);

        $postRequest = [
            'notice' => 'foo',
            'nonce' => '123abc',
        ];

        $this->assertTrue($manager->handleAjax($postRequest));
    }

    public function testHandleAjaxShouldPrintErrorWhenTheNonceIsInvalid()
    {
        $notice = $this->createMock(Notice::class);
        $notice->expects($this->never())->method('dismiss');
        $notice->id = 'foo';

        $manager = new NoticeManager('test_nonce', 'test_ajax', [$notice]);

        when('__')->returnArg();
        expect('wp_verify_nonce')->once()->andReturn(false);
        expect('status_header')->once()->with(400);

        $postRequest = [
            'notice' => 'foo',
            'nonce' => '123abc',
        ];

        $this->assertFalse($manager->handleAjax($postRequest));
        $this->expectOutputString('Invalid nonce');
    }

    public function testHandleAjaxShouldPrintErrorWhenTheNoticeDoesNotExist()
    {
        $notice = $this->createMock(Notice::class);
        $notice->expects($this->never())->method('dismiss');
        $notice->id = 'foo';

        $manager = new NoticeManager('test_nonce', 'test_ajax', [$notice]);

        when('__')->returnArg();
        expect('wp_verify_nonce')->once()->andReturn(true);
        expect('status_header')->once()->with(400);

        $postRequest = [
            'notice' => 'bar',
            'nonce' => '123abc',
        ];

        $this->assertFalse($manager->handleAjax($postRequest));
        $this->expectOutputString('Invalid notice ID');
    }
}
