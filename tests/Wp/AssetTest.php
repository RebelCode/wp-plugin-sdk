<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\Asset;

class AssetTest extends TestCase
{
    use BrainMonkeyTest;

    public function testCtorShouldSetProperties()
    {
        $handle = 'my-asset';
        $url = 'path/to/asset';
        $ver = '1.0.0';
        $deps = ['dep1', 'dep2'];

        $asset = $this->getMockForAbstractClass(Asset::class, [$handle, $url, $ver, $deps]);

        $this->assertSame($handle, $asset->id);
        $this->assertSame($url, $asset->url);
        $this->assertSame($ver, $asset->version);
        $this->assertSame($deps, $asset->deps);
        $this->assertFalse($asset->isRegistered);
    }

    public function testItShouldRegisterBeforeEnqueue()
    {
        $handle = 'my-asset';
        $asset = $this->getMockForAbstractClass(Asset::class, [$handle, 'url']);

        $asset->expects($this->once())
              ->method('register')
              ->willReturn(true);

        $asset->enqueue();
    }

    public function testItShouldOnlyRegisterOnce()
    {
        $handle = 'my-asset';
        $asset = $this->getMockForAbstractClass(Asset::class, [$handle, 'url']);
        $asset->isRegistered = true;

        $asset->expects($this->never())->method('register');
        $asset->enqueue();
    }
}
