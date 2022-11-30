<?php

namespace RebelCode\WpSdk\Wp;

/**
 * An option type is responsible for parsing values read from the WordPress options table, and serializing values to be
 * written to the WordPress options table.
 *
 * @template T
 */
interface OptionType
{
    /**
     * Parses an option value, according to the option's type.
     *
     * @return T The parsed value.
     */
    public function parseValue($value);

    /**
     * Turns an option value into a string, if necessary, for storing in the database.
     *
     * @param T $value The value to serialize.
     * @return mixed The serialized value.
     */
    public function serializeValue($value);
}
