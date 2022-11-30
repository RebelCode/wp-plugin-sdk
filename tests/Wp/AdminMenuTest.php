<?php

namespace RebelCode\WpSdk\Tests\Wp;

use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Wp\AdminPage;
use RebelCode\WpSdk\Wp\AdminMenu;
use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Wp\AdminSubMenu;
use function Brain\Monkey\Functions\expect;

class AdminMenuTest extends TestCase
{
    public function testCtorShouldSetProperties()
    {
        $page = $this->createMock(AdminPage::class);
        $slug = 'test-menu';
        $label = 'Test Menu';
        $capability = 'manage_options';
        $icon = 'dashicons-test';
        $position = 1;
        $items = [
            new AdminSubMenu(),
        ];

        $menu = new AdminMenu($page, $slug, $label, $capability, $icon, $position, $items);

        $this->assertSame($page, $menu->page);
        $this->assertSame($slug, $menu->slug);
        $this->assertSame($label, $menu->label);
        $this->assertSame($capability, $menu->capability);
        $this->assertSame($icon, $menu->icon);
        $this->assertSame($position, $menu->position);
        $this->assertSame($items, $menu->items);
    }

    public function testItCanBeRegistered()
    {
        $printFn = function () {
        };
        $page = $this->createMock(AdminPage::class);
        $page->title = 'Test Page';
        $page->expects($this->once())->method('getEchoFn')->willReturn($printFn);

        $slug = 'test-menu';
        $label = 'Test Menu';
        $capability = 'manage_options';
        $icon = 'dashicons-test';
        $position = 10;
        $items = [
            $item1 = $this->createMock(AdminSubMenu::class),
            $item2 = $this->createMock(AdminSubMenu::class),
        ];

        $menu = new AdminMenu($page, $slug, $label, $capability, $icon, $position, $items);

        expect('current_user_can')->once()->with($capability)->andReturn(true);
        expect('add_menu_page')->once()->with($page->title, $label, $capability, $slug, $printFn, $icon, $position);

        $item1->expects($this->once())->method('registerFor')->with($slug);
        $item2->expects($this->once())->method('registerFor')->with($slug);

        $menu->register();
    }

    public function testItShouldNotBeRegisteredWhenUserDoesntHaveCap()
    {
        $page = $this->createMock(AdminPage::class);
        $page->title = 'Test Page';

        $slug = 'test-menu';
        $label = 'Test Menu';
        $capability = 'manage_options';
        $icon = 'dashicons-test';
        $position = 10;
        $items = [
            $item1 = $this->createMock(AdminSubMenu::class),
            $item2 = $this->createMock(AdminSubMenu::class),
        ];

        $menu = new AdminMenu($page, $slug, $label, $capability, $icon, $position, $items);

        expect('current_user_can')->once()->with($capability)->andReturn(false);
        expect('add_menu_page')->never();

        $item1->expects($this->never())->method('registerFor');
        $item2->expects($this->never())->method('registerFor');

        $menu->register();
    }

    public function testItShouldAddSubMenuItem()
    {
        $page = $this->createMock(AdminPage::class);
        $menu = new AdminMenu($page, '', '', '', '');

        $submenu = AdminSubMenu::forUrl('https://example.com', 'Test Submenu', 'capability');
        $menu->addSubMenu($submenu);

        $this->assertEquals([$submenu], $menu->items);
    }

    public function testItCanCreateAFactory()
    {
        $pageId = 'test-page';
        $page = $this->createMock(AdminPage::class);
        $slug = 'test-menu';
        $label = 'Test Menu';
        $capability = 'manage_options';
        $icon = 'dashicons-test';
        $position = 1;
        $itemsIds = [
            'item_1',
            'item_2',
        ];
        $items = [
            new AdminSubMenu(),
            new AdminSubMenu(),
        ];

        $factory = AdminMenu::factory($pageId, $slug, $label, $capability, $icon, $position, $itemsIds);

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->exactly(3))
          ->method('get')
          ->withConsecutive([$pageId], [$itemsIds[0]], [$itemsIds[1]])
          ->willReturnOnConsecutiveCalls($page, $items[0], $items[1]);

        $menu = $factory($c);

        $this->assertSame($page, $menu->page);
        $this->assertSame($slug, $menu->slug);
        $this->assertSame($label, $menu->label);
        $this->assertSame($capability, $menu->capability);
        $this->assertSame($icon, $menu->icon);
        $this->assertSame($position, $menu->position);
        $this->assertSame($items, $menu->items);
    }
}
