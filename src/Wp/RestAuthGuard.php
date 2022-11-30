<?php

namespace RebelCode\WpSdk\Wp;

use WP_REST_Request;

/**
 * Represents an authorization guard for the REST API.
 *
 * Objects that implement this interface are used to determine if a client is authorized and authenticated to carry
 * out a specific request.
 */
interface RestAuthGuard
{
    /**
     * Retrieves any
     *
     * @param WP_REST_Request $request The request.
     *
     * @return RestAuthError|null The authorization error, or null if the request was authenticated and authorized.
     */
    public function getAuthError(WP_REST_Request $request): ?RestAuthError;
}
