<?php

namespace RebelCode\WpSdk\Tests\Wp;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Tests\Helpers\BrainMonkeyTest;
use RebelCode\WpSdk\Wp\CronJob;
use function Brain\Monkey\Functions\expect;
use function Brain\Monkey\Functions\when;

class CronJobTest extends TestCase
{
    use BrainMonkeyTest;

    public function setUp(): void
    {
        parent::setUp();

        when('wp_get_schedules')->justReturn([
            'hourly' => [
                'interval' => 3600,
                'display' => 'Every hour',
            ],
            'daily' => [
                'interval' => 86400,
                'display' => 'Every day',
            ],
        ]);
    }

    public function testCtorShouldSetProperties()
    {
        $cronJob = new CronJob($hook = 'my-hook', $args = ['one', 234], $repeat = 'hourly', [
            $h1 = function () {
            },
            $h2 = function () {
            },
        ]);

        $this->assertSame($hook, $cronJob->hook);
        $this->assertSame($args, $cronJob->args);
        $this->assertSame($repeat, $cronJob->repeat);
        $this->assertSame([$h1, $h2], $cronJob->handlers);
    }

    public function testItCanScheduleSingleEventNow()
    {
        $hook = 'my-hook';
        $args = ['one', 'two'];

        $cronJob = new CronJob($hook, $args);

        expect('wp_schedule_single_event')->once()->with(time(), $hook, $args)->andReturn(true);

        $this->assertTrue($cronJob->schedule());
    }

    public function testItCanScheduleSingleEventLater()
    {
        $hook = 'my-hook';
        $args = ['one', 'two'];
        $time = 123456789;

        $cronJob = new CronJob($hook, $args);

        expect('wp_schedule_single_event')->once()->with($time, $hook, $args)->andReturn(true);

        $this->assertTrue($cronJob->schedule($time));
    }

    public function testItCanScheduleRepeatingEventNow()
    {
        $hook = 'my-hook';
        $repeat = 'hourly';
        $args = ['one', 'two'];

        $cronJob = new CronJob($hook, $args, $repeat);

        expect('wp_schedule_event')->once()->with(time(), $repeat, $hook, $args)->andReturn(true);

        $this->assertTrue($cronJob->schedule());
    }

    public function testItCanScheduleRepeatingEventLater()
    {
        $hook = 'my-hook';
        $repeat = 'hourly';
        $args = ['one', 'two'];
        $time = 123456789;

        $cronJob = new CronJob($hook, $args, $repeat);

        expect('wp_schedule_event')->once()->with($time, $repeat, $hook, $args)->andReturn(true);

        $this->assertTrue($cronJob->schedule($time));
    }

    public function testItCanScheduleSingleEventWithMoreArgs()
    {
        $hook = 'my-hook';
        $jobArgs = ['one', 'two'];
        $moreArgs = ['three', 'four'];
        $fullArgs = array_merge($jobArgs, $moreArgs);

        $cronJob = new CronJob($hook, $jobArgs);

        expect('wp_schedule_single_event')->once()->with(time(), $hook, $fullArgs)->andReturn(true);

        $this->assertTrue($cronJob->schedule(null, $moreArgs));
    }

    public function testItCanScheduleRepeatingEventWithMoreArgs()
    {
        $hook = 'my-hook';
        $schedule = 'hourly';
        $jobArgs = ['one', 'two'];
        $moreArgs = ['three', 'four'];
        $fullArgs = array_merge($jobArgs, $moreArgs);

        $cronJob = new CronJob($hook, $jobArgs, $schedule);

        expect('wp_schedule_event')->once()->with(time(), $schedule, $hook, $fullArgs)->andReturn(true);

        $this->assertTrue($cronJob->schedule(null, $moreArgs));
    }

    public function testItCanUnscheduleEvent()
    {
        $time = 123456789;
        $hook = 'my-hook';
        $args = ['one', 'two'];

        $cronJob = new CronJob($hook, $args);

        expect('wp_get_scheduled_event')->once()->with($hook, $args)->andReturn((object) ['timestamp' => $time]);
        expect('wp_unschedule_event')->once()->with($time, $hook, $args)->andReturn(true);

        $this->assertTrue($cronJob->unschedule());
    }

    public function testItCannotUnscheduleEventThatIsNotScheduled()
    {
        $hook = 'my-hook';
        $args = ['one', 'two'];

        $cronJob = new CronJob($hook, $args);

        expect('wp_get_scheduled_event')->once()->with($hook, $args)->andReturn(false);
        expect('wp_unschedule_event')->never();

        $this->assertFalse($cronJob->unschedule());
    }

    public function testItCanCheckIfItsScheduled()
    {
        $hook = 'my-hook';
        $args = ['one', 'two'];

        $cronJob = new CronJob($hook, $args);

        expect('wp_next_scheduled')->once()->with($hook, $args)->andReturn(123456789);

        $this->assertTrue($cronJob->isScheduled());
    }

    public function testItCanCheckIfItsNotScheduled()
    {
        $hook = 'my-hook';
        $args = ['one', 'two'];

        $cronJob = new CronJob($hook, $args);

        expect('wp_next_scheduled')->once()->with($hook, $args)->andReturn(false);

        $this->assertFalse($cronJob->isScheduled());
    }

    public function testItCanGetScheduledEvent()
    {
        $hook = 'my-hook';
        $args = ['one', 'two'];

        $event = (object) [
            'hook' => $hook,
            'args' => $args,
            'timestamp' => 123456789,
        ];

        $cronJob = new CronJob($hook, $args);

        expect('wp_get_scheduled_event')->once()->with($hook, $args)->andReturn($event);

        $this->assertSame($event, $cronJob->getScheduledEvent());
    }

    public function testItCanRegisterItsHandlers()
    {
        $hook = 'my-hook';
        $args = ['one', 'two'];
        $handlers = [
            $h1 = function () {
            },
            $h2 = function () {
            },
        ];

        $cronJob = new CronJob($hook, $args, null, $handlers);

        expect('add_action')
            ->once()->with($hook, $h1)
            ->andAlsoExpectIt()
            ->once()->with($hook, $h2);

        $cronJob->registerHandlers();
    }

    public function testItCanEnsureScheduled()
    {
        $hook = 'my-hook';
        $args = ['one', 'two'];
        $handlers = [
            function () {
            },
            function () {
            },
        ];

        $cronJob = new CronJob($hook, $args, null, $handlers);

        expect('add_action')->never();

        expect('wp_get_scheduled_event')->with($hook, $args)->andReturn(false);
        expect('wp_schedule_single_event')->once()->with(time(), $hook, $args);

        expect('wp_unschedule_event')->never();

        $cronJob->ensureScheduled();
    }

    public function testItCanEnsureScheduledWhenAlreadyScheduled()
    {
        $hook = 'my-hook';
        $args = ['one', 'two'];
        $repeat = 'hourly';
        $handlers = [
            function () {
            },
            function () {
            },
        ];

        $cronJob = new CronJob($hook, $args, $repeat, $handlers);

        expect('add_action')->never();;

        expect('wp_get_scheduled_event')->with($hook, $args)->andReturn(
            (object) [
                'timestamp' => 123456789,
                'schedule' => $repeat,
            ]
        );

        expect('wp_schedule_event')->never();
        expect('wp_schedule_single_event')->never();
        expect('wp_unschedule_event')->never();

        $cronJob->ensureScheduled();
    }

    public function testItCanRescheduleWhenRepetitionChanges()
    {
        $hook = 'my-hook';
        $args = ['one', 'two'];
        $repeat = 'hourly';
        $handlers = [
            function () {
            },
            function () {
            },
        ];

        $cronJob = new CronJob($hook, $args, $repeat, $handlers);

        expect('add_action')->never();

        $timestamp = 123456789;
        expect('wp_get_scheduled_event')->with($hook, $args)->andReturn(
            (object) [
                'timestamp' => $timestamp,
                'schedule' => 'daily',
            ]
        );

        expect('wp_unschedule_event')->once()->with($timestamp, $hook, $args);
        expect('wp_schedule_event')->once()->with(time() + 3600, $repeat, $hook, $args);

        $cronJob->ensureScheduled();
    }

    public function testItCanCreateAFactory()
    {
        $hook = 'my-hook';
        $args = ['one', 234];
        $repeat = 'hourly';
        $handlerIds = [
            'handler_1',
            'handler_2',
        ];
        $handlers = [
            function () {
            },
            function () {
            },
        ];

        $factory = CronJob::factory($hook, $args, $repeat, $handlerIds);

        $c = $this->createMock(ContainerInterface::class);
        $c->expects($this->exactly(2))
          ->method('get')
          ->withConsecutive([$handlerIds[0]], [$handlerIds[1]])
          ->willReturnOnConsecutiveCalls($handlers[0], $handlers[1]);

        $cronJob = $factory($c);

        $this->assertInstanceOf(CronJob::class, $cronJob);
        $this->assertSame($hook, $cronJob->hook);
        $this->assertSame($args, $cronJob->args);
        $this->assertSame($repeat, $cronJob->repeat);
        $this->assertSame($handlers, $cronJob->handlers);
    }
}
