<?php

namespace Wp\OptionType;

use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Wp\OptionType\StringType;

class StringTypeTest extends TestCase
{
    public function provideDataForParseTest(): array
    {
        return [
            'string' => ['string', 'string'],
            'int' => [1, '1'],
            'float' => [1.1, '1.1'],
            'true' => [true, '1'],
            'false' => [false, ''],
            'null' => [null, ''],
            'array' => [[1, 2, 3], ''],
            'object' => [(object) ['a' => 1, 'b' => 2, 'c' => 3], ''],
        ];
    }

    /** @dataProvider provideDataForParseTest */
    public function testItCanParseValue($value, $expected)
    {
        $type = new StringType();

        $this->assertSame($expected, $type->parseValue($value));
    }

    public function provideDataForSerializeTest(): array
    {
        return [
            'string' => ['string', 'string'],
            'int' => [1, '1'],
            'float' => [1.1, '1.1'],
            'true' => [true, '1'],
            'false' => [false, ''],
            'null' => [null, ''],
            'array' => [[1, 2, 3], ''],
            'object' => [(object) ['a' => 1, 'b' => 2, 'c' => 3], ''],
        ];
    }

    /** @dataProvider provideDataForSerializeTest */
    public function testItCanSerializeValue($value, $expected)
    {
        $type = new StringType();

        $this->assertSame($expected, $type->serializeValue($value));
    }
}
