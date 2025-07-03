<?php
namespace Jaca\Model;

use Jaca\Support\Str;

class ModelRelationHelper
{
    public static function defaultForeignKey(string $relatedClass): string
    {
        $className = (new \ReflectionClass($relatedClass))->getShortName();
        return Str::snakeCase($className) . '_id';
    }
}