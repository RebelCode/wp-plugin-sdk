<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;
use WP_Role;

class Role
{
    /** @var string */
    public $id;

    /** @var string */
    public $label;

    /** @var array<string, bool> */
    public $capabilities;

    /**
     * Constructor.
     *
     * @param string $id The role ID.
     * @param string $label The role label.
     * @param array<string, bool> $capabilities The role capabilities.
     */
    public function __construct(string $id, string $label, array $capabilities = [])
    {
        $this->id = $id;
        $this->label = $label;
        $this->capabilities = $capabilities;
    }

    public function register(): ?WP_Role
    {
        return add_role($this->id, $this->label, $this->capabilities);
    }

    public function update(): ?WP_Role
    {
        remove_role($this->id);
        return $this->register();
    }

    /** Creates a factory for a role, for use in modules. */
    public static function factory(string $id, string $label, array $capabilities = []): Factory
    {
        return new Factory([], function () use ($id, $label, $capabilities) {
            return new self($id, $label, $capabilities);
        });
    }
}
