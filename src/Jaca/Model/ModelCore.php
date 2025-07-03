<?php
namespace Jaca\Model;

use Jaca\Model\Attributes\Column;
use Jaca\Model\Attributes\Hidden;
use Jaca\Model\Attributes\PrimaryKey;
use Jaca\Model\Attributes\Table;
use Jaca\Model\Validation\Interfaces\IValidator;
use Jaca\Support\Str;

/**
 * Base abstract model class providing reflection-based attribute mapping,
 * validation support, and serialization capabilities.
 * 
 * This class uses PHP 8 attributes to define metadata such as table name,
 * columns, primary key, and hidden properties.
 */
abstract class ModelCore implements \JsonSerializable
{
    /**
     * Cache ReflectionClass instances per class to improve performance.
     * @var \ReflectionClass[]
     */
    private static array $reflectionCache = [];

    /**
     * Table name associated with the model.
     * @var string
     */
    protected string $name;

    /**
     * Name of the primary key property.
     * @var string
     */
    protected string $primary = 'id';

    /**
     * Validation errors collected during isValid() checks.
     * @var array<string, string[]>
     */
    private array $errors = [];

    /**
     * ModelCore constructor.
     * Initializes reflection cache and fills default property values.
     */
    public function __construct()
    {
        $reflection = $this->getReflection();

        $this->fillAttributes($reflection);
        $this->setName($reflection);
        $this->setPrimary($reflection);
    }

    /**
     * Retrieves cached ReflectionClass instance for the current class.
     * 
     * @return \ReflectionClass
     */
    protected function getReflection(): \ReflectionClass
    {
        $class = static::class;
        if (!isset(self::$reflectionCache[$class])) {
            self::$reflectionCache[$class] = new \ReflectionClass($class);
        }
        return self::$reflectionCache[$class];
    }

    /**
     * Ensures all public properties are initialized to null if not already set.
     *
     * @param \ReflectionClass $reflection Reflection of the current class.
     */
    protected function fillAttributes(\ReflectionClass $reflection): void
    {
        foreach ($this->getPublicProperties(false) as $property) {
            if (!$property->isInitialized($this)) {
                $property->setValue($this, null);
            }
        }
    }

    /**
     * Sets the model's table name from the #[Table] attribute,
     * or infers the table name by converting the class name to snake_case and pluralizing.
     *
     * @param \ReflectionClass $reflection Reflection of the current class.
     */
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

    /**
     * Finds the primary key property by looking for the #[PrimaryKey] attribute.
     *
     * @param \ReflectionClass $reflection Reflection of the current class.
     */
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

    /**
     * Converts a class name to a pluralized snake_case table name.
     * 
     * Example: "UserRole" => "user_roles"
     * 
     * @param string $className The class name.
     * @return string The inferred table name.
     */
    protected function inflectTableName(string $className): string
    {
        return Str::snakeCase($className) . 's';
    }

    /**
     * Extracts column values from the model's properties into an associative array,
     * applying default values and formatting for new or existing records.
     * 
     * @param bool $isNew Indicates whether this is a new record (insert) or existing (update).
     * @return array Associative array mapping column names to values.
     */
    protected function extractColumnValues(bool $isNew): array
    {
        $reflection = $this->getReflection();
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            $columnAttr = $property->getAttributes(Column::class)[0] ?? null;
            if (!$columnAttr) continue;

            $column = $columnAttr->newInstance();
            $columnName = $column->name ?? strtolower(Str::snakeCase($property->getName()));
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

    /**
     * Maps an associative array of data to the current object's properties.
     *
     * This method uses #[Column] and #[PrimaryKey] attributes to determine the mapping between
     * object properties and array keys. If no column name is defined, the property name is
     * automatically converted to snake_case.
     *
     * Type casting is applied based on the declared type of each property, including support
     * for `int`, `float`, `bool`, `string`, and `DateTime` objects.
     *
     * Properties without relevant attributes or without corresponding keys in the input array are skipped.
     *
     * @param array $data Associative array with column names as keys.
     * @return static Returns the current object instance with mapped data.
     */
    protected function mapDataToObject(array $data): static
    {
        $reflection = $this->getReflection();

        foreach ($reflection->getProperties() as $property) {
            $columnAttr = $property->getAttributes(Column::class)[0] ?? null;
            if (!$columnAttr) {
                $primaryKeyAttr = $property->getAttributes(PrimaryKey::class);
                if (!empty($primaryKeyAttr)) {
                    $columnName = Str::snakeCase($property->getName());
                } else {
                    continue;
                }
            } else {
                $columnName = $columnAttr->newInstance()->name ?? Str::snakeCase($property->getName());
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

    /**
     * Gets the table name associated with this model.
     *
     * @return string The table name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the name of the primary key property.
     *
     * @return string The primary key property name.
     */
    public function getPrimary(): string
    {
        return $this->primary;
    }

    /**
     * Retrieves an array of column names corresponding to the model's public properties.
     *
     * @return string[] List of column names.
     */
    public function getColumns(): array
    {
        $columns = [];

        foreach ($this->getPublicProperties() as $property) {
            $attr = $property->getAttributes(Column::class)[0] ?? null;
            if ($attr) {
                $columns[] = $attr->newInstance()->name ?? Str::snakeCase($property->getName());
            } else {
                $columns[] = Str::snakeCase($property->getName());
            }
        }

        return $columns;
    }

    /**
     * Converts the current object into an associative array.
     *
     * Only public properties are included in the resulting array.
     * Each property's value is passed through `formatValue()` to ensure consistent formatting,
     * such as converting DateTime objects to strings.
     *
     * @return array An associative array representing the object's public properties.
     */
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

    /**
     * Formats a value for array serialization.
     *
     * - DateTime objects are formatted as 'Y-m-d H:i:s' strings.
     * - Objects implementing __toString are converted to strings.
     * - Other objects return their class name.
     * - Scalars and null are returned as-is.
     *
     * @param mixed $value The value to format.
     * @return mixed The formatted value.
     */
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

    /**
     * Validates the current model instance using attached validators.
     *
     * Each property is checked for attributes implementing IValidator.
     * Errors are collected internally and can be retrieved with getErrors().
     *
     * @return bool True if the model passes all validations, false otherwise.
     */
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

    /**
     * Returns validation errors collected during the last isValid() check.
     *
     * @return array<string, string[]> Array mapping property names to error messages.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Magic method used by var_dump() and similar debugging tools.
     * Returns an array representation of the object.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }

    /**
     * Retrieves all public properties, optionally excluding those marked as #[Hidden].
     *
     * @param bool $excludeHidden Whether to exclude properties with the #[Hidden] attribute.
     * @return \ReflectionProperty[] Array of ReflectionProperty objects.
     */
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

    /**
     * Serializes the object for JSON encoding.
     *
     * @return array Associative array representation for JSON serialization.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
