<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;

/** Represents configuration for a WordPress shortcode in an immutable object form. */
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

    /** Creates a factory for a shortcode, for use in modules. */
    public static function factory(string $tag, string $callbackId): Factory
    {
        return new Factory([$callbackId], function ($callback) use ($tag) {
            return new self($tag, $callback);
        });
    }
}
