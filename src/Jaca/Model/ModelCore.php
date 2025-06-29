<?php
namespace Jaca\Model;

use Jaca\Model\Attributes\Column;
use Jaca\Model\Attributes\Hidden;
use Jaca\Model\Attributes\PrimaryKey;
use Jaca\Model\Attributes\Table;
use Jaca\Model\Validation\Interfaces\IValidator;

abstract class ModelCore
{
    // Cache estático da reflexão da classe para otimização
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

    // Retorna ReflectionClass da instância, cacheado por classe
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
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isInitialized($this)) {
                $type = $property->getType();
                if ($type && !$type->isBuiltin()) {
                    $property->setValue($this, null); // Classes/Objetos → null
                } elseif ($type && $type instanceof \ReflectionNamedType) {
                    switch ($type->getName()) {
                        case 'int':
                            $property->setValue($this, 0);
                            break;
                        case 'float':
                            $property->setValue($this, 0.0);
                            break;
                        case 'string':
                            $property->setValue($this, '');
                            break;
                        case 'bool':
                            $property->setValue($this, false);
                            break;
                        default:
                            $property->setValue($this, null);
                            break;
                    }
                } else {
                    $property->setValue($this, null);
                }
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

    protected function extractColumnValues(): array
    {
        $reflection = $this->getReflection();
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            $columnAttr = $property->getAttributes(Column::class)[0] ?? null;
            if (!$columnAttr) continue;

            $column = $columnAttr->newInstance()->name ?? $property->getName();
            $value = $this->{$property->getName()};

            $data[$column] = $value;
        }

        return $data;
    }

    protected function mapDataToObject(array $data): static
    {
        $reflection = $this->getReflection();

        foreach ($reflection->getProperties() as $property) {
            $columnAttr = $property->getAttributes(Column::class)[0] ?? null;
            if (!$columnAttr) continue;

            $columnName = $columnAttr->newInstance()->name ?? $property->getName();

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
        $reflection = $this->getReflection();

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!empty($property->getAttributes(Hidden::class))) {
                continue;
            }

            $columnAttr = $property->getAttributes(Column::class);
            if (!empty($columnAttr)) {
                $column = $columnAttr[0]->newInstance();
                $columns[] = $column->name ?? $property->getName();
            } else {
                $columns[] = $property->getName();
            }
        }

        return $columns;
    }

    public function isValid(): bool
    {
        $this->errors = [];
        $reflection = $this->getReflection();

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
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
}
