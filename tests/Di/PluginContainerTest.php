<?php

namespace RebelCode\WpSdk\Tests\Di;

use Dhii\Container\Exception\NotFoundException;
use Dhii\Services\Factory;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Di\PluginContainer;
use RebelCode\WpSdk\Module;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use stdClass;
use function Brain\Monkey\Functions\expect;

class PluginContainerTest extends TestCase
{
    use BrainMonkeyTest;

    public function testItShouldImplementContainerInterface()
    {
        $this->assertInstanceOf(
            'Psr\Container\ContainerInterface',
            new PluginContainer([], [])
        );
    }

    public function testItShouldFilterFactoryResults()
    {
        $initialValue = new stdClass();
        $filteredValue = new stdClass();
        $filteredValue2 = new stdClass();

        $module = $this->createMock(Module::class);
        $module->expects($this->atLeastOnce())->method('getFactories')->willReturn([
            'foo' => function () use ($initialValue) {
                return $initialValue;
            },
        ]);

        $container = new PluginContainer(['test_', 'test/'], [$module]);

        expect('apply_filters')
            ->once()->with('test_foo', $initialValue)->andReturn($filteredValue)
            ->andAlsoExpectIt()
            ->once()->with('test/foo', $filteredValue)->andReturn($filteredValue2);

        $this->assertSame($filteredValue2, $container->get('foo'));
    }

    public function testItShouldCacheServices()
    {
        $module = $this->createMock(Module::class);
        $module->expects($this->atLeastOnce())->method('getFactories')->willReturn([
            'foo' => function () {
                return new stdClass();
            },
        ]);

        $container = new PluginContainer([], [$module]);

        $this->assertSame($container->get('foo'), $container->get('foo'));
    }

    public function testItShouldThrowWhenServiceNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Service \"foo\" does not exist");

        $container = new PluginContainer([], []);
        $container->get('foo');
    }

    public function testItShouldOverrideFactories()
    {
        $value = new stdClass();

        $factoriesMod1 = [
            'foo' => function () {
                $this->fail('Factory from module #1 should not be called');
            },
        ];
        $factoriesMod2 = [
            'foo' => function () {
                $this->fail('Factory from module #2 should not be called');
            },
        ];
        $factoriesMod3 = [
            'foo' => function ($c) use ($value) {
                $this->assertInstanceOf(ContainerInterface::class, $c);
                return $value;
            },
        ];

        $module1 = $this->createMock(Module::class);
        $module1->expects($this->atLeastOnce())->method('getFactories')->willReturn($factoriesMod1);

        $module2 = $this->createMock(Module::class);
        $module2->expects($this->atLeastOnce())->method('getFactories')->willReturn($factoriesMod2);

        $module3 = $this->createMock(Module::class);
        $module3->expects($this->atLeastOnce())->method('getFactories')->willReturn($factoriesMod3);

        $container = new PluginContainer([], [$module1, $module2, $module3]);

        $this->assertSame($value, $container->get('foo'));
    }

    public function testItShouldApplyExtensions()
    {
        $values = [
            new stdClass(),
            new stdClass(),
            new stdClass(),
            new stdClass(),
        ];

        $factoriesMod1 = [
            'foo' => function () use ($values) {
                return $values[0];
            },
        ];

        $extensionsMod2 = [
            'foo' => function ($c, $prev) use ($values) {
                $this->assertSame($values[0], $prev);
                return $values[1];
            },
        ];
        $extensionsMod3 = [
            'foo' => function ($c, $prev) use ($values) {
                $this->assertSame($values[1], $prev);
                return $values[2];
            },
        ];
        $extensionsMod4 = [
            'foo' => function ($c, $prev) use ($values) {
                $this->assertSame($values[2], $prev);
                return $values[3];
            },
        ];

        $module1 = $this->createMock(Module::class);
        $module1->expects($this->atLeastOnce())->method('getFactories')->willReturn($factoriesMod1);

        $module2 = $this->createMock(Module::class);
        $module2->expects($this->atLeastOnce())->method('getExtensions')->willReturn($extensionsMod2);

        $module3 = $this->createMock(Module::class);
        $module3->expects($this->atLeastOnce())->method('getExtensions')->willReturn($extensionsMod3);

        $module4 = $this->createMock(Module::class);
        $module4->expects($this->atLeastOnce())->method('getExtensions')->willReturn($extensionsMod4);

        $container = new PluginContainer([], [$module1, $module2, $module3, $module4]);

        $this->assertSame($values[3], $container->get('foo'));
    }

    public function testItShouldDetectCircularDependencies()
    {
        $module = $this->createMock(Module::class);
        $module->expects($this->atLeastOnce())->method('getFactories')->willReturn([
            'foo' => new Factory(['bar'], function ($bar) {
                return $bar;
            }),
            'bar' => new Factory(['baz'], function ($baz) {
                return $baz;
            }),
            'baz' => new Factory(['foo'], function ($foo) {
                return $foo;
            }),
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/foo -> bar -> baz -> foo/');

        $c = new PluginContainer([], [$module]);
        $c->get('foo');
    }
}
