<?php
namespace Jaca\Http;

use Jaca\Config\Constants;

class Response
{
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

    public static function baseUrl(string $path = ''): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $base = str_replace(
            Constants::URI_SEPARATOR . 'index.php', 
            '', 
            $scriptName);

        return rtrim($base, Constants::URI_SEPARATOR) . 
            Constants::URI_SEPARATOR . 
            ltrim($path, Constants::URI_SEPARATOR);
    }
}