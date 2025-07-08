<?php
namespace Jaca\Support;

class Str
{
    public static function snakeCase(string $input): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
    }

    public static function camelCase(string $input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $input))));
    }

    /**
     * Converts a string to StudlyCase (also known as PascalCase).
     *
     * This method transforms strings like "user_profile", "user-profile", or "userProfile"
     * into "UserProfile", which is commonly used for class names.
     *
     * Example usage:
     * Str::studly('user_profile') // returns 'UserProfile'
     * Str::studly('user-profile') // returns 'UserProfile'
     * Str::studly('userProfile')  // returns 'UserProfile'
     *
     * @param string $value The string to convert.
     * @return string The StudlyCase version of the input.
     */
    public static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }
}