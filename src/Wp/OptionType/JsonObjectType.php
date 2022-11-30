<?php

namespace RebelCode\WpSdk\Wp\OptionType;

use RebelCode\WpSdk\Wp\OptionType;
use stdClass;

/**
 * The JSON object type: parses string values as JSON objects into {@link stdClass} objects, and serializes objects into
 * JSON strings.
 *
 * @template-implements OptionType<object>
 */
class JsonObjectType implements OptionType
{
    /** @inheritDoc */
    public function parseValue($value)
    {
        if (is_string($value)) {
            return (object) json_decode($value) ?? new stdClass();
        }

        if (is_object($value)) {
            return $value;
        }

        if (is_array($value)) {
            return (object) $value;
        }

        return new stdClass();
    }

    /** @inheritDoc */
    public function serializeValue($value)
    {
        if (is_object($value) || is_array($value)) {
            return json_encode($value);
        }

        if (is_string($value) && json_decode($value) !== null) {
            return $value;
        }

        return '{}';
    }
}
