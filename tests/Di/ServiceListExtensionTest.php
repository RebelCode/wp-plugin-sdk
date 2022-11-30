<?php

namespace RebelCode\WpSdk\Tests\Di;

use Dhii\Services\Service;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Di\ServiceListExtension;
use PHPUnit\Framework\TestCase;

class ServiceListExtensionTest extends TestCase
{
    public function testIsService()
    {
        $this->assertInstanceOf(Service::class, new ServiceListExtension([]));
    }

    public function testItShouldMergeServiceLists()
    {
        $prev = ['prev1', 'prev2'];
        $list1 = ['one', 'two', 'three'];
        $list2 = ['four'];
        $list3 = ['five', 'six'];

        $service = new ServiceListExtension(['list1', 'list2', 'list3']);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(3))
                  ->method('get')
                  ->withConsecutive(['list1'], ['list2'], ['list3'])
                  ->willReturnOnConsecutiveCalls($list1, $list2, $list3);

        $result = $service($container, $prev);

        $this->assertEquals(
            array_merge($prev, $list1, $list2, $list3),
            $result
        );
    }
}
