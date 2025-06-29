<?php
namespace Jaca\Model;

use Jaca\Model\Attributes\Column;
use Jaca\Model\Attributes\Hidden;
use Jaca\Model\Attributes\PrimaryKey;
use Jaca\Model\Attributes\Table;
use Jaca\Model\Validation\Interfaces\IValidator;

abstract class ModelCore implements \JsonSerializable
{
    private static array $reflectionCache = [];

    protected string $name;
    protected string $primary = 'id';
    private array $errors = [];

    public function __construct()
    {
        $reflection = $this->getReflection();

        $this->fillAttributes($reflection);
        $this->setName($reflection);
        $this->setPrimary($reflection);
    }

    protected function getReflection(): \ReflectionClass
    {
        $class = static::class;
        if (!isset(self::$reflectionCache[$class])) {
            self::$reflectionCache[$class] = new \ReflectionClass($class);
        }
        return self::$reflectionCache[$class];
    }

    protected function fillAttributes(\ReflectionClass $reflection): void
    {
        foreach ($this->getPublicProperties(false) as $property) {
            if (!$property->isInitialized($this)) {
                $property->setValue($this, null);
            }
        }
    }

    protected function setName(\ReflectionClass $reflection): void
    {
        $attributes = $reflection->getAttributes(Table::class);

        if (!empty($attributes)) {
            $tableAttr = $attributes[0]->newInstance();
            $this->name = $tableAttr->name;
        } else {
            $class = $reflection->getShortName();
            $this->name = $this->inflectTableName($class);
        }
    }

    protected function setPrimary(\ReflectionClass $reflection): void
    {
        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes(PrimaryKey::class);
            if (!empty($attrs)) {
                $this->primary = $property->getName();
                break;
            }
        }
    }

    protected function inflectTableName(string $className): string
    {
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        return $snake . 's';
    }

    protected function extractColumnValues(bool $isNew): array
    {
        $reflection = $this->getReflection();
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            $columnAttr = $property->getAttributes(Column::class)[0] ?? null;
            if (!$columnAttr) continue;

            $column = $columnAttr->newInstance();
            $columnName = $column->name ?? $property->getName();
            $value = $this->{$property->getName()} ?? null;

            if ($isNew) {
                if (($value === null || $value === '') && $column->default !== null) {
                    if ($column->default === 'now()' && strtolower($column->type) === 'datetime') {
                        $value = (new \DateTime())->format('Y-m-d H:i:s');
                    } else {
                        $value = $column->default;
                    }
                }
            } else {
                if ($value === null) {
                    continue;
                }
            }

            $data[$columnName] = $value;
        }

        return $data;
    }

    protected function mapDataToObject(array $data): static
    {
        $reflection = $this->getReflection();

        foreach ($reflection->getProperties() as $property) {
            // Tenta obter atributo Column
            $columnAttr = $property->getAttributes(Column::class)[0] ?? null;

            // Se não tiver Column, tenta ver se é a primary key (com PrimaryKey)
            if (!$columnAttr) {
                $primaryKeyAttr = $property->getAttributes(PrimaryKey::class);
                if (!empty($primaryKeyAttr)) {
                    // Criar uma instância "falsa" para obter nome da propriedade como nome da coluna
                    $columnName = $property->getName();
                } else {
                    continue; // pula propriedade que não tem nem Column nem PrimaryKey
                }
            } else {
                $columnName = $columnAttr->newInstance()->name ?? $property->getName();
            }

            if (array_key_exists($columnName, $data)) {
                $value = $data[$columnName];

                $type = $property->getType();
                if ($type instanceof \ReflectionNamedType) {
                    $typeName = $type->getName();

                    if ($value !== null) {
                        switch ($typeName) {
                            case 'int':
                                $value = (int) $value;
                                break;
                            case 'float':
                                $value = (float) $value;
                                break;
                            case 'bool':
                                $value = (bool) $value;
                                break;
                            case 'string':
                                $value = (string) $value;
                                break;
                            case '\DateTime':
                            case 'DateTime':
                                if (is_string($value)) {
                                    try {
                                        $value = new \DateTime($value);
                                    } catch (\Exception $e) {
                                        $value = null;
                                    }
                                }
                                break;
                        }
                    }
                }

                $this->{$property->getName()} = $value;
            }
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrimary(): string
    {
        return $this->primary;
    }

    public function getColumns(): array
    {
        $columns = [];

        foreach ($this->getPublicProperties() as $property) {
            $attr = $property->getAttributes(Column::class)[0] ?? null;
            if ($attr) {
                $columns[] = $attr->newInstance()->name ?? $property->getName();
            } else {
                $columns[] = $property->getName();
            }
        }

        return $columns;
    }

    public function toArray(): array
    {
        $array = [];

        foreach ($this->getPublicProperties() as $property) {
            $name = $property->getName();
            $value = $this->$name;
            $array[$name] = $this->formatValue($value);
        }

        return $array;
    }

    protected function formatValue(mixed $value): mixed
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_object($value)) {
            return method_exists($value, '__toString') ? (string)$value : get_class($value);
        }

        return $value;
    }

    public function isValid(): bool
    {
        $this->errors = [];

        foreach ($this->getPublicProperties(false) as $property) {
            $value = $this->{$property->getName()};
            $attributes = $property->getAttributes();

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                if ($instance instanceof IValidator) {
                    $error = $instance->validate($property->getName(), $value, $this);
                    if ($error !== null) {
                        $this->errors[$property->getName()][] = $error;
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    private function getPublicProperties(bool $excludeHidden = true): array
    {
        $reflection = $this->getReflection();
        $properties = [];

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($excludeHidden && !empty($property->getAttributes(Hidden::class))) {
                continue;
            }
            $properties[] = $property;
        }

        return $properties;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
