<?php

namespace RebelCode\WpSdk\Di;

use Dhii\Container\Exception\ContainerException;
use Dhii\Container\Exception\NotFoundException;
use LogicException;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Module;

/**
 * The container implementation for the services provided by the plugin's modules.
 */
class PluginContainer implements ContainerInterface
{
    /** @var callable[] */
    protected $factories;

    /** @var callable[] */
    protected $extensions;

    /** @var array<string, mixed> */
    protected $cache = [];

    /** @var string[] */
    protected $filterPrefixes;

    /** @var array<string, mixed> */
    protected $fetchStack = [];

    /**
     * Constructor.
     *
     * @param string[] $filterPrefixes The prefixes to use for WordPress filters when services are created.
     * @param Module[] $modules The modules.
     */
    public function __construct(array $filterPrefixes, array $modules)
    {
        $this->filterPrefixes = $filterPrefixes;
        $this->compileServices($modules);
    }

    /** @inheritDoc */
    public function get($id)
    {
        // Circular dependency detection
        if (isset($this->fetchStack[$id])) {
            $trace = implode(" -> ", array_keys($this->fetchStack));

            throw new LogicException("Circular dependency detected: $trace -> $id");
        }

        $this->fetchStack[$id] = true;

        try {
            if (!$this->has($id)) {
                throw new NotFoundException("Service \"$id\" does not exist");
            }

            if (!array_key_exists($id, $this->cache)) {
                try {
                    $service = ($this->factories[$id])($this);

                    if (array_key_exists($id, $this->extensions)) {
                        $service = ($this->extensions[$id])($this, $service);
                    }
                } catch (ContainerException $ex) {
                    throw new ContainerException("Failed to create service \"${id}\" - " . $ex->getMessage(), 0, $ex);
                }

                $this->cache[$id] = $service;
                foreach ($this->filterPrefixes as $prefix) {
                    $this->cache[$id] = \apply_filters($prefix . $id, $this->cache[$id]);
                }
            }

            return $this->cache[$id];
        } finally {
            unset($this->fetchStack[$id]);
        }
    }

    /** @inheritDoc */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->factories);
    }

    /** @param Module[] $modules */
    protected function compileServices(array $modules)
    {
        $this->factories = [];
        $this->extensions = [];

        foreach ($modules as $module) {
            $this->factories = array_merge($this->factories, $module->getFactories());

            if (empty($this->extensions)) {
                $this->extensions = $module->getExtensions();
            } else {
                foreach ($module->getExtensions() as $key => $extension) {
                    if (!array_key_exists($key, $this->extensions)) {
                        $this->extensions[$key] = $extension;
                        continue;
                    }

                    $prevExtension = $this->extensions[$key];
                    $this->extensions[$key] = function (ContainerInterface $c, $prev) use ($prevExtension, $extension) {
                        return $extension($c, $prevExtension($c, $prev));
                    };
                }
            }
        }
    }
}
