<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;

/**
 * Represents a WordPress transient in an immutable object form.
 *
 * @template T
 * @template-extends AbstractOption<T>
 */
class Transient extends AbstractOption
{
    /** @var int */
    public $expiry;

    /**
     * Constructor.
     *
     * @param string $name The option's name.
     * @param OptionType<T>|null $type The option's type. If null, {@link DefaultType} will be used.
     * @param int $expiry The number of seconds until the transient expires, or 0 for no expiration.
     * @param T|null $default The default value to use when the option does not exist.
     */
    public function __construct(string $name, ?OptionType $type = null, int $expiry = 0, $default = null)
    {
        parent::__construct($name, $type, $default);
        $this->expiry = $expiry;
    }

    /** @inheritDoc */
    protected function read()
    {
        $value = get_transient($this->name);

        return ($value === false) ? null : $value;
    }

    /** @inheritDoc */
    protected function write($value): bool
    {
        return set_transient($this->name, $value, $this->expiry);
    }

    /**
     * Deletes the transient.
     *
     * @return bool True if the transient was updated, false otherwise.
     */
    public function delete(): bool
    {
        return delete_transient($this->name);
    }

    /**
     * Creates a factory for a transient, for use in modules.
     *
     * @param string $name The option's name.
     * @param OptionType|string $type The option's type instance or service ID.
     * @param int $expiry The number of seconds until the transient expires, or 0 for no expiration.
     * @param mixed|null $default The default value to use when the option does not exist.
     * @return Factory The created factory.
     */
    public static function factory(string $name, $type, int $expiry, $default = null): Factory
    {
        if (is_string($type)) {
            $deps = [$type];
            $type = null;
        } else {
            $deps = [];
        }

        return new Factory($deps, function (?OptionType $typeDep = null) use ($type, $name, $default, $expiry) {
            return new self($name, $type ?? $typeDep, $expiry, $default);
        });
    }
}
