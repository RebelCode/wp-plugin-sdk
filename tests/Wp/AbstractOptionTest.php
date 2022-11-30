<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RebelCode\WpSdk\Wp\AbstractOption;
use RebelCode\WpSdk\Wp\OptionType;

class AbstractOptionTest extends TestCase
{
    /**
     * Creates a mock for use in tests.
     *
     * @return MockObject |AbstractOption
     */
    public function getMock(string $name, ?OptionType $type = null, $default = null): MockObject
    {
        return $this->getMockBuilder(AbstractOption::class)
                    ->setConstructorArgs([$name, $type, $default])
                    ->onlyMethods(['read', 'write'])
                    ->getMockForAbstractClass();
    }

    public function testCtorShouldSetProperties()
    {
        $type = $this->createMock(OptionType::class);
        $name = 'foo';
        $default = 'bar';
        $option = $this->getMock($name, $type, $default);

        $this->assertSame($name, $option->name);
        $this->assertSame($type, $option->type);
        $this->assertSame($default, $option->default);
    }

    public function testItReadsValueAndConvertsType()
    {
        $name = 'foo';
        $rawValue = 'raw-value';
        $parsedValue = 'parsed-value';
        $type = $this->createMock(OptionType::class);

        $option = $this->getMock($name, $type, 'default-value');
        $option->expects($this->once())->method('read')->willReturn($rawValue);

        $type->expects($this->once())->method('parseValue')->with($rawValue)->willReturn($parsedValue);

        $this->assertSame($parsedValue, $option->getValue());
    }

    public function testItFallsBackToDefault()
    {
        $name = 'foo';
        $default = 'default-value';
        $type = $this->createMock(OptionType::class);

        $option = $this->getMock($name, $type, $default);
        $option->expects($this->once())->method('read')->willReturn(null);

        $type->expects($this->never())->method('parseValue');

        $this->assertSame($default, $option->getValue());
    }

    public function testItWritesTheValueWithConvertedType()
    {
        $name = 'foo';
        $inputValue = 'input-value';
        $serialized = 'serialized-value';
        $type = $this->createMock(OptionType::class);

        $option = $this->getMock($name, $type, 'default-value');
        $option->expects($this->once())->method('write')->with($serialized)->willReturn(true);

        $type->expects($this->once())->method('serializeValue')->with($inputValue)->willReturn($serialized);

        $this->assertTrue($option->setValue($inputValue));
    }

    public function testItReturnFalseWhenWriteFails()
    {
        $name = 'foo';
        $inputValue = 'input-value';
        $serialized = 'serialized-value';
        $type = $this->createMock(OptionType::class);

        $option = $this->getMock($name, $type, 'default-value');
        $option->expects($this->once())->method('write')->with($serialized)->willReturn(false);

        $type->expects($this->once())->method('serializeValue')->with($inputValue)->willReturn($serialized);

        $this->assertFalse($option->setValue($inputValue));
    }
}
