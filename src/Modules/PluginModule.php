<?php

namespace RebelCode\WpSdk\Modules;

use Dhii\Services\Factories\Value;
use Dhii\Services\Factory;
use Psr\Container\ContainerInterface;
use RebelCode\WpSdk\Handler;
use RebelCode\WpSdk\Meta\PluginMeta;
use RebelCode\WpSdk\Module;
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
    public function run(ContainerInterface $c): void
    {
        register_activation_hook($this->filePath, function () use ($c) {
            do_action($c->get('short_id') . $this->serviceDelim . 'early_activation');

            /** @var AbstractOption $transient */
            $transient = $c->get('transient');
            $transient->setValue(true);
        });

        register_deactivation_hook($this->filePath, function () use ($c) {
            do_action($c->get('short_id') . $this->serviceDelim . 'deactivation');
        });
    }

    /** @inheritDoc */
    public function getHooks(): array
    {
        return [
            'admin_init' => [
                new Handler(
                    ['short_id', 'activation_marker'],
                    function (string $prefix, AbstractOption $transient) {
                        if (is_admin() && $transient->getValue()) {
                            $transient->delete();
                            do_action($prefix . $this->serviceDelim . 'activation');
                        }
                    }
                ),
            ],
        ];
    }

    /** @inheritDoc */
    public function getFactories(): array
    {
        return [
            'file_path' => new Value($this->filePath),
            'dir_path' => new Value(dirname($this->filePath)),
            'dir_url' => new Value(plugin_dir_url($this->filePath)),
            'json_file' => new Value($this->filePath . '/plugin.json'),

            'meta' => new Factory(['json_file'], function (string $jsonFile) {
                return is_readable($jsonFile)
                    ? PluginMeta::parseFromJsonFile($jsonFile)
                    : PluginMeta::parseFromPluginHeader($this->filePath);
            }),
            'slug' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->slug;
            }),
            'short_id' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->shortId;
            }),
            'name' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->name;
            }),
            'description' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->description;
            }),
            'version' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->version;
            }),
            'url' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->url;
            }),
            'author' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->author;
            }),
            'textDomain' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->textDomain;
            }),
            'domainPath' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->domainPath;
            }),
            'min_php_version' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->minPhpVersion;
            }),
            'min_wp_version' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->minWpVersion;
            }),
            'extra' => new Factory(['meta'], function (PluginMeta $meta) {
                return $meta->extra;
            }),

            'activation_marker' => new Factory(['short_id'], function (string $short_id) {
                return new Transient("{$short_id}_activation", Transient::bool(), 60, false);
            }),
        ];
    }
}
