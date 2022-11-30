<?php

namespace Wp\OptionType;

use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Wp\OptionType\BoolType;
use stdClass;

class BoolTypeTest extends TestCase
{
    public function provideDataForParseTest(): array
    {
        return [
            ['1', true],
            ['0', false],
            ['true', true],
            ['false', false],
            ['yes', true],
            ['no', false],
            ['on', true],
            ['off', false],
        ];
    }

    /** @dataProvider provideDataForParseTest */
    public function testItCanParseValue($value, $expected)
    {
        $type = new BoolType();

        $this->assertSame($expected, $type->parseValue($value));
    }

    public function provideDataForSerializeTest(): array
    {
        return [
            'true' => [true, '1'],
            'false' => [false, '0'],
            'string' => ['foo', '1'],
            'int' => [1, '1'],
            'float' => [1.0, '1'],
            'array' => [[1, '2', 3.0], '1'],
            'object' => [new stdClass(), '1'],
        ];
    }

    /** @dataProvider provideDataForSerializeTest */
    public function testItCanSerializeValue($value, $expected)
    {
        $type = new BoolType();

        $this->assertSame($expected, $type->serializeValue($value));
    }
}
