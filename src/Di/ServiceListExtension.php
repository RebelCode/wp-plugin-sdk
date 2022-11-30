<?php

namespace RebelCode\WpSdk\Di;

use Dhii\Services\Service;
use Psr\Container\ContainerInterface;

/**
 * An extension implementation that extends a service list with a list of other services lists.
 */
class ServiceListExtension extends Service
{
    /** @inheritDoc */
    public function __invoke(ContainerInterface $c, array $prev = [])
    {
        $result = $prev;
        foreach ($this->dependencies as $dependency) {
            $result = array_merge($result, $c->get($dependency));
        }

        return $result;
    }
}
