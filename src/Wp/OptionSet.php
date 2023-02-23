<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;
use Dhii\Services\Service;

/**
 * @psalm-import-type ServiceRef from Service
 */
class OptionSet
{
    /** @var AbstractOption[] An associative array of options. */
    protected $options;

    /**
     * Constructor.
     *
     * @param AbstractOption[] $options An associative array of options. The keys are used by {@link getOption()} to
     *                                  retrieve the options.
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Retrieves all the options in the set.
     *
     * @return AbstractOption[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /** Returns the option in the set that corresponds to the given key, or null if no such option exists. */
    public function getOption(string $key): ?AbstractOption
    {
        return $this->options[$key] ?? null;
    }

    /** Retrieves the value of the option that corresponds to the given key, or null if no such option exists. */
    public function get(string $key)
    {
        $option = $this->getOption($key);

        return $option ? $option->getValue() : null;
    }

    /** Sets the value of the option that corresponds to the given key, or null if no such option exists. */
    public function set(string $key, $value): bool
    {
        $option = $this->getOption($key);

        return $option && $option->setValue($value);
    }

    /** Deletes the value of the option that corresponds to the given key, or null if no such option exists. */
    public function delete(string $key): bool
    {
        $option = $this->getOption($key);

        return $option && $option->delete();
    }

    /** Retrieves the values for all the options in the set as an associative array. */
    public function getAll(): array
    {
        $result = [];

        foreach ($this->options as $key => $option) {
            $result[$key] = $option->getValue();
        }

        return $result;
    }

    /** Updates the values of the options in the set. */
    public function update(array $changes): void
    {
        foreach ($changes as $key => $value) {
            $this->set($key, $value);
        }
    }

    /** Deletes the values for all the options in the set. */
    public function deleteAll(): void
    {
        foreach ($this->options as $option) {
            $option->delete();
        }
    }

    /**
     * Creates a factory for an option set.
     *
     * @param ServiceRef[] $options An array containing the services of the options to include in the set.
     * @return Factory The created factory.
     */
    public static function factory(array $options): Factory
    {
        return new Factory($options, function (AbstractOption ...$options) {
            return new OptionSet($options);
        });
    }
}
