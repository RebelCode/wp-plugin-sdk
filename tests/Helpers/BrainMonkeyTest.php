<?php

namespace RebelCode\WpSdk\Tests\Helpers;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use function Brain\Monkey\setUp;
use function Brain\Monkey\tearDown;

trait BrainMonkeyTest
{
    // Adds Mockery expectations to the PHPUnit assertions count.
    use MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        setUp();
    }

    public function tearDown(): void
    {
        tearDown();
    }
}
