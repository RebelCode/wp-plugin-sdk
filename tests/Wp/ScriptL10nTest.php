<?php

namespace Wp;

use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\ScriptL10n;
use PHPUnit\Framework\TestCase;
use function Brain\Monkey\Functions\expect;

class ScriptL10nTest extends TestCase
{
    use BrainMonkeyTest;

    public function testCtorShouldSetProperties()
    {
        $name = 'Foo';
        $data = [
            'bar' => 'baz',
            'qux' => 'quux',
        ];

        $l10n = new ScriptL10n($name, $data);

        $this->assertSame($name, $l10n->name);
        $this->assertSame($data, $l10n->data);
    }

    public function testIsCanLocalizeScript()
    {
        $name = 'Foo';
        $data = [
            'bar' => 'baz',
            'qux' => 'quux',
        ];

        expect('wp_localize_script')->once()->with('my-script', $name, $data)->andReturn(true);

        $l10n = new ScriptL10n($name, $data);
        $success = $l10n->localizeFor('my-script');

        $this->assertTrue($success);
    }
}
