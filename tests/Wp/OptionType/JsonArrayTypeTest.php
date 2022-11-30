<?php

namespace Wp\OptionType;

use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Wp\OptionType\JsonArrayType;

class JsonArrayTypeTest extends TestCase
{
    public function provideDataForParseTest(): array
    {
        return [
            'JSON array' => ['["a", "b", "c"]', ['a', 'b', 'c']],
            'JSON object' => ['{"foo":"bar"}', ['foo' => 'bar']],
            'array' => [['a', 'b', 'c'], ['a', 'b', 'c']],
            'object' => [(object) ['foo' => 'bar'], ['foo' => 'bar']],
            'null' => [null, []],
            'empty string' => ['', []],
            'int' => [123, []],
            'float' => [123.456, []],
            'true' => [true, []],
            'false' => [false, []],
        ];
    }

    /** @dataProvider provideDataForParseTest */
    public function testParse($value, $expected)
    {
        $type = new JsonArrayType();

        $this->assertEquals($expected, $type->parseValue($value));
    }

    public function provideDataForSerializeTest(): array
    {
        return [
            'null' => [null, '[]'],
            'array' => [['a', 'b', 'c'], '["a","b","c"]'],
            'assoc array' => [['a' => 1, 'b' => 2], '{"a":1,"b":2}'],
            'object' => [(object) ['foo' => 'bar'], '{"foo":"bar"}'],
            'JSON array' => ['["a", "b", "c"]', '["a", "b", "c"]'],
            'JSON object' => ['{"foo": "bar"}', '{"foo": "bar"}'],
            'empty string' => ['', '[]'],
            'string' => ['foo', '[]'],
            'int' => [123, '[]'],
            'float' => [123.456, '[]'],
            'true' => [true, '[]'],
            'false' => [false, '[]'],
        ];
    }

    /** @dataProvider provideDataForSerializeTest */
    public function testSerialize($value, $expected)
    {
        $type = new JsonArrayType();

        $this->assertEquals($expected, $type->serializeValue($value));
    }
}
