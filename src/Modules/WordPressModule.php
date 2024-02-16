<?php

namespace RebelCode\WpSdk\Modules;

use Dhii\Services\Factories\GlobalVar;
use Dhii\Services\Factories\Value;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Handler;
use RebelCode\WpSdk\Module;
use RebelCode\WpSdk\Plugin;
use RebelCode\WpSdk\Wp\NoticeManager;

/**
 * A module that adds base services for WordPress entities and hooks to register them.
 *
 * This module is intended to be used as a base, with other modules extending its services to implicitly register
 * WordPress entities.
 */
class WordPressModule extends Module
{
    /** @inheritDoc */
    public function run(ContainerInterface $c, Plugin $p): void
    {
        $c->get('notices/manager')->listenForRequests();
    }

    /** @inheritDoc */
    public function getHooks(): iterable
    {
        yield 'init' => new Handler(
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
        );

        yield 'plugins_loaded' => new Handler(
            ['cron_jobs'],
            function (array $cronJobs) {
                foreach ($cronJobs as $cronJob) {
                    $cronJob->registerHandlers();
                }
            }
        );

        yield 'admin_menu' => new Handler(
            ['admin_menus'],
            function (array $menus) {
                foreach ($menus as $menu) {
                    /** @var $menu AdminMenu */
                    $menu->register();
                }
            }
        );

        yield 'rest_api_init' => new Handler(
            ['rest_endpoints'],
            function (array $endpoints) {
                foreach ($endpoints as $endpoint) {
                    /** @var $endpoint RestEndpoint */
                    $endpoint->register();
                }
            }
        );
    }

    /** @inheritDoc */
    public function getFactories(): iterable
    {
        yield 'db' => new GlobalVar('wpdb');
        yield 'post_types' => new Value([]);
        yield 'notices' => new Value([]);
        yield 'notices/manager' => NoticeManager::factory('@plugin/short_id', 'notices');
        yield 'admin_menus' => new Value([]);
        yield 'cron_jobs' => new Value([]);
        yield 'rest_endpoints' => new Value([]);
        yield 'shortcodes' => new Value([]);
        yield 'block_types' => new Value([]);
    }
}
