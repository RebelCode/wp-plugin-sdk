<?php

namespace RebelCode\WpSdk\Tests\Helpers;

trait WpTest
{
    public static function importWpCore()
    {
        require_once __DIR__ . '/../../vendor/roots/wordpress-no-content/wp-includes/class-wp-error.php';
    }

    public static function importWpRoles()
    {
        require_once __DIR__ . '/../../vendor/roots/wordpress-no-content/wp-includes/class-wp-role.php';
    }

    public static function importWpPost()
    {
        static::importWpCore();
        require_once __DIR__ . '/../../vendor/roots/wordpress-no-content/wp-includes/class-wp-post.php';
    }

    public static function importWpRestApi()
    {
        static::importWpCore();
        require_once __DIR__ . '/../../vendor/roots/wordpress-no-content/wp-includes/class-wp-http-response.php';
        require_once __DIR__ . '/../../vendor/roots/wordpress-no-content/wp-includes/rest-api/class-wp-rest-request.php';
        require_once __DIR__ . '/../../vendor/roots/wordpress-no-content/wp-includes/rest-api/class-wp-rest-response.php';
    }
}
