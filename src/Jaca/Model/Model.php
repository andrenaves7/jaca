<?php
namespace Jaca\Model;

use Jaca\Database\ActionFactory;
use Jaca\Database\Interfaces\IAction;
use Jaca\Database\Interfaces\ISelect;
use Jaca\Http\HttpRequest;
use Jaca\Model\Attributes\BelongsTo;
use Jaca\Model\Attributes\Column;
use Jaca\Model\Attributes\Enum;
use Jaca\Model\Attributes\HasAndBelongsToMany;
use Jaca\Model\Attributes\HasMany;
use Jaca\Model\Attributes\HasOne;
use Jaca\Model\Attributes\ReadOnlyAttr;
use Jaca\Model\Attributes\Types\DataType;
use Jaca\Model\Interfaces\IModel;
use Jaca\Support\Collection;
use Jaca\Support\FileUploader;
use Jaca\Support\Str;

/**
 * Concrete base model class extending ModelCore with database interaction capabilities.
 * 
 * Implements CRUD operations, query building, and relationship handling.
 */
abstract class Model extends ModelCore implements IModel
{

    /**
     * Enables model relationship handling using attributes (e.g., #[HasOne], #[BelongsTo]).
     */
    use InternalDefinesRelations;

    /**
     * Database action handler used for queries (insert, update, delete, select).
     *
     * @var IAction
     */
    protected IAction $action;

    /**
     * Holds temporarily uploaded files before they are persisted.
     * The key is the model field name, and the value is the uploaded file array (from $_FILES).
     *
     * @var array<string, array<string, mixed>>
     */
    private array $pendingFiles  = [];

    /**
     * Model constructor.
     * Initializes parent and creates the IAction instance via factory.
     */
    public function __construct()
    {
        parent::__construct();
        $this->action = ActionFactory::create();
    }

    /**
     * Stores an uploaded file in the pending files list.
     * This does not save the file to permanent storage yet — that happens during save().
     *
     * @param string $field The name of the model field associated with the file.
     * @param array<string, mixed> $file The uploaded file array from $_FILES.
     *
     * @return void
     */
    public function setPendingFile(string $field, array $file): void
    {
        $this->pendingFiles [$field] = $file;
    }

    /**
     * Creates a new instance of the model and fills it with the given data.
     *
     * @param array $data Associative array of property values to fill the model.
     * @return static Returns the newly created and filled model instance.
     */
    public static function fromArray(array $data): static
    {
        $instance = new static();
        $instance->fill($data);
        return $instance;
    }

    /**
     * Creates a new model instance from an HttpRequest object.
     * Maps request POST and FILES data to the model properties
     * based on the Column attribute metadata.
     *
     * For properties with DataType::FILE, the uploaded file is stored
     * in the pending files list for later processing during save().
     *
     * @param HttpRequest $request The incoming HTTP request.
     *
     * @return static The hydrated model instance.
     */
    public static function fromRequest(HttpRequest $request): static
    {
        $model = new static();
        $reflection = new \ReflectionClass($model);
        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            $properties[strtolower($property->getName())] = $property;
        }

        // Merge GET e POST
        $data = array_merge(
            $request->get() ?? [],
            $request->post() ?? []
        );

        foreach ($data as $key => $value) {
            $propName = Str::camelCase($key);
            if (isset($properties[strtolower($propName)])) {
                $model->$propName = $value;
            }
        }
        
        // Tratamento dos arquivos
        foreach ($request->file() ?? [] as $key => $file) {
            $propName = Str::camelCase($key);
            if (isset($properties[strtolower($propName)])) {
                if ($file && $file['error'] === UPLOAD_ERR_OK) {
                    $model->setPendingFile($propName, $file);
                    $model->$propName = $file['name'];
                }
            }
        }
        
        return $model;
    }

    /**
     * Fills the model's properties from an associative array of data.
     *
     * This method uses reflection to iterate over the model's properties and assigns values
     * from the provided data array only if the property:
     *  - Has the #[Column] attribute,
     *  - Is not marked with #[Hidden],
     *  - Is not marked with #[ReadOnlyAttr],
     *  - Is not marked with #[PrimaryKey].
     *
     * @param array $data Associative array mapping property names to values.
     * @return void
     */
    public function fill(array $data): void
    {
        $reflection = $this->getReflection();

        // Filtra o array, removendo as chaves das propriedades ReadOnly
        foreach ($reflection->getProperties() as $property) {
            if (!empty($property->getAttributes(ReadOnlyAttr::class))) {
                $columnAttr = $property->getAttributes(Column::class)[0] ?? null;
                $columnName = $columnAttr?->newInstance()->tableName ?? Str::snakeCase($property->getName());
                if (isset($data[$columnName])) {
                    unset($data[$columnName]);
                }
            }
        }

        $this->mapDataToObject($data);
    }

    /**
     * Saves the current model instance.
     * 
     * Performs an insert if the primary key is null, otherwise performs an update.
     *
     * @return bool True on success, false otherwise.
     */
    public function save(): bool
    {
        $saved = false;
        if ($this->isValid()) {
            $pk = $this->getPrimary();
            $pkValue = $this->$pk;

            $isNew = ($pkValue === null || $pkValue === 0);

            // Extract column data for insert/update
            $props = $this->extractColumnValues($isNew);

            if ($isNew) {
                $id = $this->action->insert($this->getTableName(), $props);
                $this->$pk = $id;
                $saved = true;
            } else {
                $saved = $this->action->update($this->getTableName(), $props, [$pk => $pkValue]);
            }

            if ($saved && !empty($this->pendingFiles)) {
                foreach ($this->pendingFiles as $field => $file) {
                    if ($file['error'] === UPLOAD_ERR_OK) {
                        $fileName = FileUploader::store($file, $field);
                        echo $fileName . '<br />';
                        $this->$field = $fileName;
                        $props = $this->extractColumnValues(false);
                        $saved = $this->action->update($this->getTableName(), $props, [$pk => $this->$pk]);
                    }
                }
            }
        } else {
            $saved = false;
        }
        
        return $saved;
    }

    /**
     * Deletes the current model from the database.
     * 
     * @return bool True if deletion was successful, false otherwise.
     */
    public function delete(): bool
    {
        $pk = $this->getPrimary();

        return $this->action->delete($this->getTableName(), [$pk => $this->$pk]);
    }

    /**
     * Finds a single record by primary key.
     *
     * @param int|string $id Primary key value.
     * @return static|null Returns the found model instance or null if not found.
     */
    public static function find(int|string $id): ?static
    {
        $instance = new static();
        $data = $instance->action->fetchRow($instance->getTableName(), [$instance->getPrimary() => $id]);
        return $data ? $instance->mapDataToObject($data) : null;
    }

    /**
     * Finds all records matching optional conditions, ordering, and limits.
     *
     * @param array|null $where Optional associative array of conditions.
     * @param array|null $order Optional ordering rules.
     * @param int|null $limit Optional max number of results.
     * @param int|null $offset Optional offset for results.
     * @return static[] Array of model instances.
     */
    public static function findAll(
        array $where = null,
        array $order = null,
        int $limit = null,
        int $offset = null
    ): array {
        $instance = new static();
        $results = $instance->getAction()->fetchAll(
            $instance->getTableName(),
            $where,
            null,    // groupBy (not used here)
            $order,
            $limit,
            $offset
        );

        $objects = [];
        foreach ($results as $data) {
            $obj = new static();
            $objects[] = $obj->mapDataToObject($data);
        }

        return $objects;
    }

    /**
     * Finds records matching given conditions.
     *
     * @param array $conditions Associative array of conditions.
     * @return static[] Array of model instances matching conditions.
     */
    public static function findBy(array $conditions): array
    {
        $instance = new static();
        $rows = $instance->getAction()->fetchAll($instance->getTableName(), $conditions);

        $results = [];
        foreach ($rows as $row) {
            $obj = new static();
            $results[] = $obj->mapDataToObject($row);
        }

        return $results;
    }

    /**
     * Counts records matching optional conditions.
     *
     * @param array|null $where Optional associative array of conditions.
     * @return int Number of records matching conditions.
     */
    public static function count(array $where = null): int
    {
        $instance = new static();
        return $instance->getAction()->count($instance->getTableName(), $where);
    }

    /**
     * Checks if any records exist matching optional conditions.
     *
     * @param array|null $where Optional associative array of conditions.
     * @return bool True if one or more records exist, false otherwise.
     */
    public static function exists(array $where = null): bool
    {
        return static::count($where) > 0;
    }

    /**
     * Magic method to handle dynamic method calls.
     *
     * Supports:
     * - Methods ending with 'Label', which will call getLabel() for the corresponding property.
     * - Dynamic relation getters, by matching method names to related model class names.
     *
     * @param string $name The method name called.
     * @param array $args The arguments passed to the method.
     *
     * @return mixed The result of the dynamic call (label string or related model).
     *
     * @throws \Exception When no matching dynamic method or relation is found.
     */
    public function __call(string $name, array $args)
    {
        // Handle Label methods like statusLabel()
        if (substr($name, -5) === 'Label') {
            $property = substr($name, 0, -5);
            if (!property_exists($this, $property)) {
                throw new \Exception("Property '{$property}' does not exist in " . static::class);
            }
            return $this->getLabel($property);
        }

        // Resolve relations dynamically
        $studlyName = Str::studly($name);
        $relations = [
            BelongsTo::class => 'getOwner',
            HasOne::class => 'hasOne',
            HasMany::class => 'hasMany',
            HasAndBelongsToMany::class => 'hasAndBelongsToMany',
        ];

        foreach ($relations as $attributeClass => $methodName) {
            $attributes = $this->collectRelationAttributes($attributeClass);

            foreach ($attributes as $attr) {
                $meta = $attr->newInstance();
                $related = $meta->related;
                $ref = new \ReflectionClass($related);
                $relatedShortName = $ref->getShortName();

                if (Str::studly($relatedShortName) === $studlyName) {
                    return $this->$methodName($related);
                }
            }
        }

        throw new \Exception("No relationship or dynamic method '{$name}' found in " . static::class);
    }

    /**
     * Retrieves the label associated with an enum-like property.
     *
     * This method uses reflection to inspect the specified property for the
     * #[Enum] attribute. If found, it returns the corresponding label for
     * the current value of that property. If no label is defined, it returns
     * the raw value.
     *
     * @param string $property The name of the property to get the label for.
     * @return string|null The label corresponding to the property value, or null if not found or invalid.
     */
    public function getLabel(string $property): ?string
    {
        try {
            $refClass = new \ReflectionClass($this);
            if (!$refClass->hasProperty($property)) {
                return null;
            }

            $refProp = $refClass->getProperty($property);
            $attrs = $refProp->getAttributes(Enum::class);

            if ($attrs) {
                $enum = $attrs[0]->newInstance();
                $value = $this->$property;
                return $enum->values[$value] ?? $value;
            }

            return $this->$property;
        } catch (\ReflectionException $e) {
            return null;
        }
    }

    /**
     * Creates and returns a select query builder for the model's table.
     * 
     * @return ISelect Query builder instance for fluent querying.
     */
    public static function select(): ISelect
    {
        $model = new static();
        return $model->action->select($model)->from($model->getTableName());
    }

    /**
     * Gets the IAction instance used for database operations.
     *
     * @return IAction
     */
    public function getAction(): IAction
    {
        return $this->action;
    }

    /**
     * Reloads the current model from the database.
     *
     * @return bool True if reload was successful, false if primary key not set or no data found.
     */
    public function reload(): bool
    {
        $pk = $this->getPrimary();
        if (!isset($this->$pk)) {
            return false;
        }

        $data = $this->getAction()->fetchRow($this->getTableName(), [$pk => $this->$pk]);

        if (!$data) {
            return false;
        }

        $this->mapDataToObject($data);
        return true;
    }
    
    /**
     * Updates multiple records matching given conditions with provided data.
     *
     * @param array $data Associative array of columns and values to update.
     * @param array $where Conditions to select records for update.
     * @return bool True on successful update, false otherwise.
     */
    public static function updateMany(array $data, array $where): bool
    {
        $instance = new static();
        return $instance->getAction()->update($instance->getTableName(), $data, $where);
    }

    /**
     * Returns validation errors collected during the last isValid() check.
     *
     * @return array<string, string[]> Array mapping property names to error messages.
     */
    public function getErrors(): array
    {
        return parent::getErrors();
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
        return parent::toArray();
    }

    /**
     * Extracts a column of values or an associative array (e.g., id => name).
     *
     * @param string $valueField The column to use as value.
     * @param string|null $keyField The column to use as key (optional).
     * @param array|null $where Optional conditions for filtering.
     * @return array An array of values or a key-value array.
     */
    public static function pluck(string $valueField, ?string $keyField = null, array $where = null): array
    {
        $model = new static();
        $sql = $model->action->select()
        ->from($model->getTableName(), [
            $keyField, 
            $valueField]);

        if ($where) {
            foreach ($where as $key => $val) {
                $sql->where("{$key} = ?", $val);
            }
        }
        
        $rows = $sql->fetchAll();

        $result = [];

        foreach ($rows as $row) {
            if ($keyField !== null) {
                $result[$row[$keyField]] = $row[$valueField];
            } else {
                $result[] = $row[$valueField];
            }
        }

        return $result;
    }

    /**
     * Deletes a record by its primary key.
     *
     * This method attempts to find a record by the given ID and delete it.
     * If the record does not exist, an exception is thrown.
     *
     * @param int|string $id The primary key value of the record to delete.
     * @return bool True if the record was successfully deleted.
     *
     * @throws \Exception If the record with the given ID is not found.
     */
    public static function destroy(int|string $id): bool
    {
        $instance = static::find($id);
        if (!$instance) {
            throw new \Exception("Record with ID {$id} not found.");
        }
        return $instance->delete();
    }
    
    /**
     * Determine whether the model has any file-type fields.
     *
     * This method uses reflection to inspect the model's properties and checks
     * if any of them are annotated with a Column attribute of type FILE.
     *
     * @return bool True if at least one property is of type FILE, false otherwise.
     */
    public function hasFileField(): bool
    {
        $ref = new \ReflectionClass($this);

        $properties = Collection::make($ref->getProperties());

        return $properties->filter(fn($property) => 
            Collection::make($property->getAttributes(Column::class))
                ->some(fn($attr) => 
                    ($attr->getArguments()['type'] ?? null) === DataType::FILE
                )
        )->count() > 0;
    }
}
