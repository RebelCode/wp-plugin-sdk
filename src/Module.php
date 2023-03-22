<?php

namespace RebelCode\WpSdk;

use Dhii\Services\Extension;
use Dhii\Services\Service;
use Psr\Container\ContainerInterface;

class Module
{
    /** Runs the module */
    public function run(ContainerInterface $c, Plugin $plugin): void
    {
    }

    /**
     * Returns the WordPress hooks for this module.
     *
     * @return array<Handler|Handler[]>
     */
    public function getHooks(): array
    {
        return [];
    }

    /**
     * Returns the factories for the module's services.
     *
     * @return Service[]
     */
    public function getFactories(): array
    {
        return [];
    }

    /**
     * Returns the extensions for other modules' services.
     *
     * @return Extension[]
     */
    public function getExtensions(): array
    {
        return [];
    }
}
