<?php

namespace RebelCode\WpSdk\Wp;

use Exception;
use Throwable;
use Traversable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/** The callback passed to the WordPress REST API, that invokes a REST API endpoint handler. */
class RestEndpointCallback
{
    /** @var RestEndpointHandler */
    protected $handler;

    /** @var bool */
    protected $responseSent = false;

    /** @var callable|null */
    protected $prevExHandler = null;

    /**
     * Constructor.
     *
     * @param RestEndpointHandler $handler The handler.
     */
    public function __construct(RestEndpointHandler $handler)
    {
        $this->handler = $handler;
    }

    /** @return WP_REST_Response|WP_Error */
    public function __invoke(WP_REST_Request $request)
    {
        $this->responseSent = false;
        $this->registerErrorHandler();

        try {
            $response = $this->handler->handle($request);

            if ($response instanceof WP_Error) {
                return $response;
            }

            $data = $response->get_data();

            // Turn the data into an array
            $arrayData = ($data instanceof Traversable)
                ? iterator_to_array($data)
                : (array) $data;

            $response->set_data($arrayData);

            $this->responseSent = true;
            $this->unregisterErrorHandler();

            return $response;
        } catch (Exception $exception) {
            return new WP_Error('internal_server_error', $exception->getMessage(), [
                'status' => 500,
                'details' => [
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ],
            ]);
        }
    }

    /** Registers the error handler that sends an erroneous response when an uncaught error is encountered. */
    protected function registerErrorHandler(): void
    {
        // Turn off WordPress' fatal error handler
        if (!defined('WP_SANDBOX_SCRAPING')) {
            define('WP_SANDBOX_SCRAPING', true);
        }

        // If we can't turn it off, do not register the error handler
        if (!WP_SANDBOX_SCRAPING) {
            return;
        }

        // This turns off handling for errors that aren't explicitly set to be handled by WordPress
        add_filter('wp_should_handle_php_error', '__return_false');

        // Register our own shutdown function to handle errors
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error === null) {
                return;
            }

            $this->sendError($error['type'], $error['message'], $error['file'], $error['line']);
        });

        // Register the exception handler
        $this->prevExHandler = set_exception_handler(function (Throwable $exception) {
            $this->sendError(
                $exception->getCode(),
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            );
        });
    }

    /** Unregisters the error handlers. */
    protected function unregisterErrorHandler(): void
    {
        remove_filter('wp_should_handle_php_error', '__return_false');
        set_exception_handler($this->prevExHandler);
    }

    /**
     * Sends an erroneous response and terminates execution.
     *
     * @param int|string $code The error code.
     * @param string $message The error message.
     * @param string $file
     * @param int $line
     * @return never-returns
     */
    protected function sendError($code, string $message = '', string $file = '', int $line = 0): void
    {
        if ($this->responseSent) {
            return;
        }

        $lines = explode("\n", $message);

        http_response_code(500);
        header('Content-type: application/json');

        $errorData = [
            'code' => $code,
            'message' => $lines[0],
            'details' => $message,
            'file' => $file,
            'line' => $line,
        ];

        echo json_encode($errorData);

        die;
    }
}
