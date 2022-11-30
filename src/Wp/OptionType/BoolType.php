<?php

namespace RebelCode\WpSdk\Wp\OptionType;

use RebelCode\WpSdk\Wp\OptionType;

/** The boolean type: parses values into booleans and serializes booleans into '1' and '0' strings. */
class BoolType implements OptionType
{
    /** @inheritDoc */
    public function parseValue($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /** @inheritDoc */
    public function serializeValue($value)
    {
        return !!$value ? '1' : '0';
    }
}
