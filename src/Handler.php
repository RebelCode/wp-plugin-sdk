<?php

namespace RebelCode\WpSdk;

use Dhii\Services\ResolveKeysCapableTrait;
use Dhii\Services\Service;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionFunction;

/** Represents a handler for a WordPress hook. */
class Handler extends Service
{
    use ResolveKeysCapableTrait;

    /** @var callable */
    public $handler;

    /** @var int|string */
    public $priority;

    /** @var int|null */
    public $numArgs;

    /**
     * Constructor.
     *
     * @param int|string $priority The priority at which the handler should be called.
     *        If a string is passed, the priority will be fetched from the container
     *        using the string as the service ID.
     */
    public function __construct(array $deps, callable $handler, $priority = 10, ?int $numArgs = null)
    {
        parent::__construct($deps);
        $this->handler = $handler;
        $this->priority = $priority;
        $this->numArgs = $numArgs ?? max(0, static::countParams($handler) - count($deps));
    }

    /** Attaches the handler to a WordPress hook. */
    public function attach(string $hook, ContainerInterface $c)
    {
        if (is_string($this->priority)) {
            $priority = $c->get($this->priority);
        } else {
            $priority = $this->priority;
        }

        add_filter($hook, $this($c), $priority, $this->numArgs ?? 1);
    }

    /** Returns the handler function. */
    public function __invoke(ContainerInterface $c): callable
    {
        return function (...$hookArgs) use ($c) {
            $depArgs = $this->resolveKeys($c, $this->dependencies);
            $allArgs = array_merge($hookArgs, $depArgs);
            // Call the handler, passing the hook args first and the dependencies second.
            return call_user_func_array($this->handler, $allArgs);
        };
    }

    /** Counts the parameters for a handler, for automatically setting the `numArgs` when hooking in the handler. */
    public static function countParams(callable $function): ?int
    {
        try {
            $ref = new ReflectionFunction($function);
            return $ref->getNumberOfParameters();
        } catch (ReflectionException $throwable) {
            return null;
        }
    }
}
