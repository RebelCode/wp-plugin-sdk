<?php

namespace RebelCode\WpSdk;

use Dhii\Services\Extension;
use Dhii\Services\Service;
use IteratorAggregate;
use Psr\Container\ContainerInterface;
use Traversable;

/** @implements IteratorAggregate<string,callable> */
class Module implements IteratorAggregate
{
    /** For compatibility with iterable modules. */
    public function getIterator(): Traversable
    {
        yield from $this->getFactories();
        yield from $this->getExtensions();

        return function (ContainerInterface $c) {
            foreach ($this->getHooks() as $key => $handlers) {
                /** @var $handlers Handler[] */
                $handlers = (array) $handlers;

                foreach ($handlers as $handler) {
                    $handler->attach($key, $c);
                }
            }

            $this->run($c);
        };
    }

    /** Runs the module */
    public function run(ContainerInterface $c, Plugin $plugin): void
    {
    }

    /**
     * Returns the WordPress hooks for this module.
     *
     * @return iterable<Handler|Handler[]>
     */
    public function getHooks(): iterable
    {
        return [];
    }

    /**
     * Returns the factories for the module's services.
     *
     * @return iterable<Service>
     */
    public function getFactories(): iterable
    {
        return [];
    }

    /**
     * Returns the extensions for other modules' services.
     *
     * @return iterable<Extension>
     */
    public function getExtensions(): iterable
    {
        return [];
    }
}
