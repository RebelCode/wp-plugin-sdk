<?php

use Dhii\Services\Factories\Value;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Module;
use RebelCode\WpSdk\Plugin;

return [
    "foo" => new class extends Module {
        public function getFactories(): array
        {
            return [
                "bar" => new Value('baz'),
            ];
        }

        public function run(ContainerInterface $c, Plugin $p): void
        {
            echo $c->get("bar");
        }
    },
];
