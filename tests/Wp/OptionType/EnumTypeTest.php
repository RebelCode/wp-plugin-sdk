<?php

namespace RebelCode\WpSdk\Tests\Wp\OptionType;

use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Wp\OptionType\EnumType;

class EnumTypeTest extends TestCase
{
    public function provideData(): array
    {
        return [
            'foo' => ['foo', 'foo'],
            'bar' => ['bar', 'bar'],
            'baz' => ['baz', 'baz'],
            'other' => ['invalid', 'foo'],
            'int' => [1, 'foo'],
            'float' => [1.1, 'foo'],
            'true' => [true, 'foo'],
            'false' => [false, 'foo'],
            'null' => [null, 'foo'],
            'array' => [[1, 2, 3], 'foo'],
            'object' => [(object) ['a' => 1, 'b' => 2, 'c' => 3], 'foo'],
        ];
    }

    /** @dataProvider provideData */
    public function testItCanParseValue($value, $expected)
    {
        $type = new EnumType([
            'foo',
            'bar',
            'baz',
        ]);

        $this->assertSame($expected, $type->parseValue($value));
    }

    /** @dataProvider provideData */
    public function testItCanSerializeValue($value, $expected)
    {
        $type = new EnumType([
            'foo',
            'bar',
            'baz',
        ]);

        $this->assertSame($expected, $type->serializeValue($value));
    }
}
