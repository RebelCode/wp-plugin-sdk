<?php

namespace RebelCode\WpSdk\Wp;

use Exception;
use Traversable;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/** The callback passed to the WordPress REST API, that invokes a REST API endpoint handler. */
class RestEndpointCallback
{
    /** @var RestEndpointHandler */
    protected $handler;

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
        try {
            $response = $this->handler->handle($request);

            if ($response instanceof WP_Error) {
                return $response;
            }

            $data = $response->get_data();

            if (!is_scalar($data)) {
                // Turn the data into an array
                $arrayData = ($data instanceof Traversable)
                    ? iterator_to_array($data)
                    : (array) $data;

                $response->set_data($arrayData);
            }

            return $response;
        } catch (RestException $exception) {
            return $exception->getResponse();
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
}
