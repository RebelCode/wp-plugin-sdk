<?php

namespace RebelCode\WpSdk\Wp\OptionType;

use RebelCode\WpSdk\Wp\OptionType;

/** The integer type: parses values into integers and serializes values into strings. */
class IntType implements OptionType
{
    /** @inheritDoc */
    public function parseValue($value)
    {
        if (is_scalar($value)) {
            return (int) (string) $value;
        } else {
            return 0;
        }
    }

    /** @inheritDoc */
    public function serializeValue($value)
    {
        if (is_scalar($value)) {
            return (string) (int) $value;
        } else {
            return '0';
        }
    }
}
