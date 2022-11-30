<?php

namespace Wp\OptionType;

use RebelCode\WpSdk\Wp\OptionType\DefaultType;
use PHPUnit\Framework\TestCase;
use stdClass;

class DefaultTypeTest extends TestCase
{
    public function provideData(): array
    {
        return [
            'null' => [null],
            'int' => [1],
            'float' => [1.1],
            'true' => [true],
            'false' => [false],
            'string' => ['foo'],
            'array' => [[1, 2, 3]],
            'object' => [new stdClass()],
        ];
    }

    /** @dataProvider provideData */
    public function testItDoesNotParseValue($value)
    {
        $type = new DefaultType();
        $this->assertSame($value, $type->parseValue($value));
    }

    /** @dataProvider provideData */
    public function testItDoesNotSerializeValue($value)
    {
        $type = new DefaultType();
        $this->assertSame($value, $type->serializeValue($value));
    }
}
