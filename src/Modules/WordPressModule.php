<?php

namespace RebelCode\WpSdk\Modules;

use Dhii\Services\Factories\GlobalVar;
use Dhii\Services\Factories\Value;
use RebelCode\WpSdk\Handler;
use RebelCode\WpSdk\Module;
use RebelCode\WpSdk\Wp\AdminMenu;
use RebelCode\WpSdk\Wp\NoticeManager;
use RebelCode\WpSdk\Wp\RestEndpoint;
use RebelCode\WpSdk\Wp\Shortcode;
use WP_Block_Type;

/**
 * A module that adds base services for WordPress entities and hooks to register them.
 *
 * This module is intended to be used as a base, with other modules extending its services to implicitly register
 * WordPress entities.
 */
class WordPressModule extends Module
{
    /** @inheritDoc */
    public function getHooks(): array
    {
        return [
            'init' => new Handler(
                ['post_types', 'shortcodes', 'block_types'],
                function (array $postTypes, array $shortcodes, array $blockTypes) {
                    foreach ($postTypes as $postType) {
                        $postType->register();
                    }
                    foreach ($shortcodes as $shortcode) {
                        /** @var $shortcode Shortcode */
                        $shortcode->register();
                    }
                    foreach ($blockTypes as $blockType) {
                        /** @var $blockType WP_Block_Type */
                        register_block_type($blockType);
                    }
                }
            ),

            'plugins_loaded' => new Handler(
                ['cron_jobs'],
                function (array $cronJobs) {
                    foreach ($cronJobs as $cronJob) {
                        $cronJob->registerHandlers();
                    }
                }
            ),

            'admin_init' => new Handler(
                ['admin_menus', 'notices/manager'],
                function (array $menus, NoticeManager $manager) {
                    if (!$manager->handleAjax($_POST)) {
                        die;
                    }

                    foreach ($menus as $menu) {
                        /** @var $menu AdminMenu */
                        $menu->register();
                    }
                }
            ),

            'rest_api_init' => new Handler(
                ['rest_endpoints'],
                function (array $endpoints) {
                    foreach ($endpoints as $endpoint) {
                        /** @var $endpoint RestEndpoint */
                        $endpoint->register();
                    }
                }
            ),
        ];
    }

    /** @inheritDoc */
    public function getFactories(): array
    {
        return [
            'db' => new GlobalVar('wpdb'),
            'post_types' => new Value([]),
            'notices' => new Value([]),
            'notices/manager' => NoticeManager::factory('@plugin/code', 'notices'),
            'admin_menus' => new Value([]),
            'cron_jobs' => new Value([]),
            'rest_endpoints' => new Value([]),
            'shortcodes' => new Value([]),
            'block_types' => new Value([]),
        ];
    }
}
