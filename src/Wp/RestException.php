<?php

namespace RebelCode\WpSdk\Wp;

use RuntimeException;
use Throwable;
use WP_REST_Response;

class RestException extends RuntimeException
{
    /** @var mixed */
    protected $data;
    protected int $status;

    /**
     * Constructor.
     * @param string $message The error message.
     * @param mixed $data The response data for the error.
     * @param int $status The status code of the error response.
     * @param Throwable|null $prev The previous exception, if any.
     */
    public function __construct(string $message, $data, int $status = 500, ?Throwable $prev = null)
    {
        parent::__construct($message, 0, $prev);
        $this->data = $data;
        $this->status = $status;
    }

    /** Gets a REST response for this exception. */
    public function getResponse(): WP_REST_Response
    {
        return new WP_REST_Response($this->data, $this->status);
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    /** @return mixed */
    public function getResponseData()
    {
        return $this->data;
    }
}
