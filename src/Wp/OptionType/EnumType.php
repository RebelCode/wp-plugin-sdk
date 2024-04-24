<?php

namespace RebelCode\WpSdk\Wp\OptionType;

use RebelCode\WpSdk\Wp\OptionType;

/** The string type: parses and serializes values into strings. */
class EnumType implements OptionType
{
    /** @var array<string> */
    protected array $options;

    /**
     * Constructor.
     * @param array<string> $options The possible values for the enum.
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /** @inheritDoc */
    public function parseValue($value)
    {
        if (is_scalar($value) && in_array($value, $this->options, true)) {
            return (string) $value;
        } else {
            return reset($this->options) ?: '';
        }
    }

    /** @inheritDoc */
    public function serializeValue($value)
    {
        return $this->parseValue($value);
    }
}
