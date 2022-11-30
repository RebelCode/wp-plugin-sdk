<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;

/** Represents a WordPress cron job. */
class CronJob
{
    /** @var string */
    public $hook;

    /** @var array */
    public $args;

    /** @var string|null */
    public $repeat;

    /** @var callable[] */
    public $handlers;

    /**
     * Constructor.
     *
     * @param string $hook The hook to trigger when the cron job is invoked.
     * @param array $args Optional arguments to pass to cron job handlers.
     * @param string|null $repeat Optional repetition schedule. See {@link wp_get_schedules()}. If null, the cron job
     *                              will be scheduled for a one-time invocation only.
     * @param callable[] $handlers Optional list of handlers to register with the cron job.
     *                             See {@link CronJob::ensureScheduled()}.
     */
    public function __construct(string $hook, array $args = [], string $repeat = null, array $handlers = [])
    {
        $this->hook = $hook;
        $this->args = $args;
        $this->repeat = $repeat;
        $this->handlers = $handlers;
    }

    /**
     * Schedules the cron job.
     *
     * @param int|null $time The time at which to schedule the job, or null to run immediately.
     * @param array $args Optional arguments to pass to the cron job. These will be merged with the cron job's args.
     * @return bool True if the job was scheduled, false on error.
     */
    public function schedule(?int $time = null, array $args = []): bool
    {
        $time = $time ?? time();
        $allArgs = array_merge($this->args, $args);

        if ($this->repeat === null) {
            $ret = wp_schedule_single_event($time, $this->hook, $allArgs);
        } else {
            $ret = wp_schedule_event($time, $this->repeat, $this->hook, $allArgs);
        }

        return $ret === true;
    }

    /**
     * Remove the scheduled event for the job.
     *
     * @return bool True if the job was unscheduled, false on error or if the job was not already scheduled.
     */
    public function unschedule(): bool
    {
        $scheduled = $this->getScheduledEvent();

        if ($scheduled === null) {
            return false;
        }

        $ret = wp_unschedule_event($scheduled->timestamp, $this->hook, $this->args);

        return $ret === true;
    }

    /**
     * Checks if a cron job is scheduled.
     *
     * @return bool True if the job is scheduled, false if not.
     */
    public function isScheduled(): bool
    {
        $next = wp_next_scheduled($this->hook, $this->args);

        return $next !== false;
    }

    /**
     * Retrieves the scheduled event for a cron job, if any.
     *
     * @return object|false The event object. False if the event does not exist.
     */
    public function getScheduledEvent(): ?object
    {
        $event = wp_get_scheduled_event($this->hook, $this->args);

        return is_object($event) ? $event : null;
    }

    /** Registers the hooks for the cron job's handlers. */
    public function registerHandlers(): void
    {
        foreach ($this->handlers as $handler) {
            add_action($this->hook, $handler);
        }
    }

    /**
     * Ensures that a cron job and its handlers are scheduled.
     *
     * Cron events will be rescheduled if the existing event's repetition schedule does not match the schedule of the
     * cron job given as argument.
     */
    public function ensureScheduled()
    {
        // Cache for the WordPress schedules
        static $schedules = null;

        // Get the existing event, if it exists
        $event = $this->getScheduledEvent();
        $isScheduled = is_object($event);

        // Check if doing cron or if Crontrol is rescheduling a job
        $doingCron = !empty(filter_input(INPUT_GET, 'doing_wp_cron'));
        $fromCrontrol = filter_input(INPUT_GET, 'crontrol-single-event') === '1';

        // If an event is already scheduled with the same repetition, stop here
        // We also stop if currently running crons or if the Crontrol plugin is rescheduling a job
        if (($isScheduled && $event->schedule === $this->repeat) || ($doingCron || $fromCrontrol)) {
            return;
        }

        // Unschedule any existing event
        $this->unschedule();

        // Get the WordPress schedules if not already cached
        if ($schedules === null) {
            $schedules = wp_get_schedules();
        }

        $time = time();
        $time += ($schedules[$this->repeat]['interval'] ?? 0);

        $this->schedule($time);
    }

    /** Creates a factory for a cron job, for use in modules. */
    public static function factory(
        string $hook,
        array $args = [],
        string $repeat = null,
        array $handlersIds = []
    ): Factory {
        return new Factory($handlersIds, function (...$handlers) use ($hook, $args, $repeat) {
            return new self($hook, $args, $repeat, $handlers);
        });
    }
}
