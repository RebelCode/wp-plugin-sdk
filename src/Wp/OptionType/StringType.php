<?php

namespace RebelCode\WpSdk\Wp\OptionType;

use RebelCode\WpSdk\Wp\OptionType;

/** The string type: parses and serializes values into strings. */
class StringType implements OptionType
{
    /** @inheritDoc */
    public function parseValue($value)
    {
        if (is_scalar($value)) {
            return (string) $value;
        } else {
            return '';
        }
    }

    /** @inheritDoc */
    public function serializeValue($value)
    {
        if (is_scalar($value)) {
            return (string) $value;
        } else {
            return '';
        }
    }
}
