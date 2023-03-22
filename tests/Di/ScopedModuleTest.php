<?php

namespace RebelCode\WpSdk\Tests\Di;

use Dhii\Container\DeprefixingContainer;
use Dhii\Services\Service;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Di\ScopedModule;
use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Handler;
use RebelCode\WpSdk\Module;
use RebelCode\WpSdk\Plugin;
use function Brain\Monkey\Functions\when;

class ScopedModuleTest extends TestCase
{
    public function testItShouldPrefixFactories()
    {
        $s1 = $this->getMockForAbstractClass(Service::class, [['d1']]);
        $s2 = $this->getMockForAbstractClass(Service::class, [['d2', 'd3']]);

        $inner = $this->createMock(Module::class);
        $inner->expects($this->once())->method('getFactories')->willReturn([
            'foo' => $s1,
            'bar' => $s2,
        ]);

        $module = new ScopedModule('pre_', $inner);

        $expected = [
            'pre_foo' => $this->getMockForAbstractClass(Service::class, [['pre_d1']]),
            'pre_bar' => $this->getMockForAbstractClass(Service::class, [['pre_d2', 'pre_d3']]),
        ];

        $this->assertEquals($expected, $module->getFactories());
    }

    public function testItShouldNotPrefixFactoryDepsWithAtSymbol()
    {
        $s1 = $this->getMockForAbstractClass(Service::class, [['d1']]);
        $s2 = $this->getMockForAbstractClass(Service::class, [['@d2', 'd3']]);

        $inner = $this->createMock(Module::class);
        $inner->expects($this->once())->method('getFactories')->willReturn([
            'foo' => $s1,
            'bar' => $s2,
        ]);

        $module = new ScopedModule('pre_', $inner);

        $expected = [
            'pre_foo' => $this->getMockForAbstractClass(Service::class, [['pre_d1']]),
            'pre_bar' => $this->getMockForAbstractClass(Service::class, [['d2', 'pre_d3']]),
        ];

        $this->assertEquals($expected, $module->getFactories());
    }

    public function testItShouldOnlyPrefixExtensionDeps()
    {
        $s1 = $this->getMockForAbstractClass(Service::class, [['d1']]);
        $s2 = $this->getMockForAbstractClass(Service::class, [['d2', 'd3']]);

        $inner = $this->createMock(Module::class);
        $inner->expects($this->once())->method('getExtensions')->willReturn([
            'foo' => $s1,
            'bar' => $s2,
        ]);

        $module = new ScopedModule('pre_', $inner);

        $expected = [
            'foo' => $this->getMockForAbstractClass(Service::class, [['pre_d1']]),
            'bar' => $this->getMockForAbstractClass(Service::class, [['pre_d2', 'pre_d3']]),
        ];

        $this->assertEquals($expected, $module->getExtensions());
    }

    public function testItShouldOnlyPrefixHandlerDeps()
    {
        when('__return_true')->justReturn(true);

        $h1 = new Handler(['d1'], '__return_true');
        $h2 = new Handler(['@d2', 'd3'], '__return_true');
        $h3 = new Handler(['d4'], '__return_true');

        $inner = $this->createMock(Module::class);
        $inner->expects($this->once())->method('getHooks')->willReturn([
            'foo' => [$h1],
            'bar' => [$h2, $h3],
        ]);

        $module = new ScopedModule('pre_', $inner);

        $expected = [
            'foo' => [new Handler(['pre_d1'], '__return_true')],
            'bar' => [
                new Handler(['d2', 'pre_d3'], '__return_true'),
                new Handler(['pre_d4'], '__return_true'),
            ],
        ];

        $this->assertEquals($expected, $module->getHooks());
    }

    public function testItShouldRunWithScopedServices()
    {
        $c = $this->createMock(ContainerInterface::class);
        $p = $this->createMock(Plugin::class);

        $inner = $this->createMock(Module::class);
        $inner->expects($this->once())->method('run')->with(new DeprefixingContainer($c, 'pre_', false), $p);

        $module = new ScopedModule('pre_', $inner);
        $module->run($c, $p);
    }
}
