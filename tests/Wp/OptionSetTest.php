<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\Option;
use RebelCode\WpSdk\Wp\OptionSet;
use function Brain\Monkey\Functions\expect;

class OptionSetTest extends TestCase
{
    use BrainMonkeyTest;

    public function testItCanGetOptions()
    {
        $options = [
            'option1' => new Option('option1', Option::string(), 'bar', true),
            'option2' => new Option('option2', Option::int(), 'baz', true),
        ];

        $optionSet = new OptionSet($options);

        $this->assertSame($options, $optionSet->getOptions());
    }

    public function testItCanGetAnOptionByKey()
    {
        $options = [
            'option1' => new Option('option1', Option::string(), null, true),
            'option2' => new Option('option2', Option::int(), null, true),
        ];

        $optionSet = new OptionSet($options);

        $this->assertSame($options['option1'], $optionSet->getOption('option1'));
    }

    public function testItReturnsNullIfNoOptionExists()
    {
        $options = [
            'option1' => new Option('option1', Option::string(), null, true),
            'option2' => new Option('option2', Option::int(), null, true),
        ];

        $optionSet = new OptionSet($options);

        $this->assertNull($optionSet->getOption('baz'));
    }

    public function testItCanGetTheValueOfAnOption()
    {
        $options = [
            'option1' => new Option('option1', Option::string(), null, true),
            'option2' => new Option('option2', Option::int(), null, true),
        ];

        $optionSet = new OptionSet($options);

        expect('get_option')->once()->with('option1', null)->andReturn('foo');

        $this->assertSame('foo', $optionSet->get('option1'));
    }

    public function setOptionValueDataProvider(): array
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }

    /** @dataProvider setOptionValueDataProvider */
    public function testItCanSetTheValueOfAnOption($return, $autoload)
    {
        $optionSet = new OptionSet([
            'option1' => new Option('option1', Option::string(), null, $autoload),
            'option2' => new Option('option2', Option::int(), null, $autoload),
        ]);

        expect('update_option')->once()->with('option1', 'foo', $autoload)->andReturn($return);

        $this->assertEquals($return, $optionSet->set('option1', 'foo'));
    }

    public function testItCanDeleteAnOption()
    {
        $optionSet = new OptionSet([
            'option1' => new Option('option1', Option::string(), null, true),
            'option2' => new Option('option2', Option::int(), null, true),
        ]);

        expect('delete_option')->once()->with('option1')->andReturn(true);

        $this->assertTrue($optionSet->delete('option1'));
    }

    public function testItReturnsFalseWhenWpCannotDeleteAnOption()
    {
        $optionSet = new OptionSet([
            'option1' => new Option('option1', Option::string(), null, true),
            'option2' => new Option('option2', Option::int(), null, true),
        ]);

        expect('delete_option')->once()->with('option1')->andReturn(false);

        $this->assertFalse($optionSet->delete('option1'));
    }

    public function testItCannotDeleteAnOptionThatDoesNotExist()
    {
        $optionSet = new OptionSet([
            'option1' => new Option('option1', Option::string(), null, true),
            'option2' => new Option('option2', Option::int(), null, true),
        ]);

        $this->assertFalse($optionSet->delete('option3'));
    }

    public function testItCanDeleteAllOptions()
    {
        $optionSet = new OptionSet([
            'option1' => new Option('option1', Option::string(), null, true),
            'option2' => new Option('option2', Option::int(), null, true),
        ]);

        expect('delete_option')->with('option1')->andReturn(true);
        expect('delete_option')->with('option2')->andReturn(true);

        $optionSet->deleteAll();
    }

    public function testItCanGetAllOptionValues()
    {
        $optionSet = new OptionSet([
            'option1' => new Option('option1', Option::bool(), null, true),
            'option2' => new Option('option2', Option::int(), null, true),
        ]);

        expect('get_option')
            ->once()->with('option1', null)->andReturn('1')
            ->andAlsoExpectIt()
            ->once()->with('option2', null)->andReturn('123');

        $expected = [
            'option1' => true,
            'option2' => 123,
        ];

        $this->assertSame($expected, $optionSet->getAll());
    }

    public function testItCanUpdateOptions()
    {
        $optionSet = new OptionSet([
            'option1' => new Option('option1', Option::string(), null, true),
            'option2' => new Option('option2', Option::bool(), null, true),
        ]);

        expect('update_option')->with('option1', 'foo', null)->andReturn(true);
        expect('update_option')->with('option2', 1, null)->andReturn(true);

        $optionSet->update([
            'option1' => 'foo',
            'option2' => true,
        ]);
    }

    public function testItCanCreateFactory()
    {
        $optionIds = [
            'option_1',
            'option_2',
        ];
        $options = [
            new Option('option_1', Option::string(), null, true),
            new Option('option_2', Option::bool(), null, true),
        ];

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->exactly(2))
          ->method('get')
          ->withConsecutive([$optionIds[0]], [$optionIds[1]])
          ->willReturnOnConsecutiveCalls($options[0], $options[1]);

        $factory = OptionSet::factory($optionIds);
        $optionSet = $factory($c);

        $this->assertInstanceOf(OptionSet::class, $optionSet);
        $this->assertSame($options, $optionSet->getOptions());
    }
}
