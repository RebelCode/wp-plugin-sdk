<?php

namespace RebelCode\WpSdk\Tests\Wp;

use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Tests\Helpers\WpTest;
use RebelCode\WpSdk\Wp\Role;
use PHPUnit\Framework\TestCase;
use WP_Role;
use function Brain\Monkey\Functions\expect;

class RoleTest extends TestCase
{
    use BrainMonkeyTest;
    use WpTest;

    public static function setUpBeforeClass(): void
    {
        static::importWpRoles();
    }

    public function testCtorShouldSetProperties()
    {
        $id = 'foo';
        $label = 'Foo';
        $capabilities = [
            'read' => true,
            'write' => false,
        ];

        $role = new Role($id, $label, $capabilities);

        $this->assertSame($id, $role->id);
        $this->assertSame($label, $role->label);
        $this->assertSame($capabilities, $role->capabilities);
    }

    public function testRegister()
    {
        $id = 'foo';
        $label = 'Foo';
        $capabilities = [
            'read' => true,
            'write' => false,
        ];

        $role = new Role($id, $label, $capabilities);
        $wpRole = $this->createMock(WP_Role::class);

        expect('add_role')->once()->with($id, $label, $capabilities)->andReturn($wpRole);

        $this->assertSame($wpRole, $role->register());
    }

    public function testUpdate()
    {
        $id = 'foo';
        $label = 'Foo';
        $capabilities = [
            'read' => true,
            'write' => false,
        ];

        $role = new Role($id, $label, $capabilities);
        $wpRole = $this->createMock(WP_Role::class);

        expect('remove_role')->once()->with($id);
        expect('add_role')->once()->with($id, $label, $capabilities)->andReturn($wpRole);

        $this->assertSame($wpRole, $role->update());
    }

    public function testItCanCreateAFactory()
    {
        $id = 'foo';
        $label = 'Foo';
        $capabilities = [
            'read' => true,
            'write' => false,
        ];

        $factory = Role::factory($id, $label, $capabilities);
        $container = $this->createMock(ContainerInterface::class);

        $role = $factory($container);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertSame($id, $role->id);
        $this->assertSame($label, $role->label);
        $this->assertSame($capabilities, $role->capabilities);
    }
}
