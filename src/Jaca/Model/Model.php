<?php
namespace Jaca\Model;

use Jaca\Database\ActionFactory;
use Jaca\Database\Interfaces\IAction;
use Jaca\Database\Interfaces\ISelect;
use Jaca\Model\Attributes\BelongsTo;
use Jaca\Model\Attributes\HasOne;
use Jaca\Model\Interfaces\IModel;
use Jaca\Support\Str;

/**
 * Concrete base model class extending ModelCore with database interaction capabilities.
 * 
 * Implements CRUD operations, query building, and relationship handling.
 */
abstract class Model extends ModelCore implements IModel
{
    /**
     * Database action handler used for queries (insert, update, delete, select).
     *
     * @var IAction
     */
    protected IAction $action;

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
     * Saves the current model instance.
     * 
     * Performs an insert if the primary key is null, otherwise performs an update.
     *
     * @return bool True on success, false otherwise.
     */
    public function save(): bool
    {
        $pk = $this->getPrimary();
        $pkValue = $this->$pk;

        $isNew = ($pkValue === null);

        // Extract column data for insert/update
        $props = $this->extractColumnValues($isNew);

        if ($isNew) {
            $id = $this->action->insert($this->getName(), $props);
            $this->$pk = $id;
            return true;
        } else {
            return $this->action->update($this->getName(), $props, [$pk => $pkValue]);
        }
    }

    /**
     * Deletes the current model from the database.
     * 
     * @return bool True if deletion was successful, false otherwise.
     */
    public function delete(): bool
    {
        $pk = $this->getPrimary();
        // Debug print; consider removing or changing to proper logging
        print_r([$pk => $this->$pk]);

        return $this->action->delete($this->getName(), [$pk => $this->$pk]);
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
        $data = $instance->action->fetchRow($instance->getName(), [$instance->getPrimary() => $id]);
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
            $instance->getName(),
            $where,
            null,    // groupBy (not used here)
            $order,
            $limit,
            $offset
        );

        $objects = [];
        foreach ($results as $data) {
            $objects[] = $instance->mapDataToObject($data);
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
        $rows = $instance->getAction()->fetchAll($instance->getName(), $conditions);

        $results = [];
        foreach ($rows as $row) {
            $results[] = $instance->mapDataToObject($row);
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
        return $instance->getAction()->count($instance->getName(), $where);
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
     * Updates multiple records matching given conditions with provided data.
     *
     * @param array $data Associative array of columns and values to update.
     * @param array $where Conditions to select records for update.
     * @return bool True on successful update, false otherwise.
     */
    public static function updateMany(array $data, array $where): bool
    {
        $instance = new static();
        return $instance->getAction()->update($instance->getName(), $data, $where);
    }

    /**
     * Returns a query builder select instance for this model.
     * 
     * @return ISelect Query builder instance.
     */
    public static function select(): ISelect
    {
        $model = new static();
        return $model->action->select($model)->from($model->getName());
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

        $data = $this->getAction()->fetchRow($this->getName(), [$pk => $this->$pk]);

        if (!$data) {
            return false;
        }

        $this->mapDataToObject($data);
        return true;
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
     * Returns the related owner model instance for a given related model name.
     * 
     * Finds the property with #[BelongsTo] attribute matching the model name,
     * then queries for the related record using the foreign key.
     * 
     * @param string $modelName The related model's class name.
     * @return IModel|null The related owner model instance or null if foreign key is null.
     * @throws \Exception If no property with #[BelongsTo] matching $modelName is found.
     */
    public function getOwner(string $modelName): ?IModel
    {
        $ref = new \ReflectionClass($this);
        foreach ($ref->getProperties() as $prop) {
            foreach ($prop->getAttributes(BelongsTo::class) as $attr) {
                $meta = $attr->newInstance();

                if ($meta->related === $modelName) {
                    $foreignKey = $meta->foreignKey ?? $prop->getName();
                    $foreignKey = Str::camelCase($foreignKey);
                    $relatedClass = $meta->related;
                    $instance = new $relatedClass();

                    // Use ownerKey if set, else fallback to primary key of related class
                    $ownerKey = $meta->ownerKey ?? $instance->getPrimary();

                    $foreignValue = $this->$foreignKey ?? null;

                    if ($foreignValue === null) {
                        return null;
                    }

                    // Build query to fetch related record by ownerKey = foreignValue
                    $data = $instance->getAction()->fetchRow($instance->getName(), [$ownerKey => $foreignValue]);
                    return $data ? $instance->mapDataToObject($data) : null;
                }
            }
        }

        throw new \Exception("No property with #[BelongsTo] for {$modelName} was found.");
    }

    /**
     * Retrieves the related model in a one-to-one (hasOne) relationship.
     *
     * This method looks for a property in the current model annotated with #[HasOne]
     * that references the given model name. It then constructs and executes a query
     * to fetch the related model instance where the foreign key in the related model
     * matches the primary key value of the current model.
     *
     * The foreign key and owner key can be customized via the #[HasOne] attribute.
     * If not provided, defaults are used: the foreign key defaults to the snake_case
     * version of this model's primary key, and the owner key defaults to the primary
     * key of the related model.
     *
     * @param string $modelName The fully qualified class name of the related model.
     * @return IModel|null The related model instance if found, or null otherwise.
     * @throws \Exception If no property with #[HasOne] for the specified model is found.
     */
    public function hasOne(string $modelName): ?IModel
    {
        $ref = new \ReflectionClass($this);
        $relatedClass = null;
        $foreignKey = null;

        $attributes = [];

        // Coleta todos os atributos HasOne da classe
        $attributes = array_merge($attributes, $ref->getAttributes(HasOne::class));

        // Coleta todos os atributos HasOne das propriedades
        foreach ($ref->getProperties() as $prop) {
            $attributes = array_merge($attributes, $prop->getAttributes(HasOne::class));
        }

        foreach ($attributes as $attr) {
            $meta = $attr->newInstance();

            if ($meta->related === $modelName) {
                $relatedClass = $meta->related;
                $instance = new $relatedClass();

                $foreignKey = $meta->foreignKey ?? Str::snakeCase($this->getPrimary());
                $ownerKey = $meta->ownerKey ?? $this->getPrimary();

                $ownerValue = $this->{$ownerKey} ?? null;

                if ($ownerValue === null) {
                    return null;
                }

                $data = $instance->getAction()->fetchRow($instance->getName(), [$foreignKey => $ownerValue]);
                return $data ? $instance->mapDataToObject($data) : null;
            }
        }

        throw new \Exception("No property or class with #[HasOne] for {$modelName} was found.");
    }
}
