<?php

namespace RebelCode\WpSdk;

use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Di\PluginContainer;
use RebelCode\WpSdk\Di\ScopedModule;
use RebelCode\WpSdk\Modules\PluginModule;
use RebelCode\WpSdk\Modules\WordPressModule;
use RuntimeException;

class Plugin implements ContainerInterface
{
    /** @var string The path to the plugin's main PHP file. */
    protected $filePath;

    /** @var Module[] The plugin's modules. */
    protected $modules;

    /** @var ContainerInterface The plugin's DI container. */
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The DI container.
     * @param Module[] $modules The list of modules.
     */
    public function __construct(string $filePath, ContainerInterface $container, array $modules = [])
    {
        $this->filePath = $filePath;
        $this->modules = $modules;
        $this->container = $container;
    }

    /** @inheritDoc */
    public function get(string $id)
    {
        return $this->container->get($id);
    }

    /** @inheritDoc */
    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    /** Runs the plugin. */
    public function run(): void
    {
        foreach ($this->modules as $module) {
            foreach ($module->getHooks() as $hook => $handlers) {
                foreach ((array) $handlers as $handler) {
                    $handler->attach($hook, $this->container);
                }
            }

            $module->run($this->container, $this);
        }
    }

    /**
     * Creates a standard plugin instance from a file path, loading modules from auto-detected module PHP files, with
     * a {@link PluginModule} and {@link WordPressModule} also included.
     */
    public static function create(string $filePath, string $scopeDelim = '/', array $filterPrefixes = []): self
    {
        $modulesFile = dirname($filePath) . '/modules.php';

        if (!is_readable($modulesFile)) {
            throw new RuntimeException('Cannot open and read the "modules.php" file');
        }

        $modules = require $modulesFile;
        if (!is_array($modules)) {
            throw new RuntimeException('The return value of the "modules.php" file is not an array');
        }

        $modules = array_merge(
            [
                'plugin' => new PluginModule($filePath, $scopeDelim),
                'wp' => new WordPressModule(),
            ],
            $modules
        );

        foreach ($modules as $key => $module) {
            $modules[$key] = new ScopedModule($key . $scopeDelim, $module);
        }

        $container = new PluginContainer($filterPrefixes, $modules);

        return new self($filePath, $container, $modules);
    }
}
