<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\WpTest;
use RebelCode\WpSdk\Wp\RestAuthError;
use RebelCode\WpSdk\Wp\RestAuthGuard;
use RebelCode\WpSdk\Wp\RestEndpoint;
use RebelCode\WpSdk\Wp\RestEndpointCallback;
use RebelCode\WpSdk\Wp\RestEndpointHandler;
use WP_REST_Request;
use function Brain\Monkey\Functions\expect;

class RestEndpointTest extends TestCase
{
    use WpTest;

    public static function setUpBeforeClass(): void
    {
        static::importWpRestApi();
    }

    public function testCtorShouldSetProperties()
    {
        $namespace = 'foo';
        $route = 'bar';
        $methods = ['GET', 'POST'];
        $handler = $this->createMock(RestEndpointHandler::class);
        $authHandler = $this->createMock(RestAuthGuard::class);

        $endpoint = new RestEndpoint($namespace, $route, $methods, $handler, $authHandler);

        $this->assertSame($namespace, $endpoint->namespace);
        $this->assertSame($route, $endpoint->route);
        $this->assertSame($methods, $endpoint->methods);
        $this->assertSame($handler, $endpoint->handler);
        $this->assertSame($authHandler, $endpoint->authHandler);
    }

    public function testItShouldCreateAFactory()
    {
        $namespace = 'foo';
        $route = 'bar';
        $methods = ['GET', 'POST'];
        $auth = $this->createMock(RestAuthGuard::class);

        $handlerClass = get_class(new class(0, 0) implements RestEndpointHandler {
            public $arg1;

            public $arg2;

            public function __construct($arg1, $arg2)
            {
                $this->arg1 = $arg1;
                $this->arg2 = $arg2;
            }

            public function handle(WP_REST_Request $request)
            {
            }
        });

        $factory = RestEndpoint::factory($namespace, $route, $methods, $handlerClass, ['one', 'two'], 'auth');

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->once())->method('get')->with('auth')->willReturn($auth);

        $endpoint = $factory($c);

        $this->assertSame($namespace, $endpoint->namespace);
        $this->assertSame($route, $endpoint->route);
        $this->assertSame($methods, $endpoint->methods);
        $this->assertInstanceOf($handlerClass, $endpoint->handler);
        $this->assertEquals('one', $endpoint->handler->arg1);
        $this->assertEquals('two', $endpoint->handler->arg2);
        $this->assertSame($auth, $endpoint->authHandler);
    }

    public function testItShouldGetTheCallback()
    {
        $endpoint = new RestEndpoint('foo', 'bar', ['GET'], $this->createMock(RestEndpointHandler::class));
        $callback = $endpoint->getCallback();

        $this->assertInstanceOf(RestEndpointCallback::class, $callback);
    }

    public function testItShouldCacheTheCallback()
    {
        $endpoint = new RestEndpoint('foo', 'bar', ['GET'], $this->createMock(RestEndpointHandler::class));

        $this->assertSame($endpoint->getCallback(), $endpoint->getCallback());
    }

    public function testItShouldGetThePermissionCallback()
    {
        $endpoint = new RestEndpoint(
            'foo',
            'bar',
            ['GET'],
            $this->createMock(RestEndpointHandler::class),
            $this->createMock(RestAuthGuard::class)
        );

        $callback = $endpoint->getCallback();

        $this->assertIsCallable($callback);
    }

    public function testItShouldCacheThePermissionCallback()
    {
        $endpoint = new RestEndpoint(
            'foo',
            'bar',
            ['GET'],
            $this->createMock(RestEndpointHandler::class),
            $this->createMock(RestAuthGuard::class)
        );

        $this->assertSame($endpoint->getPermissionCallback(), $endpoint->getPermissionCallback());
    }

    public function testItShouldRegister()
    {
        $namespace = 'foo';
        $route = 'bar';
        $methods = ['GET', 'POST'];
        $handler = $this->createMock(RestEndpointHandler::class);
        $authHandler = $this->createMock(RestAuthGuard::class);

        $endpoint = new RestEndpoint($namespace, $route, $methods, $handler, $authHandler);

        expect('register_rest_route')->once()->with($namespace, $route, [
            'methods' => $methods,
            'callback' => $endpoint->getCallback(),
            'permission_callback' => $endpoint->getPermissionCallback(),
        ])->andReturn(true);

        $this->assertTrue($endpoint->register());
    }

    public function testItShouldReturnTrueFnWithNoAuthGuard()
    {
        $callback = RestEndpoint::createPermissionCallback(null);

        $this->assertTrue($callback());
    }

    public function testItShouldReturnErrorFromAuthGuard()
    {
        $request = $this->createMock(WP_REST_Request::class);
        $guard = $this->createMock(RestAuthGuard::class);

        $error = new RestAuthError(401, ['Cannot find Waldo', 'Pigs cannot fly']);

        $guard->expects($this->once())->method('getAuthError')->with($request)->willReturn($error);

        $callback = RestEndpoint::createPermissionCallback($guard);
        $result = $callback($request);
        $data = $result->get_error_data();

        $this->assertEquals($error->reasons, $data['reasons']);
        $this->assertEquals($error->status, $data['status']);
        $this->assertEquals('unauthorized', $result->get_error_code());
    }

    public function testItShouldReturnTrueWhenAuthGuardReturnsNull()
    {
        $request = $this->createMock(WP_REST_Request::class);
        $guard = $this->createMock(RestAuthGuard::class);

        $guard->expects($this->once())->method('getAuthError')->with($request)->willReturn(null);

        $callback = RestEndpoint::createPermissionCallback($guard);
        $result = $callback($request);

        $this->assertTrue($result);
    }
}
