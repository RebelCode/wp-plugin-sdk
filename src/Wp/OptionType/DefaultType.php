<?php

namespace RebelCode\WpSdk\Wp\OptionType;

use RebelCode\WpSdk\Wp\OptionType;

/** The default option type. Performs no parsing and no serialization. */
class DefaultType implements OptionType
{
    /** @inheritDoc */
    public function parseValue($value)
    {
        return $value;
    }

    /** @inheritDoc */
    public function serializeValue($value)
    {
        return $value;
    }
}
