<?php

namespace RebelCode\WpSdk\Wp\OptionType;

use RebelCode\WpSdk\Wp\OptionType;

/**
 * The JSON array type: parses string values as JSON arrays into PHP arrays, and serializes PHP arrays into JSON
 * strings.
 *
 * @template-implements OptionType<array>
 */
class JsonArrayType implements OptionType
{
    /** @inheritDoc */
    public function parseValue($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return (array) $value;
        }

        return [];
    }

    /** @inheritDoc */
    public function serializeValue($value)
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        if (is_object($value)) {
            return json_encode((array) $value);
        }

        if (is_string($value) && json_decode($value) !== null) {
            return $value;
        }

        return '[]';
    }
}
