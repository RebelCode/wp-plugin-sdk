<?php

namespace Wp\OptionType;

use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Wp\OptionType\FloatType;
use stdClass;

class FloatTypeTest extends TestCase
{
    public function provideDataForParseTest(): array
    {
        return [
            'float' => [1.51, 1.51],
            'int' => [1, 1.0],
            'string int' => ['1', 1.0],
            'string float' => ['1.51', 1.51],
            'true' => [true, 1.0],
            'false' => [false, 0.0],
            'null' => [null, 0.0],
            'array' => [[1, 2, 3], 0.0],
            'object' => [new stdClass(), 0.0],
        ];
    }

    /** @dataProvider provideDataForParseTest */
    public function testItCanParseValue($value, $expected)
    {
        $type = new FloatType();

        $this->assertSame($expected, $type->parseValue($value));
    }

    public function provideDataForSerializeTest(): array
    {
        return [
            'float' => [1.51, '1.51'],
            'whole float' => [1.0, '1'],
            'int' => [1, '1'],
            'string int' => ['1', '1'],
            'string float' => ['1.51', '1.51'],
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
        $type = new FloatType();

        $this->assertSame($expected, $type->serializeValue($value));
    }
}
