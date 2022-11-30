<?php

namespace RebelCode\WpSdk\Wp;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

interface RestEndpointHandler
{
    /**
     * Handles a request.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function handle(WP_REST_Request $request);
}
