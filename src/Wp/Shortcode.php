<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;
use Dhii\Services\Service;

/**
 * Represents configuration for a WordPress shortcode in an immutable object form.
 *
 * @psalm-import-type ServiceRef from Service
 */
class Shortcode
{
    /** @var string */
    public $tag;

    /** @var callable */
    public $callback;

    /**
     * Constructor.
     *
     * @param string $tag The shortcode tag.
     * @param callable $callback The function that takes the shortcode arguments and returns the rendered string.
     */
    public function __construct(string $tag, callable $callback)
    {
        $this->tag = $tag;
        $this->callback = $callback;
    }

    /** Registers the shortcode. */
    public function register()
    {
        add_shortcode($this->tag, $this->callback);
    }

    /**
     * Creates a factory for a shortcode, for use in modules.
     *
     * @param string $tag The shortcode tag.
     * @param ServiceRef $callback The service for the shortcode's callback.
     */
    public static function factory(string $tag, $callback): Factory
    {
        return new Factory([$callback], function ($callback) use ($tag) {
            return new self($tag, $callback);
        });
    }
}
