<?php

namespace RebelCode\WpSdk\Tests\Di;

use Dhii\Services\Service;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Di\OverrideExtension;
use PHPUnit\Framework\TestCase;

class OverrideExtensionTest extends TestCase
{
    public function testIsService()
    {
        $this->assertInstanceOf(Service::class, new OverrideExtension('test'));
    }

    public function testItShouldOverrideService()
    {
        $prev = 123;
        $new = 456;
        $newId = 'new_service';

        $service = new OverrideExtension($newId);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($newId)
                  ->willReturn($new);

        $result = $service($container, $prev);

        $this->assertSame($new, $result);
    }
}
