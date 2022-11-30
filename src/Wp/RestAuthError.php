<?php

namespace RebelCode\WpSdk\Wp;

class RestAuthError
{
    /** @var int */
    public $status;

    /** @var string[] */
    public $reasons;

    /**
     * Constructor.
     *
     * @param int $status The HTTP status code.
     * @param string[] $reasons The human-readable reasons for the error.
     */
    public function __construct(int $status, array $reasons)
    {
        $this->status = $status;
        $this->reasons = $reasons;
    }
}
