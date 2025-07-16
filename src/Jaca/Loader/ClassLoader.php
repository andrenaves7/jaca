<?php
namespace Jaca\Loader;

/**
 * Class ClassLoader
 *
 * Utility class responsible for verifying the existence of PHP class files
 * based on fully qualified class names and base directory paths.
 *
 * This class helps map namespaces to filesystem paths and checks if the
 * corresponding PHP file exists.
 *
 * Usage example:
 * ```php
 * if (ClassLoader::classExist('/path/to/src', 'App\\Helpers\\MyHelper')) {
 *     // Class file exists, proceed to load or instantiate
 * }
 * ```
 *
 * @package Jaca\Loader
 */
class ClassLoader
{
    /**
     * Namespace separator character.
     */
    private const NAMESPACE_SEPARATOR = '\\';

    /**
     * Directory separator character.
     */
    private const DIRECTORY_SEPARATOR = DIRECTORY_SEPARATOR;

    /**
     * Checks if the PHP file for a given fully qualified class name exists
     * within a base directory path.
     *
     * @param string $basePath The base directory path where classes are stored.
     * @param string $className The fully qualified class name (including namespace).
     * @return bool True if the class file exists; false otherwise.
     */
    public static function classExist(string $basePath, string $className): bool
    {
        $relativePath = str_replace(self::NAMESPACE_SEPARATOR, self::DIRECTORY_SEPARATOR, $className) . '.php';
        $fullPath = rtrim($basePath, self::DIRECTORY_SEPARATOR) . self::DIRECTORY_SEPARATOR . $relativePath;

        return file_exists($fullPath);
    }
}
