<?php

namespace RebelCode\WpSdk\Wp;

use RebelCode\WpSdk\Wp\OptionType\BoolType;
use RebelCode\WpSdk\Wp\OptionType\DefaultType;
use RebelCode\WpSdk\Wp\OptionType\FloatType;
use RebelCode\WpSdk\Wp\OptionType\IntType;
use RebelCode\WpSdk\Wp\OptionType\JsonArrayType;
use RebelCode\WpSdk\Wp\OptionType\JsonObjectType;
use RebelCode\WpSdk\Wp\OptionType\StringType;

/** @template T */
abstract class AbstractOption
{
    /** @var OptionType The option's value type. See the constants in this class. */
    public $type;

    /** The name of the option. */
    public $name;

    /** @var T The default value to use if the option does not exist. */
    public $default;

    /**
     * Constructor.
     *
     * @param string $name The option's name.
     * @param OptionType<T>|null $type The option's type. If null, {@link DefaultType} will be used.
     * @param T|null $default The default value to use when the option does not exist.
     */
    public function __construct(string $name, ?OptionType $type = null, $default = null)
    {
        $this->type = $type;
        $this->name = $name;
        $this->default = $default;
    }

    /**
     * Gets the value of the option.
     *
     * @return T
     */
    public function getValue()
    {
        $raw = $this->read();

        if ($raw === null) {
            return $this->default;
        } else {
            return $this->type->parseValue($raw);
        }
    }

    /**
     * Sets the option's value.
     *
     * @param T $value The new value.
     * @return bool True if the value was updated, false otherwise.
     */
    public function setValue($value): bool
    {
        return $this->write($this->type->serializeValue($value));
    }

    /**
     * Deletes the option.
     *
     * @return bool True if the value was updated, false otherwise.
     */
    abstract public function delete(): bool;

    /**
     * Reads the raw value from the database, prior to type conversion.
     *
     * @return mixed The value read from the database.
     */
    abstract protected function read();

    /**
     * Writes the value to the database, after type conversion.
     *
     * @param mixed $value The value to write to the database.
     * @return bool True if the value was written successfully, false otherwise.
     */
    abstract protected function write($value): bool;

    /** Retrieves the default type. */
    public static function default(): DefaultType
    {
        static $cache = null;
        return $cache ?? $cache = new DefaultType();
    }

    /** Retrieves the string type. */
    public static function string(): StringType
    {
        static $cache = null;
        return $cache ?? $cache = new StringType();
    }

    /** Retrieves the boolean type. */
    public static function bool(): BoolType
    {
        static $cache = null;
        return $cache ?? $cache = new BoolType();
    }

    /** Retrieves the integer type. */
    public static function int(): IntType
    {
        static $cache = null;
        return $cache ?? $cache = new IntType();
    }

    /** Retrieves the float type. */
    public static function float(): FloatType
    {
        static $cache = null;
        return $cache ?? $cache = new FloatType();
    }

    /** Retrieves the JSON array type. */
    public static function jsonArray(): JsonArrayType
    {
        static $cache = null;
        return $cache ?? $cache = new JsonArrayType();
    }

    /** Retrieves the JSON object type. */
    public static function jsonObject(): JsonObjectType
    {
        static $cache = null;
        return $cache ?? $cache = new JsonObjectType();
    }
}
