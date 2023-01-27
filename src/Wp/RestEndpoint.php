<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;
use WP_Error;
use WP_REST_Request;

class RestEndpoint
{
    /** @var string */
    public $namespace;

    /** @var string */
    public $route;

    /** @var string[] */
    public $methods;

    /** @var RestEndpointHandler */
    public $handler;

    /** @var RestAuthGuard|null */
    public $authHandler;

    /** @var RestEndpointCallback|null */
    protected $_callback = null;

    /** @var callable|null */
    protected $_permissionCallback = null;

    /**
     * Constructor.
     *
     * @param string $namespace The REST API namespace.
     * @param string $route The route.
     * @param string[] $methods The accepted HTTP methods.
     * @param RestEndpointHandler $handler The handler.
     * @param RestAuthGuard|null $authHandler Optional authorization handler.
     */
    public function __construct(
        string $namespace,
        string $route,
        array $methods,
        RestEndpointHandler $handler,
        RestAuthGuard $authHandler = null
    ) {
        $this->namespace = $namespace;
        $this->route = $route;
        $this->methods = $methods;
        $this->handler = $handler;
        $this->authHandler = $authHandler;
    }

    /** Retrieves the callback for the endpoint. */
    public function getCallback(): RestEndpointCallback
    {
        return ($this->_callback === null)
            ? $this->_callback = new RestEndpointCallback($this->handler)
            : $this->_callback;
    }

    /** Retrieves the permission callback for the endpoint. */
    public function getPermissionCallback()
    {
        return ($this->_permissionCallback === null)
            ? $this->_permissionCallback = $this->createPermissionCallback($this->authHandler)
            : $this->_permissionCallback;
    }

    /**
     * Registers the endpoints to the WordPress REST API.
     *
     * @return bool True on success, false on error.
     */
    public function register(): bool
    {
        return register_rest_route($this->namespace, $this->route, [
            'methods' => $this->methods,
            'callback' => $this->getCallback(),
            'permission_callback' => $this->getPermissionCallback(),
        ]);
    }

    /**
     * Creates a permission callback for an auth guard instance.
     *
     * @param RestAuthGuard|null $auth The auth guard instance, if any.
     *
     * @return callable The callback.
     */
    public static function createPermissionCallback(?RestAuthGuard $auth)
    {
        if ($auth === null) {
            return '__return_true';
        }

        return function (WP_REST_Request $request) use ($auth) {
            $error = $auth->getAuthError($request);

            if ($error === null) {
                return true;
            }

            return new WP_Error('unauthorized', 'Unauthorized', [
                'status' => $error->status,
                'reasons' => $error->reasons,
            ]);
        };
    }

    /** Creates a factory for an endpoint. */
    public static function factory(
        string $ns,
        string $route,
        array $methods,
        string $handlerServiceId,
        ?string $authId = null
    ): Factory {
        return new Factory(
            [$handlerServiceId, $authId],
            function (RestEndpointHandler $handler, RestAuthGuard $auth) use ($ns, $route, $methods) {
                return new RestEndpoint($ns, $route, $methods, $handler, $auth);
            }
        );
    }
}
