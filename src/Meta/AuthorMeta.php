<?php

namespace RebelCode\WpSdk\Meta;

class AuthorMeta
{
    /** @var string The name of the plugin author. */
    public $name;

    /** @var string The URL to the plugin author's website. */
    public $url;

    /** Constructor. */
    public function __construct(string $name, string $url)
    {
        $this->name = $name;
        $this->url = $url;
    }
}
