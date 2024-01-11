<?php

namespace RebelCode\WpSdk\Di;

use Dhii\Container\DeprefixingContainer;
use Dhii\Services\Service;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Handler;
use RebelCode\WpSdk\Module;
use RebelCode\WpSdk\Plugin;

/**
 * A module decorator that prefixes all of a module's services.
 *
 * This instance ensures that all service factories given by the inner module are prefixed. In addition, the decorator
 * will also prefix all service dependencies for both factories and extensions.
 *
 * Naturally, prefixing can break references to services that are outside the module. For this reason, extension keys
 * will NOT be prefixed and dependencies that start with an '@' character will also NOT be prefixed. This gives modules
 * the ability to extend or depend on external services.
 *
 * Example:
 * ```
 * [
 *      "foo" => new Constructor(Foo::class, ['bar']),
 *      "bar" => new Value("hello"),
 * ]
 * // If prefix is set to "pre/", the above will be transformed into:
 * [
 *      "pre/foo" => new Constructor(Foo::class, ['pre/bar']),
 *      "pre/bar" => new Value("hello"),
 * ]
 * ```
 */
class ScopedModule extends Module
{
    /** @var string The prefix to apply to service keys. */
    protected $prefix;

    /** @var Module The module instance to which to apply the prefixing. */
    protected $inner;

    /**
     * Constructor.
     *
     * @param string $prefix The prefix to apply to service keys.
     * @param Module $inner The module instance to which to apply the prefixing.
     */
    public function __construct(string $prefix, Module $inner)
    {
        $this->prefix = $prefix;
        $this->inner = $inner;
    }

    /** @inheritDoc */
    public function getFactories(): array
    {
        return $this->prefixFactories($this->inner->getFactories());
    }

    /** @inheritDoc */
    public function getExtensions(): array
    {
        return $this->prefixExtensions($this->inner->getExtensions());
    }

    /** @inheritDoc */
    public function getHooks(): array
    {
        return $this->prefixHooks($this->inner->getHooks());
    }

    /** @inheritDoc */
    public function run(ContainerInterface $c, Plugin $plugin): void
    {
        $container = new DeprefixingContainer($c, $this->prefix, false);
        $this->inner->run($container, $plugin);
    }

    /**
     * Applies the prefix to a service key.
     *
     * Service keys that start with '@' are not prefixed, but the '@' is omitted.
     *
     * @param string $key The service key.
     *
     * @return string The prefixed service key.
     */
    protected function applyPrefix(string $key): string
    {
        return ($key[0] === '@')
            ? substr($key, 1)
            : $this->prefix . $key;
    }

    /**
     * Prefixes a list of factories.
     *
     * @param Service[] $factories The factories to prefix.
     *
     * @return Service[] The list of prefixed factories.
     */
    protected function prefixFactories(array $factories): array
    {
        $results = [];

        foreach ($factories as $key => $factory) {
            $newKey = $this->applyPrefix($key);

            $results[$newKey] = $this->prefixDependencies($factory);
        }

        return $results;
    }

    /**
     * Prefixes a list of extensions.
     *
     * @param Service[] $extensions The extensions to prefix.
     *
     * @return Service[] The list of prefixed extensions.
     */
    protected function prefixExtensions(array $extensions): array
    {
        $results = [];

        foreach ($extensions as $key => $extension) {
            $results[$key] = $this->prefixDependencies($extension);
        }

        return $results;
    }

    /**
     * Prefixes a list of hooks.
     *
     * @param Handler[] $hooks The hooks to prefix.
     *
     * @return Handler[] The list of prefixed hooks.
     */
    protected function prefixHooks(array $hooks): array
    {
        $results = [];

        foreach ($hooks as $hook => $handlers) {
            $results[$hook] = [];

            if (!is_array($handlers)) {
                $handlers = [$handlers];
            }

            foreach ($handlers as $handler) {
                $newHandler = $this->prefixDependencies($handler);

                if (is_string($newHandler->priority)) {
                    $newHandler->priority = $this->applyPrefix($newHandler->priority);
                }

                $results[$hook][] = $newHandler;
            }
        }

        return $results;
    }

    /**
     * Creates a copy of the service with its dependencies prefixed.
     *
     * @param callable|Service $service The service whose dependencies to prefix.
     *
     * @return Service The new service.
     */
    protected function prefixDependencies($service): Service
    {
        if (!($service instanceof Service)) {
            return $service;
        }

        $dependencies = $service->getDependencies();
        $dependencies = array_map(function ($dep) {
            return is_string($dep)
                ? $this->applyPrefix($dep)
                : $this->prefixDependencies($dep);
        }, $dependencies);

        return $service->withDependencies($dependencies);
    }
}
