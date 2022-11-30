<?php

namespace Wp\OptionType;

use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Wp\OptionType\IntType;
use stdClass;

class IntTypeTest extends TestCase
{
    public function provideDataForParseTest(): array
    {
        return [
            'int' => [1, 1],
            'float' => [1.75, 1],
            'string int' => ['1', 1],
            'string float' => ['1.75', 1],
            'true' => [true, 1],
            'false' => [false, 0],
            'null' => [null, 0],
            'array' => [[1, 2, 3], 0],
            'object' => [new stdClass(), 0],
        ];
    }

    /** @dataProvider provideDataForParseTest */
    public function testItCanParseValue($value, $expected)
    {
        $type = new IntType();

        $this->assertSame($expected, $type->parseValue($value));
    }

    public function provideDataForSerializeTest(): array
    {
        return [
            'int' => [1, '1'],
            'float' => [1.51, '1'],
            'string int' => ['1', '1'],
            'string float' => ['1.51', '1'],
            'true' => [true, '1'],
            'false' => [false, '0'],
            'null' => [null, '0'],
            'array' => [[1, 2, 3], '0'],
            'object' => [new stdClass(), '0'],
        ];
    }

    /** @dataProvider provideDataForSerializeTest */
    public function testItCanSerializeValue($value, $expected)
    {
        $type = new IntType();

        $this->assertSame($expected, $type->serializeValue($value));
    }
}
