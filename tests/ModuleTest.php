<?php

namespace RebelCode\WpSdk\Tests;

use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Module;

class ModuleTest extends TestCase
{
    public function testItReturnsNoFactories()
    {
        $module = new Module();

        $this->assertCount(0, $module->getFactories());
    }

    public function testItReturnsNoExtensions()
    {
        $module = new Module();

        $this->assertCount(0, $module->getExtensions());
    }

    public function testItReturnsNoHooks()
    {
        $module = new Module();

        $this->assertCount(0, $module->getHooks());
    }
}
