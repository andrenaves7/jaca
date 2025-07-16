<?php
namespace Jaca\Config;

/**
 * Class Config
 *
 * Provides access to configuration files with optional caching.
 * Loads PHP configuration files located in the config directory.
 *
 * The base path for configuration can be set using the APP_CONFIG_PATH environment variable.
 * If not defined, it defaults to "../config" relative to the current working directory.
 *
 * Example usage:
 * Config::get('database'); // returns entire config file
 * Config::get('database', 'host'); // returns specific key
 *
 * @package Jaca\Config
 */
class Config
{
    /**
     * Static cache of loaded configuration files.
     *
     * @var array<string, array>
     */
    protected static array $cache = [];

    /**
     * Retrieves a configuration file or a specific key within it.
     *
     * @param string $file The configuration file name (without .php extension).
     * @param string|null $key Optional. A specific key within the config file to retrieve.
     * @return mixed The configuration array or the value for the specific key, or null if not found.
     */
    public static function get(string $file, ?string $key = null)
    {
        $base = $_ENV['APP_CONFIG_PATH'] ?? realpath(getcwd()) . '/../config';

        if (!isset(self::$cache[$file])) {
            $path = "{$base}/{$file}.php";
            self::$cache[$file] = file_exists($path) ? require $path : [];
        }

        return $key ? (self::$cache[$file][$key] ?? null) : self::$cache[$file];
    }
}
