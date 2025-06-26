<?php
namespace Jaca\Config;

class Config
{
    protected static array $cache = [];

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
