<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;
use Dhii\Services\Service;

/**
 * Represents a page that is shown in the WordPress Admin.
 *
 * @psalm-import-type ServiceRef from Service
 */
class AdminPage
{
    /** @var string */
    public $title;

    /** @var callable */
    public $renderFn;

    /**
     * Constructor.
     *
     * @param string $title The title of the page, shown in the browser's tab.
     * @param callable $renderFn The function that returns the rendered contents of the page
     */
    public function __construct(string $title, callable $renderFn)
    {
        $this->title = $title;
        $this->renderFn = $renderFn;
    }

    /** Renders the contents of the page to a string. */
    public function render(...$args): string
    {
        return call_user_func($this->renderFn, ...$args);
    }

    /** Prints the contents of the page. */
    public function print(...$args): void
    {
        echo $this->render(...$args);
    }

    /** Retrieves the function that outputs the page's contents, for use with admin menus and callbacks. */
    public function getEchoFn(): callable
    {
        // The first argument is passed by WordPress and is unused
        return function ($unused, ...$args) {
            echo $this->render(...$args);
        };
    }

    /**
     * Creates a factory for an admin page, for use in modules.
     *
     * @param string $title The title of the page, shown in the browser's tab.
     * @param ServiceRef $renderFn The service for the function that returns the rendered contents of the page.
     */
    public static function factory(string $title, $renderFn): Factory
    {
        return new Factory([$renderFn], function ($renderFn) use ($title) {
            return new self($title, $renderFn);
        });
    }
}
