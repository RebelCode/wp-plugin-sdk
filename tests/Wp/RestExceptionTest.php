<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Tests\Helpers\WpTest;
use RebelCode\WpSdk\Wp\RestException;
use RuntimeException;
use WP_REST_Response;
use stdClass;

class RestExceptionTest extends TestCase
{
    use WpTest;

    public static function setUpBeforeClass(): void
    {
        static::importWpRestApi();
    }

    public function testGetResponse(): void
    {
        $status = 405;
        $data = [
            'foo' => 1,
            'bar' => 'baz',
        ];

        $prev = new RuntimeException('Previous exception');
        $subject = new RestException('Oh no!', $data, $status, $prev);

        $this->assertEquals('Oh no!', $subject->getMessage());
        $this->assertSame($prev, $subject->getPrevious());

        $response = $subject->getResponse();

        $this->assertInstanceOf(WP_REST_Response::class, $response);
        $this->assertEquals($status, $response->get_status());
        $this->assertEquals($data, $response->get_data());
    }

    public function testGetResponseData(): void
    {
        $data = new stdClass();
        $subject = new RestException('Oh no!', $data, 405);

        $this->assertSame($data, $subject->getResponseData());
    }

    public function testGetStatusCode(): void
    {
        $status = 405;
        $subject = new RestException('Oh no!', [], $status);

        $this->assertEquals($status, $subject->getStatusCode());
    }
}
