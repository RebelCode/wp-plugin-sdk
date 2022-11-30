<?php

namespace RebelCode\WpSdk\Tests\Wp;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Tests\Helpers\WpTest;
use RebelCode\WpSdk\Wp\RestEndpointCallback;
use RebelCode\WpSdk\Wp\RestEndpointHandler;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class RestEndpointCallbackTest extends TestCase
{
    use WpTest;

    public static function setUpBeforeClass(): void
    {
        static::importWpRestApi();
    }

    public function testItShouldReturnResponse()
    {
        $request = $this->createMock(WP_REST_Request::class);
        $response = new WP_REST_Response(['foo' => 'bar']);

        $handler = $this->createMock(RestEndpointHandler::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($response);

        $callback = new RestEndpointCallback($handler);

        $this->assertSame($response, $callback($request));
    }

    public function testItShouldCastResponseDataToArray()
    {
        $request = $this->createMock(WP_REST_Request::class);
        $response = new WP_REST_Response((object) $data = ['foo' => 'bar']);

        $handler = $this->createMock(RestEndpointHandler::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($response);

        $callback = new RestEndpointCallback($handler);

        $this->assertEquals($data, $callback($request)->get_data());
    }

    public function testItShouldTurnIteratorResponseDataToArray()
    {
        $request = $this->createMock(WP_REST_Request::class);
        $response = new WP_REST_Response(new ArrayIterator($data = ['foo' => 'bar']));

        $handler = $this->createMock(RestEndpointHandler::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($response);

        $callback = new RestEndpointCallback($handler);

        $this->assertEquals($data, $callback($request)->get_data());
    }

    public function testItShouldReturnWpErrorFromHandler()
    {
        $request = $this->createMock(WP_REST_Request::class);
        $error = new WP_Error($code = 404, $msg = 'Not found', $data = ['foo' => 'bar']);

        $handler = $this->createMock(RestEndpointHandler::class);
        $handler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($error);

        $callback = new RestEndpointCallback($handler);
        $result = $callback($request);

        $this->assertEquals($code, $result->get_error_code());
        $this->assertEquals($msg, $result->get_error_message());
        $this->assertEquals($data, $result->get_error_data());
    }
}
