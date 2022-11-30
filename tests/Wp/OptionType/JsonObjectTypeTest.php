<?php

namespace Wp\OptionType;

use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Wp\OptionType\JsonObjectType;
use stdClass;

class JsonObjectTypeTest extends TestCase
{
    public function provideDataForParseTest(): array
    {
        return [
            'JSON object' => ['{"foo":"bar"}', (object) ['foo' => 'bar']],
            'JSON array' => ['["a", "b", "c"]', (object) ['a', 'b', 'c']],
            'array' => [['a', 'b', 'c'], (object) ['a', 'b', 'c']],
            'object' => [(object) ['foo' => 'bar'], (object) ['foo' => 'bar']],
            'null' => [null, new stdClass()],
            'empty string' => ['', new stdClass()],
            'int' => [123, new stdClass()],
            'float' => [123.456, new stdClass()],
            'true' => [true, new stdClass()],
            'false' => [false, new stdClass()],
        ];
    }

    /** @dataProvider provideDataForParseTest */
    public function testParse($value, $expected)
    {
        $type = new JsonObjectType();

        $this->assertEquals($expected, $type->parseValue($value));
    }

    public function provideDataForSerializeTest(): array
    {
        return [
            'object' => [(object) ['foo' => 'bar'], '{"foo":"bar"}'],
            'array' => [['a', 'b', 'c'], '["a","b","c"]'],
            'assoc array' => [['a' => 1, 'b' => 2], '{"a":1,"b":2}'],
            'JSON object' => ['{"foo": "bar"}', '{"foo": "bar"}'],
            'JSON array' => ['["a", "b", "c"]', '["a", "b", "c"]'],
            'null' => [null, '{}'],
            'empty string' => ['', '{}'],
            'string' => ['foo', '{}'],
            'int' => [123, '{}'],
            'float' => [123.456, '{}'],
            'true' => [true, '{}'],
            'false' => [false, '{}'],
        ];
    }

    /** @dataProvider provideDataForSerializeTest */
    public function testSerialize($value, $expected)
    {
        $type = new JsonObjectType();

        $this->assertEquals($expected, $type->serializeValue($value));
    }
}
