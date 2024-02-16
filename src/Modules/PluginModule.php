<?php

namespace RebelCode\WpSdk\Modules;

use Dhii\Services\Factories\Value;
use Dhii\Services\Factory;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Handler;
use RebelCode\WpSdk\Meta\PluginMeta;
use RebelCode\WpSdk\Module;
use RebelCode\WpSdk\Plugin;
use RebelCode\WpSdk\Wp\AbstractOption;
use RebelCode\WpSdk\Wp\Transient;

use function plugin_dir_url;

/** A module that adds information about the plugin. */
class PluginModule extends Module
{
    /** @var string */
    protected $filePath;

    /** @var string */
    protected $serviceDelim;

    /**
     * Constructor.
     *
     * @param string $filePath The path to the plugin's main file.
     * @param string $serviceDelim The delimiter to use for service IDs.
     */
    public function __construct(string $filePath, string $serviceDelim = '/')
    {
        $this->filePath = $filePath;
        $this->serviceDelim = $serviceDelim;
    }

    /** @inheritDoc */
    public function run(ContainerInterface $c, Plugin $plugin): void
    {
        register_activation_hook($this->filePath, function () use ($c) {
            do_action($c->get('short_id') . $this->serviceDelim . 'early_activation');

            /** @var AbstractOption $marker */
            $marker = $c->get('activation_marker');
            $marker->setValue(true);
        });

        register_deactivation_hook($this->filePath, function () use ($c) {
            do_action($c->get('short_id') . $this->serviceDelim . 'deactivation');
        });
    }

    /** @inheritDoc */
    public function getHooks(): iterable
    {
        yield 'admin_init' => [
            new Handler(
                ['short_id', 'activation_marker'],
                function (string $prefix, AbstractOption $marker) {
                    if (is_admin() && $marker->getValue()) {
                        $marker->delete();
                        do_action($prefix . $this->serviceDelim . 'activation');
                    }
                }
            ),
        ];
    }

    /** @inheritDoc */
    public function getFactories(): iterable
    {
        yield 'file_path' => new Value($this->filePath);
        yield 'dir_path' => new Value(dirname($this->filePath));
        yield 'dir_url' => new Value(rtrim(plugin_dir_url($this->filePath), '/'));
        yield 'json_file' => new Value($this->filePath . '/plugin.json');

        yield 'meta' => new Factory(['json_file'], function (string $jsonFile) {
            if (is_readable($jsonFile)) {
                return PluginMeta::parseFromJsonFile($jsonFile);
            } else {
                return PluginMeta::parseFromPluginHeader($this->filePath);
            }
        });

        yield 'slug' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->slug);
        yield 'short_id' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->shortId);
        yield 'name' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->name);
        yield 'description' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->description);
        yield 'version' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->version);
        yield 'url' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->url);
        yield 'author' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->author);
        yield 'textDomain' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->textDomain);
        yield 'domainPath' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->domainPath);
        yield 'min_php_version' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->minPhpVersion);
        yield 'min_wp_version' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->minWpVersion);
        yield 'extra' => new Factory(['meta'], fn (PluginMeta $meta) => $meta->extra);

        yield 'activation_marker' => new Factory(['short_id'], fn (string $short_id) => new Transient("{$short_id}_activation", Transient::bool(), 60, false));
    }
}
