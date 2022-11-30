<?php

namespace RebelCode\WpSdk\Wp;

/** Represents an asset, a WordPress mechanism used for enqueueing scripts and stylesheets. */
abstract class Asset
{
    /** @var string */
    public $id;

    /** @var string */
    public $url;

    /** @var string[] */
    public $deps;

    /** @var string|null */
    public $version;

    /** @var bool */
    public $isRegistered = false;

    /**
     * Constructor.
     *
     * @param int $type Either {@link Asset::SCRIPT} or {@link Asset::style}.
     * @param string $id The ID of the asset.
     * @param string $url The URL of the asset.
     * @param string|null $version The version of the asset, used for caching.
     * @param string[] $deps Keys of dependency assets of the same type.
     * @param ScriptL10n|null $l10n Optional l10n, for scripts only.
     */
    public function __construct(
        string $id,
        string $url,
        string $version = null,
        array $deps = []
    ) {
        $this->id = $id;
        $this->url = $url;
        $this->version = $version;
        $this->deps = $deps;
    }

    /** Enqueues the asset. */
    public function enqueue(): void
    {
        if (!$this->isRegistered) {
            $this->register();
        }

        $this->wpEnqueue();
    }

    /**
     * Registers the asset.
     *
     * This method should set the {@link $isRegistered} property to true if the asset was successfully registered, as
     * well as return its value.
     */
    abstract public function register(): bool;

    /** Abstraction for the relevant `wp_enqueue_xxx` function. */
    abstract protected function wpEnqueue();
}
