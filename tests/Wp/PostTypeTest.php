<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\PostType;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

class PostTypeTest extends TestCase
{
    use BrainMonkeyTest;

    public function testCtorShouldSetProperties()
    {
        $postType = new PostType('foo', $args = [
            'label' => 'Foo',
            'description' => 'Foo description',
            'public' => true,
            'rest_base' => 'bar',
        ]);

        $this->assertSame('foo', $postType->slug);
        $this->assertSame($args, $postType->args);
    }

    public function testItShouldAddArgs()
    {
        $postType = new PostType('dog', $argsBefore = [
            'label' => 'Dog',
            'description' => 'Canine creature',
            'public' => true,
            'rest_base' => 'woof',
        ]);

        $argsToAdd = [
            'description' => 'Domestic wolf',
            'show_ui' => true,
            'menu_position' => 15,
        ];

        $result = $postType->withAddedArgs($argsToAdd);
        $expected = array_merge($argsBefore, $argsToAdd);

        $this->assertNotSame($postType, $result);
        $this->assertEquals($expected, $result->args);
    }

    public function testItShouldGenerateLabels()
    {
        $postType = new PostType('dog', $argsBefore = [
            'label' => 'Dog',
            'description' => 'Canine creature',
            'public' => true,
            'rest_base' => 'woof',
        ]);

        when('__')->returnArg();
        when('_x')->returnArg();

        $result = $postType->withAutoLabels('Dog', 'Dogs');

        $this->assertNotSame($result, $postType);
        $this->assertEquals($argsBefore['label'], $result->args['label']);
        $this->assertEquals($argsBefore['description'], $result->args['description']);
        $this->assertEquals($argsBefore['public'], $result->args['public']);
        $this->assertEquals($argsBefore['rest_base'], $result->args['rest_base']);

        $expectedLabels = [
            'name' => 'Dogs',
            'singular_name' => 'Dog',
            'menu_name' => 'Dogs',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Dog',
            'edit_item' => 'Edit Dog',
            'new_item' => 'New Dog',
            'view_item' => 'View Dog',
            'view_items' => 'View Dogs',
            'search_items' => 'Search Dogs',
            'not_found' => 'No dogs found',
            'not_found_in_trash' => 'No dogs found in Trash',
            'parent_item_colon' => 'Parent Dog:',
            'all_items' => 'All Dogs',
            'archives' => 'Dog Archives',
            'attributes' => 'Dog Attributes',
            'insert_into_item' => 'Insert into dog',
            'uploaded_to_this_item' => 'Uploaded to this dog',
            'featured_image' => 'Featured Image',
            'set_featured_image' => 'Set featured image',
            'remove_featured_image' => 'Remove featured image',
            'use_featured_image' => 'Use as featured image',
            'filter_items_list' => 'Filter dogs list',
            'filter_by_date' => 'Filter by date',
            'items_list_navigation' => 'Dogs list navigation',
            'items_list' => 'Dogs list',
            'item_published' => 'Dog published.',
            'item_published_privately' => 'Dog published privately.',
            'item_reverted_to_draft' => 'Dog reverted to draft.',
            'item_scheduled' => 'Dog scheduled.',
            'item_updated' => 'Dog updated.',
            'item_link' => 'Dog link',
            'item_link_description' => 'A link to a Dog',
        ];

        $this->assertEquals($expectedLabels, $result->args['labels']);
    }

    public function testItShouldCopyWithRestApiArgs()
    {
        $postType = new PostType('book', $argsBefore = [
            'public' => true,
            'show_in_ui' => true,
            'menu_position' => 15,
        ]);

        $argsToAdd = [
            'show_in_rest' => true,
            'rest_base' => ($base = 'paper'),
            'rest_namespace' => ($ns = 'my_plugin'),
            'rest_controller_class' => ($controller = 'MyPlugin\\Controllers\\BookController'),
        ];

        $result = $postType->withRestApi($base, $ns, $controller);
        $expected = array_merge($argsBefore, $argsToAdd);

        $this->assertNotSame($postType, $result);
        $this->assertEquals($expected, $result->args);
    }

    public function testItShouldCopyWithNoUi()
    {
        $postType = new PostType('book', $argsBefore = [
            'public' => true,
            'label' => 'Book',
            'menu_position' => 15,
        ]);

        $argsToAdd = [
            'public' => false,
            'has_archive' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => false,
        ];

        $result = $postType->withNoUi();
        $expected = array_merge($argsBefore, $argsToAdd);

        $this->assertNotSame($postType, $result);
        $this->assertEquals($expected, $result->args);
    }

    public function testItShouldCopyWithOnlyAdminUi()
    {
        $postType = new PostType('book', $argsBefore = [
            'public' => true,
            'label' => 'Book',
            'menu_position' => 15,
        ]);

        $argsToAdd = [
            'public' => false,
            'has_archive' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_ui' => true,
            'show_in_menu' => ($showInMenu = true),
            'show_in_admin_bar' => ($showInAdminBar = true),
            'show_in_nav_menus' => ($showNavMenus = false),
        ];

        $result = $postType->withAdminUiOnly($showInMenu, $showInAdminBar, $showNavMenus);
        $expected = array_merge($argsBefore, $argsToAdd);

        $this->assertNotSame($postType, $result);
        $this->assertEquals($expected, $result->args);
    }

    public function testItShouldRegister()
    {
        $postType = new PostType('dog', $args = [
            'label' => 'Dog',
            'description' => 'Canine creature',
            'public' => true,
            'rest_base' => 'woof',
        ]);

        expect('register_post_type')->once()->with('dog', $args);

        $postType->register();
    }

    public function testItShouldCreateFactory()
    {
        $factory = PostType::factory('dog', $args = [
            'label' => 'Dog',
            'description' => 'Canine creature',
            'public' => true,
            'rest_base' => 'woof',
        ]);

        $container = $this->createMock(ContainerInterface::class);
        $postType = $factory($container);

        $this->assertInstanceOf(PostType::class, $postType);
        $this->assertEquals('dog', $postType->slug);
        $this->assertEquals($args, $postType->args);
    }
}
