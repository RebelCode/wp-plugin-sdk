<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;

/** Represents a top-level WordPress admin menu. */
class AdminMenu
{
    /** @var string */
    public $slug;
    /** @var string */
    public $label;
    /** @var string */
    public $capability;
    /** @var string */
    public $icon;
    /** @var int|null */
    public $position;
    /** @var AdminPage */
    public $page;
    /** @var AdminSubMenu[] */
    public $items;

    /**
     * Constructor.
     *
     * @param AdminPage $page The page that the menu refers to.
     * @param string $slug The slug name for the menu.
     * @param string $label The label to show in the WP admin menu bar.
     * @param string $capability The user capability required to show the menu and access the page.
     * @param string $icon The icon to show near the menu's label in the WP admin menu bar.
     * @param int|null $position The position of the menu in the WP admin menu bar.
     * @param AdminSubMenu[] $items The submenu items for this menu.
     */
    public function __construct(
        AdminPage $page,
        string $slug,
        string $label,
        string $capability,
        string $icon = '',
        ?int $position = null,
        array $items = []
    ) {
        $this->page = $page;
        $this->slug = $slug;
        $this->label = $label;
        $this->capability = $capability;
        $this->icon = $icon;
        $this->position = $position;
        $this->items = $items;
    }

    /** Adds a submenu item to the menu. */
    public function addSubMenu(AdminSubMenu $subMenu): void
    {
        $this->items[] = $subMenu;
    }

    /**
     * Registers a menu with WordPress.
     */
    public function register()
    {
        if (!current_user_can($this->capability)) {
            return;
        }

        add_menu_page(
            $this->page->title,
            $this->label,
            $this->capability,
            $this->slug,
            $this->page->getEchoFn(),
            $this->icon,
            $this->position
        );

        foreach ($this->items as $item) {
            $item->registerFor($this->slug);
        }
    }

    /** Creates a factory for an admin menu, for use in modules. */
    public static function factory(
        string $pageId,
        string $slug,
        string $label,
        string $cap,
        string $icon = '',
        ?int $position = null,
        array $itemIds = []
    ): Factory {
        return new Factory(
            array_merge([$pageId], $itemIds),
            function ($page, ...$items) use ($slug, $label, $cap, $icon, $position) {
                return new self($page, $slug, $label, $cap, $icon, $position, $items);
            }
        );
    }
}
