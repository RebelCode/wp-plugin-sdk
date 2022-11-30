<?php

namespace RebelCode\WpSdk\Tests\Wp\RestAuthGuard;

use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\WpTest;
use RebelCode\WpSdk\Wp\RestAuthGuard\RestAuthNonce;
use PHPUnit\Framework\TestCase;
use WP_REST_Request;
use function Brain\Monkey\Functions\expect;

class RestAuthNonceTest extends TestCase
{
    use WpTest;

    public static function setUpBeforeClass(): void
    {
        static::importWpRestApi();
    }

    public function testItShouldReturnErrorForInvalidNonce()
    {
        $nonceAction = 'my-nonce-action';
        $nonceParam = 'my-nonce-param';
        $nonceValue = '123abc';

        $auth = new RestAuthNonce($nonceAction, $nonceParam);

        $request = $this->createMock(WP_REST_Request::class);
        $request->expects($this->once())->method('get_param')->with($nonceParam)->willReturn($nonceValue);

        expect('wp_verify_nonce')->once()->with($nonceValue, $nonceAction)->andReturn(false);

        $error = $auth->getAuthError($request);

        $this->assertNotNull($error);
        $this->assertEquals(403, $error->status);
        $this->assertCount(1, $error->reasons);
    }

    public function testItShouldReturnNullForValidNonce()
    {
        $nonceAction = 'my-nonce-action';
        $nonceParam = 'my-nonce-param';
        $nonceValue = '123abc';

        $auth = new RestAuthNonce($nonceAction, $nonceParam);

        $request = $this->createMock(WP_REST_Request::class);
        $request->expects($this->once())->method('get_param')->with($nonceParam)->willReturn($nonceValue);

        expect('wp_verify_nonce')->once()->with($nonceValue, $nonceAction)->andReturn(true);

        $this->assertNull($auth->getAuthError($request));
    }

    public function testItCanCreateAFactory()
    {
        $nonceAction = 'my-nonce-action';
        $nonceParam = 'my-nonce-param';

        $factory = RestAuthNonce::factory($nonceAction, $nonceParam);
        $container = $this->createMock(ContainerInterface::class);

        $auth = $factory($container);

        $this->assertInstanceOf(RestAuthNonce::class, $auth);
        $this->assertEquals($nonceAction, $auth->nonceAction);
        $this->assertEquals($nonceParam, $auth->nonceParam);
    }
}
