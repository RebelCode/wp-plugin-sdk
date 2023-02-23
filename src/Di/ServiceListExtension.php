<?php

namespace RebelCode\WpSdk\Di;

use Dhii\Services\ResolveKeysCapableTrait;
use Dhii\Services\Service;
use Psr\Container\ContainerInterface;

/**
 * An extension implementation that extends a service list with a list of other services lists.
 */
class ServiceListExtension extends Service
{
    use ResolveKeysCapableTrait;

    /** @inheritDoc */
    public function __invoke(ContainerInterface $c, array $prev = [])
    {
        $result = $prev;
        foreach ($this->resolveDeps($c, $this->dependencies) as $list) {
            $result = array_merge($result, $list);
        }

        return $result;
    }
}
