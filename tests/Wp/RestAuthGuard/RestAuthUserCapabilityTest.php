<?php

namespace RebelCode\WpSdk\Tests\Wp\RestAuthGuard;

use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\WpTest;
use RebelCode\WpSdk\Wp\RestAuthGuard\RestAuthUserCapability;
use PHPUnit\Framework\TestCase;
use WP_REST_Request;
use function Brain\Monkey\Functions\expect;

class RestAuthUserCapabilityTest extends TestCase
{
    use WpTest;

    public static function setUpBeforeClass(): void
    {
        static::importWpRestApi();
    }

    public function testItShouldReturn401WhenUserNotLoggedIn()
    {
        $cap = 'access_my_endpoint';
        $auth = new RestAuthUserCapability($cap);

        expect('get_current_user_id')->once()->andReturn(0);

        $request = $this->createMock(WP_REST_Request::class);
        $error = $auth->getAuthError($request);

        $this->assertEquals(401, $error->status);
        $this->assertNotEmpty($error->reasons);
    }

    public function testItShouldReturn403WhenUserDoesntHaveCap()
    {
        $cap = 'access_my_endpoint';
        $auth = new RestAuthUserCapability($cap);

        expect('get_current_user_id')->once()->andReturn(123);
        expect('user_can')->once()->with(123, $cap)->andReturn(false);

        $request = $this->createMock(WP_REST_Request::class);
        $error = $auth->getAuthError($request);

        $this->assertEquals(403, $error->status);
        $this->assertNotEmpty($error->reasons);
    }

    public function testItShouldReturnNullWhenUserHasCap()
    {
        $cap = 'access_my_endpoint';
        $auth = new RestAuthUserCapability($cap);

        expect('get_current_user_id')->once()->andReturn(123);
        expect('user_can')->once()->with(123, $cap)->andReturn(true);

        $request = $this->createMock(WP_REST_Request::class);

        $this->assertNull($auth->getAuthError($request));
    }

    public function testItCanCreateAFactory()
    {
        $capability = 'access_my_endpoint';

        $factory = RestAuthUserCapability::factory($capability);
        $container = $this->createMock(ContainerInterface::class);

        $auth = $factory($container);

        $this->assertInstanceOf(RestAuthUserCapability::class, $auth);
        $this->assertEquals($capability, $auth->capability);
    }
}
