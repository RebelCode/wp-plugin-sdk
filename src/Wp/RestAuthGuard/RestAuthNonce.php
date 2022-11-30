<?php

namespace RebelCode\WpSdk\Wp\RestAuthGuard;

use Dhii\Services\Factory;
use RebelCode\WpSdk\Wp\RestAuthError;
use RebelCode\WpSdk\Wp\RestAuthGuard;
use WP_REST_Request;

/** A REST API auth guard that checks a WordPress nonce in the request. */
class RestAuthNonce implements RestAuthGuard
{
    protected const DEF_NONCE_FIELD = '_wpnonce';

    /** @var string */
    public $nonceAction;

    /** @var string */
    public $nonceParam;

    /**
     * Constructor.
     *
     * @param string $nonceAction The nonce action.
     * @param string $nonceParam The nonce param.
     */
    public function __construct(string $nonceAction, string $nonceParam = self::DEF_NONCE_FIELD)
    {
        $this->nonceAction = $nonceAction;
        $this->nonceParam = $nonceParam;
    }

    /** @inheritDoc */
    public function getAuthError(WP_REST_Request $request): ?RestAuthError
    {
        $nonce = $request->get_param($this->nonceParam) ?? '';

        return wp_verify_nonce($nonce, $this->nonceAction)
            ? null
            : new RestAuthError(403, ['You do not have permission to complete this action']);
    }

    public static function factory(string $nonceAction, string $nonceParam = self::DEF_NONCE_FIELD): Factory
    {
        return new Factory([], function () use ($nonceAction, $nonceParam) {
            return new self($nonceAction, $nonceParam);
        });
    }
}
