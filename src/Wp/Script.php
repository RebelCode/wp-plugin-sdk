<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;

/** A script asset implementation. */
class Script extends Asset
{
    /** @var ScriptL10n|null */
    public $l10n;

    /**
     * Constructor.
     *
     * @param string $id The ID of the script.
     * @param string $url The URL of the script file.
     * @param string|null $version The version number.
     * @param array $deps The IDs of the script's dependencies.
     * @param ScriptL10n|null $l10n The data to localize for the script.
     */
    public function __construct(
        string $id,
        string $url,
        string $version = null,
        array $deps = [],
        ?ScriptL10n $l10n = null
    ) {
        parent::__construct($id, $url, $version, $deps);
        $this->l10n = $l10n;
    }

    /** @inheritDoc */
    public function register(): bool
    {
        $this->isRegistered = wp_register_script(
            $this->id,
            $this->url,
            $this->deps,
            $this->version,
            true
        );

        if ($this->isRegistered && $this->l10n !== null) {
            return $this->l10n->localizeFor($this->id);
        } else {
            return true;
        }
    }

    /** @inheritDoc */
    protected function wpEnqueue(): void
    {
        wp_enqueue_script($this->id);
    }

    /**
     * Creates a script factory, for use in modules.
     *
     * @param string $id The ID of the script.
     * @param string $url The URL of the script file.
     * @param string|null $version The version number.
     * @param array $deps The IDs of the script's dependencies.
     * @param string|null $l10nId The ID of the service that provides the localization data.
     * @return Factory The factory for the script.
     */
    public static function factory(
        string $id,
        string $url,
        string $version = null,
        array $deps = [],
        ?string $l10nId = null
    ): Factory {
        $serviceDeps = ['@plugin/dir_url'];

        if ($l10nId !== null) {
            $serviceDeps[] = $l10nId;
        }

        return new Factory(
            $serviceDeps,
            function (string $dirUrl, ?ScriptL10n $l10n = null) use ($id, $url, $version, $deps) {
                return new self($id, $dirUrl . $url, $version, $deps, $l10n);
            }
        );
    }
}
