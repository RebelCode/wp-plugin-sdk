<?php

namespace RebelCode\WpSdk\Di;

use Dhii\Services\Factory;

/** A factory for services that read a local JSON file and return the parsed result. */
class JsonFileService extends Factory
{
    /**
     * Constructor.
     *
     * @param string $fileService The name of the service that provides the file path.
     * @param string|null $defaultService The name of the service that provides the default value.
     */
    public function __construct(string $fileService, ?string $defaultService = null)
    {
        $deps = array_filter([$fileService, $defaultService]);

        parent::__construct($deps, function ($file, $default = null) {
            if (!is_readable($file)) {
                return $default;
            }

            $json = @file_get_contents($file);
            if (!is_string($json)) {
                return $default;
            }

            $data = @json_decode($json);

            return $data === null ? $default : $data;
        });
    }
}
