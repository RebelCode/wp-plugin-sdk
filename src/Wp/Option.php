<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;
use RebelCode\WpSdk\Wp\OptionType\DefaultType;

/** @template T */
class Option extends AbstractOption
{
    /** Whether the option is autoloaded. */
    public $autoload;

    /**
     * Constructor.
     *
     * @param string $name The option's name.
     * @param OptionType<T>|null $type The option's type. If null, {@link DefaultType} will be used.
     * @param T|null $default The default value to use when the option does not exist.
     * @param bool $autoload Whether to autoload the option.
     */
    public function __construct(string $name, ?OptionType $type = null, $default = null, bool $autoload = false)
    {
        parent::__construct($name, $type, $default);
        $this->autoload = $autoload;
    }

    /**
     * Deletes the option.
     *
     * @return bool True if the value was updated, false otherwise.
     */
    public function delete(): bool
    {
        return delete_option($this->name);
    }

    /** @inheritDoc */
    protected function read()
    {
        return get_option($this->name, null);
    }

    /** @inheritDoc */
    protected function write($value): bool
    {
        return update_option($this->name, $value, $this->autoload);
    }

    /**
     * Creates a factory for an option, for use in modules.
     *
     * @param string $name The option's name.
     * @param OptionType|string $type The option's type instance or service ID.
     * @param mixed|null $default The default value to use when the option does not exist.
     * @param bool $autoload Whether to autoload the option.
     * @return Factory The created factory.
     */
    public static function factory(string $name, $type, $default = null, bool $autoload = false): Factory
    {
        if (is_string($type)) {
            $deps = [$type];
            $type = null;
        } else {
            $deps = [];
        }

        return new Factory($deps, function (?OptionType $typeDep = null) use ($type, $name, $default, $autoload) {
            return new self($name, $type ?? $typeDep, $default, $autoload);
        });
    }
}
