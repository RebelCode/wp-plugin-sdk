<?php

namespace RebelCode\WpSdk\Wp\RestAuthGuard;

use Dhii\Services\Factory;
use RebelCode\WpSdk\Wp\RestAuthError;
use RebelCode\WpSdk\Wp\RestAuthGuard;
use WP_REST_Request;

/** A REST API auth guard that checks if a user has a specific capability. */
class RestAuthUserCapability implements RestAuthGuard
{
    /** @var string */
    public $capability;

    /**
     * Constructor.
     *
     * @param string $capability The capability to check.
     */
    public function __construct(string $capability)
    {
        $this->capability = $capability;
    }

    /** @inheritDoc */
    public function getAuthError(WP_REST_Request $request): ?RestAuthError
    {
        $userId = get_current_user_id();

        if ($userId === 0) {
            return new RestAuthError(401, ['You must be logged in']);
        }

        if (!user_can($userId, $this->capability)) {
            return new RestAuthError(403, ['You do not have permission to complete this action']);
        }

        return null;
    }

    /** Creates a factory, for use in modules. */
    public static function factory(string $capability): Factory
    {
        return new Factory([], function () use ($capability) {
            return new self($capability);
        });
    }
}
