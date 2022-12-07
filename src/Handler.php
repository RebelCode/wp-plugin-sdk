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

    /** @var int */
    public $priority;

    /** @var int|null */
    public $numArgs;

    /** Constructor. */
    public function __construct(array $deps, callable $handler, int $priority = 10, ?int $numArgs = null)
    {
        parent::__construct($deps);
        $this->handler = $handler;
        $this->priority = $priority;
        $this->numArgs = $numArgs ?? max(0, static::countParams($handler) - count($deps));
    }

    /** Attaches the handler to a WordPress hook. */
    public function attach(string $hook, ContainerInterface $c)
    {
        add_filter($hook, $this($c), $this->priority ?? 10, $this->numArgs ?? 1);
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
