<?php

namespace RebelCode\WpSdk\Wp;

use Dhii\Services\Factory;

class PostType
{
    /** @var string */
    public $slug;

    /** @var array */
    public $args;

    /**
     * Constructor.
     *
     * @param string $slug The post type key. Must not exceed 20 characters and may only contain lowercase alphanumeric
     *                     characters, dashes, and underscores.
     * @param array<string, mixed> $args The post type arguments. See {@link register_post_type()} for available args.
     */
    public function __construct(string $slug, array $args = [])
    {
        $this->slug = $slug;
        $this->args = $args;
    }

    /**
     * Creates a copy of the post type that has a given set of arguments merged with the original arguments.
     *
     * @param array<string, mixed> $args The arguments to add to the original arguments.
     * @return self The new post type instance.
     */
    public function withAddedArgs(array $args): self
    {
        $clone = clone $this;
        $clone->args = array_merge($clone->args, $args);

        return $clone;
    }

    /**
     * Creates a copy of the post type with auto-generated labels.
     *
     * @param string $singularName The singular name of the post type.
     * @param string $pluralName The plural name of the post type.
     * @param string $textDomain The text domain to use for translations.
     * @return self The new post type instance.
     */
    public function withAutoLabels(string $singularName, string $pluralName, string $textDomain = 'default'): self
    {
        $s = $singularName;
        $p = $pluralName;
        $ls = strtolower($s);
        $lp = strtolower($p);
        $d = $textDomain;

        $labels = [
            'name' => $p,
            'singular_name' => $s,
            'menu_name' => $p,
            'add_new' => __('Add New'),
            'add_new_item' => sprintf(_x('Add New %s', '%s = singular post type name', $d), $s),
            'edit_item' => sprintf(_x('Edit %s', '%s = singular post type name', $d), $s),
            'new_item' => sprintf(_x('New %s', '%s = singular post type name', $d), $s),
            'view_item' => sprintf(_x('View %s', '%s = singular post type name', $d), $s),
            'view_items' => sprintf(_x('View %s', '%s = plural post type name', $d), $p),
            'search_items' => sprintf(_x('Search %s', '%s = plural post type name', $d), $p),
            'not_found' => sprintf(_x('No %s found', '%s = plural post type name', $d), $lp),
            'not_found_in_trash' => sprintf(_x('No %s found in Trash', '%s = plural post type name', $d), $lp),
            'parent_item_colon' => sprintf(_x('Parent %s:', '%s = singular post type name', $d), $s),
            'all_items' => sprintf(_x('All %s', '%s = plural post type name', $d), $p),
            'archives' => sprintf(_x('%s Archives', '%s = singular post type name', $d), $s),
            'attributes' => sprintf(_x('%s Attributes', '%s = singular post type name', $d), $s),
            'insert_into_item' => sprintf(_x('Insert into %s', '%s = singular post type name', $d), $ls),
            'uploaded_to_this_item' => sprintf(_x('Uploaded to this %s', '%s = singular post type name', $d), $ls),
            'featured_image' => __('Featured Image'),
            'remove_featured_image' => __('Remove featured image'),
            'use_featured_image' => __('Use as featured image'),
            'set_featured_image' => __('Set featured image'),
            'filter_items_list' => sprintf(_x('Filter %s list', '%s = plural post type name', $d), $lp),
            'filter_by_date' => __('Filter by date'),
            'items_list_navigation' => sprintf(_x('%s list navigation', '%s = plural post type name', $d), $p),
            'items_list' => sprintf(_x('%s list', '%s = plural post type name', $d), $p),
            'item_published' => sprintf(_x('%s published.', '%s = singular post type name', $d), $s),
            'item_published_privately' => sprintf(
                _x('%s published privately.', '%s = singular post type name', $d),
                $s
            ),
            'item_reverted_to_draft' => sprintf(_x('%s reverted to draft.', '%s = singular post type name', $d), $s),
            'item_scheduled' => sprintf(_x('%s scheduled.', '%s = singular post type name', $d), $s),
            'item_updated' => sprintf(_x('%s updated.', '%s = singular post type name', $d), $s),
            'item_link' => sprintf(_x('%s link', '%s = singular post type name', $d), $s),
            'item_link_description' => sprintf(_x('A link to a %s', '%s = singular post type name', $d), $s),
        ];

        return $this->withAddedArgs(['labels' => $labels]);
    }

    /**
     * Creates a copy of the post type and sets arguments related to the REST API.
     *
     * @param string|null $base The base slug to use for the endpoint. If null, the post type slug will be used.
     * @param string|null $ns The namespace to use for the endpoint. If null, the post type slug will be used.
     * @param string|null $controllerClass The controller class name. Default is {@link \WP_REST_Posts_Controller}.
     * @return self The new post type instance.
     */
    public function withRestApi(?string $base = null, ?string $ns = null, ?string $controllerClass = null): self
    {
        $args = ['show_in_rest' => true];

        if ($base !== null) {
            $args['rest_base'] = $base;
        }

        if ($ns !== null) {
            $args['rest_namespace'] = $ns;
        }

        if ($controllerClass !== null) {
            $args['rest_controller_class'] = $controllerClass;
        }

        return $this->withAddedArgs($args);
    }

    /**
     * Creates a copy of the post type and sets the relevant arguments that remove all UI associated with the post type.
     *
     * @return self The new post type instance.
     */
    public function withNoUi(): self
    {
        return $this->withAddedArgs([
            'public' => false,
            'has_archive' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_ui' => false,
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
        ]);
    }

    /**
     * Creates a copy of the post type and sets the arguments such that only the admin-side UI is enabled.
     *
     * @param bool $adminMenu Whether to show the post type in the admin menu. Default is true.
     * @param bool $adminBar Whether to show the post type in the admin bar. Default is true.
     * @param bool $navMenu Whether to show the post type in navigation menus. Default is true.
     * @return self The new post type instance.
     */
    public function withAdminUiOnly(bool $adminMenu = true, bool $adminBar = true, bool $navMenu = true): self
    {
        return $this->withAddedArgs([
            'public' => false,
            'has_archive' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_ui' => true,
            'show_in_menu' => $adminMenu,
            'show_in_admin_bar' => $adminBar,
            'show_in_nav_menus' => $navMenu,
        ]);
    }

    /** Registers the post type to WordPress. */
    public function register()
    {
        register_post_type($this->slug, $this->args);
    }

    /** Creates a factory for a post type. */
    public static function factory(string $slug, array $args): Factory
    {
        return new Factory([], function () use ($slug, $args) {
            return new self($slug, $args);
        });
    }
}
