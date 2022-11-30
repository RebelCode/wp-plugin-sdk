<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\AbstractOption;
use RebelCode\WpSdk\Wp\Option;
use RebelCode\WpSdk\Wp\OptionType;
use function Brain\Monkey\Functions\expect;

class OptionTest extends TestCase
{
    use BrainMonkeyTest;

    public function testItExtendsAbstractOption()
    {
        $this->assertInstanceOf(AbstractOption::class, new Option('test'));
    }

    public function autoloadOptionDataProvider(): array
    {
        return [[true], [false]];
    }

    /** @dataProvider autoloadOptionDataProvider */
    public function testCtorShouldSetProperties($autoload)
    {
        $type = $this->createMock(OptionType::class);
        $name = 'foo';
        $default = 'bar';
        $option = new Option($name, $type, $default, $autoload);

        $this->assertSame($name, $option->name);
        $this->assertSame($type, $option->type);
        $this->assertSame($default, $option->default);
        $this->assertSame($autoload, $option->autoload);
    }

    public function testItGetsValueFromWp()
    {
        $name = 'foo';
        $rawValue = 'raw-value';
        $parsedValue = 'parsed-value';
        $type = $this->createMock(OptionType::class);

        $option = new Option($name, $type, 'default-value', true);

        $type->expects($this->once())->method('parseValue')->with($rawValue)->willReturn($parsedValue);
        expect('get_option')->with($name, null)->andReturn($rawValue);

        $this->assertSame($parsedValue, $option->getValue());
    }

    public function testItFallsBackToDefault()
    {
        $name = 'foo';
        $default = 'default-value';
        $type = $this->createMock(OptionType::class);

        $option = new Option($name, $type, $default, true);

        $type->expects($this->never())->method('parseValue');
        expect('get_option')->with($name, null)->andReturn(null);

        $this->assertSame($default, $option->getValue());
    }

    public function testItSetsValueToWp()
    {
        $name = 'foo';
        $inputValue = 'input-value';
        $serialized = 'serialized-value';
        $type = $this->createMock(OptionType::class);

        $option = new Option($name, $type, 'default-value', true);

        $type->expects($this->once())->method('serializeValue')->with($inputValue)->willReturn($serialized);
        expect('update_option')->with($name, $serialized, true)->andReturn(true);

        $this->assertTrue($option->setValue($inputValue));
    }

    /** @dataProvider autoloadOptionDataProvider */
    public function testItSetAutoload($autoload)
    {
        $name = 'foo';
        $type = $this->createMock(OptionType::class);

        $option = new Option($name, $type, null, $autoload);

        $type->method('parseValue')->willReturnArgument(0);
        $type->method('serializeValue')->willReturnArgument(0);

        expect('update_option')->with($name, 'new_value', $autoload)->andReturn(true);

        $option->setValue('new_value');
    }

    public function testItCanCreateAFactory()
    {
        $type = $this->createMock(OptionType::class);
        $name = 'foo';
        $default = 'bar';

        $factory = Option::factory($name, $type, $default, true);

        $c = $this->createMock(ContainerInterface::class);
        $option = $factory($c);

        $this->assertSame($name, $option->name);
        $this->assertSame($type, $option->type);
        $this->assertSame($default, $option->default);
        $this->assertTrue($option->autoload);
    }

    public function testItCanCreateAFactoryWithOptionTypeDep()
    {
        $typeId = 'custom_type';
        $type = $this->createMock(OptionType::class);
        $name = 'foo';
        $default = 'bar';

        $factory = Option::factory($name, $typeId, $default, true);

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->once())->method('get')->with($typeId)->willReturn($type);

        $option = $factory($c);

        $this->assertSame($name, $option->name);
        $this->assertSame($type, $option->type);
        $this->assertSame($default, $option->default);
        $this->assertTrue($option->autoload);
    }
}
