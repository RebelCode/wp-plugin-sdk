<?php

namespace RebelCode\WpSdk\Wp\OptionType;

use RebelCode\WpSdk\Wp\OptionType;

/** The float type: parses values into floats and serializes values into strings. */
class FloatType implements OptionType
{
    /** @inheritDoc */
    public function parseValue($value): float
    {
        if (is_scalar($value)) {
            return (float) (string) $value;
        } else {
            return 0.0;
        }
    }

    /** @inheritDoc */
    public function serializeValue($value)
    {
        if (is_scalar($value)) {
            return (string) (float) $value;
        } else {
            return '0';
        }
    }
}
