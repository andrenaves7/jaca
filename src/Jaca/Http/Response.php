<?php
namespace Jaca\Http;

use Jaca\Config\Constants;

/**
 * Class Response
 *
 * Handles HTTP responses, such as redirection and base URL generation.
 *
 * @package Jaca\Http
 */
class Response
{
    /**
     * Redirects the browser to a given URL.
     *
     * If the URL is provided as an array, it will be joined using the configured URI separator.
     * Automatically handles output of HTTP headers if they haven't been sent yet.
     *
     * @param string|array $url The target URL or an array of URI segments.
     * @return void
     */
    public static function redirect(string|array $url): void
    {
        if (is_array($url)) {
            $url = implode(Constants::URI_SEPARATOR,
                array_map(fn($v) => trim($v, Constants::URI_SEPARATOR), $url));
        }

        $location = self::baseUrl($url);

        if (!headers_sent()) {
            header('Location: ' . $location);
            exit;
        }
    }

    /**
     * Builds the full base URL for a given path.
     *
     * Removes the "index.php" part from the script name and joins it with the given path.
     *
     * @param string $path The relative path to append to the base URL.
     * @return string The full base URL.
     */
    public static function baseUrl(string $path = ''): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $base = str_replace(
            Constants::URI_SEPARATOR . 'index.php',
            '',
            $scriptName
        );

        return rtrim($base, Constants::URI_SEPARATOR) .
            Constants::URI_SEPARATOR .
            ltrim($path, Constants::URI_SEPARATOR);
    }
}
