<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\AbstractOption;
use RebelCode\WpSdk\Wp\OptionType;
use RebelCode\WpSdk\Wp\Transient;
use function Brain\Monkey\Functions\expect;

class TransientTest extends TestCase
{
    use BrainMonkeyTest;

    public function testItExtendsAbstractOption()
    {
        $this->assertInstanceOf(AbstractOption::class, new Transient('test'));
    }

    public function testCtorSetsProperties()
    {
        $type = $this->createMock(OptionType::class);
        $transient = new Transient('my-transient', $type, 12345);

        $this->assertSame('my-transient', $transient->name);
        $this->assertSame($type, $transient->type);
        $this->assertSame(12345, $transient->expiry);
    }

    public function testItGetsValueFromWp()
    {
        $name = 'foo';
        $rawValue = 'raw-value';
        $parsedValue = 'parsed-value';
        $type = $this->createMock(OptionType::class);

        $transient = new Transient($name, $type, 123, 'default-value');

        $type->expects($this->once())->method('parseValue')->with($rawValue)->willReturn($parsedValue);
        expect('get_transient')->with($name, null)->andReturn($rawValue);

        $this->assertSame($parsedValue, $transient->getValue());
    }

    public function testItFallsBackToDefault()
    {
        $name = 'foo';
        $default = 'default-value';
        $type = $this->createMock(OptionType::class);

        $transient = new Transient($name, $type, 123, $default);

        $type->expects($this->never())->method('parseValue');
        expect('get_transient')->with($name, null)->andReturn(false);

        $this->assertSame($default, $transient->getValue());
    }

    public function testItSetsValueToWp()
    {
        $name = 'foo';
        $inputValue = 'input-value';
        $serialized = 'serialized-value';
        $expiry = 123;
        $type = $this->createMock(OptionType::class);

        $transient = new Transient($name, $type, $expiry, 'default-value');

        $type->expects($this->once())->method('serializeValue')->with($inputValue)->willReturn($serialized);
        expect('set_transient')->with($name, $serialized, $expiry)->andReturn(true);

        $this->assertTrue($transient->setValue($inputValue));
    }

    public function testItCanCreateAFactory()
    {
        $type = $this->createMock(OptionType::class);
        $name = 'foo';
        $expiry = 123;
        $default = 'bar';

        $factory = Transient::factory($name, $type, $expiry, $default);

        $c = $this->createMock(ContainerInterface::class);
        $transient = $factory($c);

        $this->assertSame($name, $transient->name);
        $this->assertSame($type, $transient->type);
        $this->assertSame($expiry, $transient->expiry);
        $this->assertSame($default, $transient->default);
    }

    public function testItCanCreateAFactoryWithTypeDep()
    {
        $typeId = 'custom_type';
        $type = $this->createMock(OptionType::class);
        $name = 'foo';
        $expiry = 123;
        $default = 'bar';

        $factory = Transient::factory($name, $typeId, $expiry, $default);

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->once())->method('get')->with($typeId)->willReturn($type);

        $transient = $factory($c);

        $this->assertSame($name, $transient->name);
        $this->assertSame($type, $transient->type);
        $this->assertSame($expiry, $transient->expiry);
        $this->assertSame($default, $transient->default);
    }
}
