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
     * Recupera a metadata de uma relação (atributo) de um modelo dado.
     *
     * Esse método busca no modelo informado uma propriedade que possua o atributo
     * de relacionamento especificado (ex: HasOne, BelongsTo, etc) e opcionalmente filtra
     * pelo nome da propriedade ou pela classe relacionada.
     *
     * @param object $model Instância do modelo onde buscar a relação.
     * @param string $relationAttributeClass Nome completo da classe do atributo de relação (ex: HasOne::class).
     * @param string|null $propertyOrRelatedClass Nome da propriedade do modelo ou nome da classe relacionada
     *                                            para filtrar o atributo desejado. Se nulo, retorna o primeiro encontrado.
     * 
     * @return object|null Instância da metadata do atributo de relacionamento encontrado, ou null se não achar.
     */
    public static function getRelationMeta(object $model, string $relationAttributeClass, ?string $propertyOrRelatedClass = null): ?object
    {
        $ref = new \ReflectionClass($model);

        foreach ($ref->getProperties() as $property) {
            foreach ($property->getAttributes($relationAttributeClass) as $attr) {
                $instance = $attr->newInstance();

                if ($propertyOrRelatedClass !== null) {
                    if ($propertyOrRelatedClass === $property->getName()) {
                        return $instance;
                    }

                    if (property_exists($instance, 'related') && $instance->related === $propertyOrRelatedClass) {
                        return $instance;
                    }
                } else {
                    return $instance;
                }
            }
        }

        return null;
    }

    /**
     * Retorna o nome do campo que pode ser usado como label em selects para o model relacionado.
     * Ele ignora a primary key e retorna a primeira propriedade pública disponível.
     * 
     * @param string|object $modelClass Nome da classe ou instância do model
     * @return string|null Nome do campo para label, ou null se não achar nenhum
     */
    public static function getLabelField(string|object $modelClass): ?string
    {
        $ref = new \ReflectionClass($modelClass);

        // Primeiro, tenta encontrar uma propriedade marcada com #[IsLabel]
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            if (count($prop->getAttributes(IsLabel::class)) > 0) {
                return $prop->getName();
            }
        }

        // Se não achar, pega o primeiro atributo público que não é PrimaryKey
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            if (count($prop->getAttributes(PrimaryKey::class)) === 0) {
                return $prop->getName();
            }
        }

        return null;
    }
}
