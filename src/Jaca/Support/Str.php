<?php
namespace Jaca\Support;

/**
 * Class Str
 *
 * Provides utility methods for string case transformations,
 * commonly used in frameworks following naming conventions
 * like snake_case, camelCase, StudlyCase (PascalCase), and title casing.
 */
class Str
{
    /**
     * Converts a camelCase or StudlyCase string to snake_case.
     *
     * Example:
     * Str::snakeCase('userName') => 'user_name'
     *
     * @param string $input The input string to convert.
     * @return string The snake_case version of the string.
     */
    public static function snakeCase(string $input): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
    }

    /**
     * Converts a string to camelCase.
     *
     * Accepts strings in snake_case or kebab-case and returns camelCase.
     *
     * Examples:
     * Str::camelCase('user_name') => 'userName'
     * Str::camelCase('user-name') => 'userName'
     *
     * @param string $input The string to convert.
     * @return string The camelCase version of the string.
     */
    public static function camelCase(string $input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $input))));
    }

    /**
     * Converts a string to StudlyCase (also known as PascalCase).
     *
     * Transforms inputs like "user_profile", "user-profile", or "userProfile"
     * into "UserProfile", commonly used for class names.
     *
     * Examples:
     * Str::studly('user_profile') => 'UserProfile'
     * Str::studly('user-profile') => 'UserProfile'
     * Str::studly('userProfile')  => 'UserProfile'
     *
     * @param string $value The string to convert.
     * @return string The StudlyCase version of the input string.
     */
    public static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    /**
     * Converts a snake_case, kebab-case, camelCase or StudlyCase string into a human-readable title case string.
     *
     * Useful for generating labels or headings from variable names.
     *
     * Examples:
     * Str::title('user_profile')      => 'User Profile'
     * Str::title('user-profile')      => 'User Profile'
     * Str::title('userProfile')       => 'User Profile'
     * Str::title('UserProfile')       => 'User Profile'
     *
     * @param string $value The string to convert.
     * @return string The human-readable title case version of the string.
     */
    public static function title(string $value): string
    {
        if (strpos($value, '_') !== false || strpos($value, '-') !== false) {
            // Snake case or kebab case
            $value = str_replace(['_', '-'], ' ', $value);
        } else {
            // CamelCase or StudlyCase
            $value = preg_replace('/(?<!^)([A-Z])/', ' $1', $value);
        }

        return ucwords($value);
    }
}
