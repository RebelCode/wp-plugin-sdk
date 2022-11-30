<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;

/** A style asset implementation. */
class Style extends Asset
{
    /**
     * Constructor.
     *
     * @param string $id The ID of the style.
     * @param string $url The URL of the style file.
     * @param string|null $version The version number.
     * @param array $deps The IDs of the style's dependencies.
     */
    public function __construct(
        string $id,
        string $url,
        string $version = null,
        array $deps = []
    ) {
        parent::__construct($id, $url, $version, $deps);
    }

    /** @inheritDoc */
    public function register(): bool
    {
        return $this->isRegistered = wp_register_style(
            $this->id,
            $this->url,
            $this->deps,
            $this->version
        );
    }

    /** Enqueues the asset. */
    protected function wpEnqueue(): void
    {
        wp_enqueue_style($this->id);
    }

    /**
     * Creates a style factory, for use in modules.
     *
     * @param string $id The ID of the style.
     * @param string $url The URL of the style file.
     * @param string|null $version The version number.
     * @param array $deps The IDs of the style's dependencies.
     * @return Factory The factory for the style.
     */
    public static function factory(
        string $id,
        string $url,
        string $version = null,
        array $deps = []
    ): Factory {
        return new Factory(['@plugin/dir_url'], function (string $dirUrl) use ($id, $url, $version, $deps) {
            return new self($id, $dirUrl . $url, $version, $deps);
        });
    }
}
