<?php

namespace RebelCode\WpSdk\Di;

use Dhii\Services\ResolveKeysCapableTrait;
use Dhii\Services\Service;
use Psr\Container\ContainerInterface;

/**
 * A service helper for extensions that completely override an existing service.
 */
class OverrideExtension extends Service
{
    use ResolveKeysCapableTrait;

    /**
     * Constructor.
     *
     * @param string $replacement The key of the service that will be used to replace the original when extended.
     */
    public function __construct(string $replacement)
    {
        parent::__construct([$replacement]);
    }

    /** @inheritDoc */
    public function __invoke(ContainerInterface $c)
    {
        return $this->resolveDeps($c, $this->dependencies)[0];
    }
}
