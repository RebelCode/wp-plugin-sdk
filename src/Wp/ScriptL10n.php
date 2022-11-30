<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;

class ScriptL10n
{
    /** @var string */
    public $name;

    /** @var array */
    public $data;

    /**
     * Constructor.
     *
     * @param string $name The name of the l10n variable.
     * @param array $data The l10n data.
     */
    public function __construct(string $name, array $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * Adds the localization to a script.
     *
     * @param string $id The ID of the script.
     * @return bool True if the script was successfully localized, false otherwise.
     */
    public function localizeFor(string $id): bool
    {
        return wp_localize_script($id, $this->name, $this->data);
    }

    /** Creates a factory service. */
    public static function factory(string $name, array $data): Factory
    {
        return new Factory([], function () use ($name, $data) {
            return new self($name, $data);
        });
    }
}
