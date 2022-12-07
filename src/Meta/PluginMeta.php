<?php

namespace RebelCode\WpSdk\Meta;

class PluginMeta
{
    /** @var string The plugin's slug. */
    public $slug;

    /** @var string The unique short ID of the plugin, often used to prefix classes, functions, and hooks. */
    public $shortId;

    /** @var string The human-friendly name of the plugin. */
    public $name;

    /** @var string A short description of the plugin. */
    public $description;

    /** @var string The plugin's version. */
    public $version;

    /** @var string The URL to the plugin's website. */
    public $url;

    /** @var AuthorMeta Information about the plugin author. */
    public $author;

    /** @var string The text translation domain. */
    public $textDomain;

    /** @var string The path to the translation files. */
    public $domainPath;

    /** @var string The minimum required PHP version. */
    public $minPhpVersion;

    /** @var string The minimum required WordPress version. */
    public $minWpVersion;

    /** @var array<string, string> */
    public $extra;

    /** Parses plugin information from a JSON file */
    public static function parseFromJsonFile(string $filePath): ?self
    {
        if (is_readable($filePath)) {
            $json = file_get_contents($filePath);
            $data = @json_decode($json, true);

            return ($data === null) ? null : static::fromArray($data);
        } else {
            return null;
        }
    }

    /** Creates an instance from the data parsed from the plugin's header. */
    public static function parseFromPluginHeader(string $filePath): ?self
    {
        if (!function_exists('get_plugin_data')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $data = get_plugin_data($filePath, false, false);

        $pluginMeta = new self();
        $pluginMeta->slug = basename(dirname($filePath));
        $pluginMeta->shortId = static::generateShortId($data['Name'] ?? '');
        $pluginMeta->name = $data['Name'] ?? '';
        $pluginMeta->description = $data['Description'] ?? '';
        $pluginMeta->version = $data['Version'] ?? '';
        $pluginMeta->url = $data['PluginURI'] ?? '';
        $pluginMeta->author = new AuthorMeta($data['AuthorName'] ?? '', $data['AuthorURI'] ?? '');
        $pluginMeta->textDomain = $data['TextDomain'] ?? '';
        $pluginMeta->domainPath = $data['DomainPath'] ?? '';
        $pluginMeta->minPhpVersion = $data['RequiresPHP'] ?? '';
        $pluginMeta->minWpVersion = $data['RequiresWP'] ?? '';
        $pluginMeta->extra = [];

        return $pluginMeta;
    }

    /** Creates an instance from an array of data. */
    public static function fromArray(array $data): self
    {
        $pluginMeta = new self();
        $pluginMeta->slug = $data['slug'] ?? '';
        $pluginMeta->shortId = $data['shortId'] ?? '';
        $pluginMeta->name = $data['name'] ?? '';
        $pluginMeta->description = $data['description'] ?? '';
        $pluginMeta->version = $data['version'] ?? '';
        $pluginMeta->url = $data['url'] ?? '';
        $pluginMeta->textDomain = $data['textDomain'] ?? '';
        $pluginMeta->domainPath = $data['domainPath'] ?? '';
        $pluginMeta->author = new AuthorMeta($data['author']['name'] ?? '', $data['author']['url'] ?? '');
        $pluginMeta->minPhpVersion = $data['minPhpVersion'] ?? '';
        $pluginMeta->minWpVersion = $data['minWpVersion'] ?? '';
        $pluginMeta->extra = $data['extra'] ?? [];

        return $pluginMeta;
    }

    /** Generates a short ID for a plugin based on its name. */
    public static function generateShortId(string $name): string
    {
        $words = array_filter(explode(' ', $name));
        $initials = array_map(function ($word) {
            return $word[0];
        }, $words);

        return strtolower(implode('', $initials));
    }
}
