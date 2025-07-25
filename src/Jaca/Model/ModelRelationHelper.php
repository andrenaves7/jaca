<?php
namespace Jaca\Model;

use Jaca\Model\Attributes\IsLabel;
use Jaca\Model\Attributes\PrimaryKey;
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

    /**
     * Retrieves a specific relation attribute instance from a model's property based on metadata.
     *
     * @param object      $model                 The model instance to inspect.
     * @param string      $id                    The identifier used to match the property (compared in snake_case).
     * @param string      $relationAttributeClass The attribute class to look for.
     * @param string|null $propertyOrRelatedClass Optional: specific property name or related class name to match.
     *
     * @return object|null The relation attribute instance if found, or null.
     */
    public static function getRelationMeta(
        object $model,
        string $id,
        string $relationAttributeClass,
        ?string $propertyOrRelatedClass = null
    ): ?object {
        $idSnake = Str::snakeCase($id);
        $reflection = new \ReflectionClass($model);

        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            $propertySnake = Str::snakeCase($propertyName);

            if ($propertySnake !== $idSnake) {
                continue;
            }

            foreach ($property->getAttributes($relationAttributeClass) as $attribute) {
                $instance = $attribute->newInstance();

                $matchesPropertyName = $propertyOrRelatedClass === $propertyName;
                $matchesRelatedClass = (
                    $propertyOrRelatedClass !== null &&
                    property_exists($instance, 'related') &&
                    $instance->related === $propertyOrRelatedClass
                );

                if ($propertyOrRelatedClass === null || $matchesPropertyName || $matchesRelatedClass) {
                    return $instance;
                }
            }
        }

        return null;
    }

    /**
     * Returns the name of the field that can be used as a label in selects for the related model.
     *
     * Priority:
     * 1. The first public property marked with #[IsLabel]
     * 2. The first public property that is not marked with #[PrimaryKey]
     *
     * @param string|object $modelClass Class name or model instance.
     * @return string|null The field name to use as label, or null if none is found.
     */
    public static function getLabelField(string|object $modelClass): ?string
    {
        $ref = new \ReflectionClass($modelClass);
        $publicProps = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);

        // First, look for a property with #[IsLabel]
        foreach ($publicProps as $prop) {
            if ($prop->getAttributes(IsLabel::class)) {
                return $prop->getName();
            }
        }

        // Then, return the first public property that is not a primary key
        foreach ($publicProps as $prop) {
            if (empty($prop->getAttributes(PrimaryKey::class))) {
                return $prop->getName();
            }
        }

        return null;
    }
}
