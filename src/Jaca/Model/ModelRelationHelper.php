<?php
namespace Jaca\Model;

use Jaca\Support\Str;

/**
 * Helper class to provide utility functions for model relationships.
 */
class ModelRelationHelper
{
    /**
     * Generates a default foreign key name based on the related class name.
     *
     * Converts the short class name to snake_case and appends '_id'.
     *
     * For example, for a related class 'UserProfile', it returns 'user_profile_id'.
     *
     * @param string $relatedClass Fully qualified class name of the related model.
     * @return string Default foreign key name.
     */
    public static function defaultForeignKey(string $relatedClass): string
    {
        $className = (new \ReflectionClass($relatedClass))->getShortName();
        return Str::snakeCase($className) . '_id';
    }
}
