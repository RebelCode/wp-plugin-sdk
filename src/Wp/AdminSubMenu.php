<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;

/** Represents a WordPress admin submenu that appears under a top-level menu. */
class AdminSubMenu
{
    /** @var string */
    public $slug;
    /** @var string */
    public $label;
    /** @var string */
    public $capability;
    /** @var int|null */
    public $position;
    /** @var AdminPage|null */
    public $page;
    /** @var string|null */
    public $url;

    /**
     * Creates a sub-menu instance that refers to a page.
     *
     * @param AdminPage $page The page that the submenu refers to.
     * @param string $slug The slug name for the submenu.
     * @param string $label The label to show for this submenu in the WP admin menu bar.
     * @param string $cap The user capability required to show the menu and access the page.
     * @param int|null $pos The position of the submenu in its parent menu's container.
     *
     * @return static The created submenu instance.
     */
    public static function forPage(AdminPage $page, string $slug, string $label, string $cap, int $pos = null): self
    {
        $submenu = new self();
        $submenu->slug = $slug;
        $submenu->label = $label;
        $submenu->capability = $cap;
        $submenu->position = $pos;
        $submenu->page = $page;
        $submenu->url = null;

        return $submenu;
    }

    /**
     * Creates a sub-menu instance that points to a URL.
     *
     * @param string $url The absolute URL that the submenu points to.
     * @param string $label The label to show for this submenu in the WP admin menu bar.
     * @param string $capability The user capability required to show the menu and access the page.
     * @param int|null $position The position of the submenu in its parent menu's container.
     *
     * @return static The created submenu instance.
     */
    public static function forUrl(string $url, string $label, string $capability, int $position = null): self
    {
        $submenu = new self();
        $submenu->slug = '';
        $submenu->label = $label;
        $submenu->capability = $capability;
        $submenu->position = $position;
        $submenu->url = $url;
        $submenu->page = null;

        return $submenu;
    }

    /**
     * Registers the submenu to a parent menu.
     *
     * @param string $parentSlug The slug name of the parent menu.
     */
    public function registerFor(string $parentSlug)
    {
        if (!current_user_can($this->capability)) {
            return;
        }

        if ($this->page instanceof AdminPage && $this->url === null) {
            add_submenu_page(
                $parentSlug,
                $this->page->title,
                $this->label,
                $this->capability,
                $this->slug,
                $this->page->getEchoFn(),
                $this->position
            );
        } elseif (is_string($this->url) && strlen($this->url) > 0 && $this->page === null) {
            global $submenu;

            // Add to the menu
            $submenu[$parentSlug] = $submenu[$parentSlug] ?? [];
            $submenu[$parentSlug][] = [
                $this->label,
                $this->capability,
                $this->url,
                $this->label,
            ];
        }
    }

    /** Creates a factory for a page submenu, for use in modules. */
    public static function factoryForPage(
        string $pageId,
        string $slug,
        string $label,
        string $cap,
        int $pos = null
    ): Factory {
        return new Factory([$pageId], function ($page) use ($slug, $label, $cap, $pos) {
            return self::forPage($page, $slug, $label, $cap, $pos);
        });
    }

    /** Creates a factory for a URL submenu, for use in modules. */
    public static function factoryForUrl(
        string $url,
        string $label,
        string $cap,
        int $pos = null
    ): Factory {
        return new Factory([], function () use ($url, $label, $cap, $pos) {
            return self::forUrl($url, $label, $cap, $pos);
        });
    }
}
