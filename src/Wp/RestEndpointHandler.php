<?php

namespace RebelCode\WpSdk\Wp;

use WP_Error as Error;
use WP_REST_Request as Request;
use WP_REST_Response as Response;

interface RestEndpointHandler
{
    /**
     * Prepares a response for the given request.
     *
     * @param Request $request The request.
     * @return Response|Error The response or error.
     */
    public function handle(Request $request);
}
