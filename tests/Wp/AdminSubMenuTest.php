<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\AdminPage;
use RebelCode\WpSdk\Wp\AdminSubMenu;
use function Brain\Monkey\Functions\expect;

class AdminSubMenuTest extends TestCase
{
    use BrainMonkeyTest;

    protected function setUp(): void
    {
        parent::setUp();

        global $submenu;
        $submenu = [];
    }

    public function testItCanBeInstantiatedForPage()
    {
        $page = $this->createMock(AdminPage::class);
        $slug = 'test-menu';
        $label = 'Test Menu';
        $capability = 'manage_options';
        $position = 10;

        $instance = AdminSubMenu::forPage($page, $slug, $label, $capability, $position);

        $this->assertSame($page, $instance->page);
        $this->assertSame($slug, $instance->slug);
        $this->assertSame($label, $instance->label);
        $this->assertSame($capability, $instance->capability);
        $this->assertSame($position, $instance->position);
    }

    public function testItCanBeInstantiatedForUrl()
    {
        $url = 'https://example.com/test-menu';
        $label = 'Test Menu';
        $capability = 'manage_options';
        $position = 10;

        $instance = AdminSubMenu::forUrl($url, $label, $capability, $position);

        $this->assertSame($url, $instance->url);
        $this->assertSame($label, $instance->label);
        $this->assertSame($capability, $instance->capability);
        $this->assertSame($position, $instance->position);
    }

    public function testItCanRegisterForPage()
    {
        $printFn = function () {
        };
        $page = $this->createMock(AdminPage::class);
        $page->expects($this->once())->method('getEchoFn')->willReturn($printFn);

        $slug = 'test-menu';
        $label = 'Test Menu';
        $capability = 'manage_options';
        $position = 10;

        $instance = AdminSubMenu::forPage($page, $slug, $label, $capability, $position);
        $parentSlug = 'parent-menu';

        expect('current_user_can')->once()->with($capability)->andReturn(true);

        expect('add_submenu_page')
            ->once()->with($parentSlug, $page->title, $label, $capability, $slug, $printFn, $position);

        $instance->registerFor($parentSlug);
    }

    public function testItCanRegisterForUrl()
    {
        global $submenu;

        $url = 'https://example.com/test-menu';
        $label = 'Test Menu';
        $capability = 'manage_options';
        $position = 10;

        $instance = AdminSubMenu::forUrl($url, $label, $capability, $position);
        $parentSlug = 'parent-menu';

        expect('current_user_can')->once()->with($capability)->andReturn(true);

        $instance->registerFor($parentSlug);

        $this->assertNotEmpty($submenu);
        $this->assertEquals([$label, $capability, $url, $label], $submenu[$parentSlug][0]);
    }

    public function testItDoesntRegisterForPageWhenUserDoesntHaveCap()
    {
        $page = $this->createMock(AdminPage::class);

        $slug = 'test-menu';
        $label = 'Test Menu';
        $capability = 'manage_options';
        $position = 10;

        $instance = AdminSubMenu::forPage($page, $slug, $label, $capability, $position);
        $parentSlug = 'parent-menu';

        expect('current_user_can')->once()->with($capability)->andReturn(false);
        expect('add_submenu_page')->never();

        $instance->registerFor($parentSlug);
    }

    public function testItDoesntRegisterForUrlWhenUserDoesntHaveCap()
    {
        global $submenu;

        $url = 'https://example.com/test-menu';
        $label = 'Test Menu';
        $capability = 'manage_options';
        $position = 10;

        $instance = AdminSubMenu::forUrl($url, $label, $capability, $position);
        $parentSlug = 'parent-menu';

        expect('current_user_can')->once()->with($capability)->andReturn(false);
        expect('add_submenu_page')->never();

        $instance->registerFor($parentSlug);

        $this->assertEmpty($submenu);
    }

    public function testItCanCreateAFactoryForAPageSubMenu()
    {
        $pageId = 'my_page';
        $page = $this->createMock(AdminPage::class);
        $slug = 'my_menu';
        $label = 'My Menu';
        $capability = 'my_cap';
        $position = 10;

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->once())->method('get')->with($pageId)->willReturn($page);

        $factory = AdminSubMenu::factoryForPage($pageId, $slug, $label, $capability, $position);
        $submenu = $factory($c);

        $this->assertInstanceOf(AdminSubMenu::class, $submenu);
        $this->assertSame($page, $submenu->page);
        $this->assertSame($slug, $submenu->slug);
        $this->assertSame($label, $submenu->label);
        $this->assertSame($capability, $submenu->capability);
        $this->assertSame($position, $submenu->position);
    }

    public function testItCanCreateAFactoryForAUrlSubMenu()
    {
        $url = 'https://example.com/my-menu';
        $label = 'My Menu';
        $capability = 'my_cap';
        $position = 10;

        $factory = AdminSubMenu::factoryForUrl($url, $label, $capability, $position);
        $submenu = $factory($this->createMock(ContainerInterface::class));

        $this->assertInstanceOf(AdminSubMenu::class, $submenu);
        $this->assertSame($url, $submenu->url);
        $this->assertSame($label, $submenu->label);
        $this->assertSame($capability, $submenu->capability);
        $this->assertSame($position, $submenu->position);
    }
}
