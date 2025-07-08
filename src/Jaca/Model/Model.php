<?php
namespace Jaca\Model;

use Jaca\Database\ActionFactory;
use Jaca\Database\Interfaces\IAction;
use Jaca\Database\Interfaces\ISelect;
use Jaca\Model\Attributes\BelongsTo;
use Jaca\Model\Attributes\HasAndBelongsToMany;
use Jaca\Model\Attributes\HasMany;
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
            $id = $this->action->insert($this->getTableName(), $props);
            $this->$pk = $id;
            return true;
        } else {
            return $this->action->update($this->getTableName(), $props, [$pk => $pkValue]);
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
        $rows = $instance->getAction()->fetchAll($instance->getTableName(), $conditions);

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
                    $relatedClass = $meta->related;
                    $instance = new $relatedClass();

                    $foreignKey = ($meta->foreignKey !== null && $meta->foreignKey !== '') 
                        ? $meta->foreignKey : Str::snakeCase($this->getPrimary());

                    $ownerKey = ($meta->ownerKey !== null && $meta->ownerKey !== '')
                        ? $meta->ownerKey : $this->getPrimary();

                    $foreignValue = $this->{Str::camelCase($foreignKey)} ?? null;

                    if ($foreignValue === null) {
                        return null;
                    }

                    // Build query to fetch related record by ownerKey = foreignValue
                    $data = $instance->getAction()->fetchRow($instance->getTableName(), [$ownerKey => $foreignValue]);
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
        $relatedClass = null;
        $foreignKey = null;

        $attributes = $this->collectRelationAttributes(HasOne::class);

        foreach ($attributes as $attr) {
            $meta = $attr->newInstance();

            if ($meta->related === $modelName) {
                $relatedClass = $meta->related;
                $instance = new $relatedClass();

                $foreignKey = ($meta->foreignKey !== null && $meta->foreignKey !== '') 
                    ? $meta->foreignKey : ModelRelationHelper::defaultForeignKey($relatedClass);

                $localKey = ($meta->localKey !== null && $meta->localKey !== '')
                    ? $meta->localKey : Str::snakeCase($instance->getPrimary());

                $foreignKeyValue = $this->{Str::camelCase($foreignKey)} ?? null;

                if ($foreignKeyValue === null) {
                    return null;
                }

                $data = $instance->getAction()->fetchRow($instance->getTableName(), [$localKey => $foreignKeyValue]);
                return $data ? $instance->mapDataToObject($data) : null;
            }
        }

        throw new \Exception("No property or class with #[HasOne] for {$modelName} was found.");
    }

    /**
     * Retrieves a collection of related models in a one-to-many (hasMany) relationship.
     *
     * This method searches for properties or class-level attributes marked with #[HasMany]
     * that match the given related model name. It then queries all related records where
     * the foreign key in the related model matches this model's primary key value.
     *
     * @param string $modelName The fully qualified class name of the related model.
     * @return IModel[] Array of related model instances.
     * @throws \Exception If no #[HasMany] attribute is found for the given model.
     */
    public function hasMany(string $modelName): array
    {
        $attributes = $this->collectRelationAttributes(HasMany::class);

        foreach ($attributes as $attr) {
            $meta = $attr->newInstance();

            if ($meta->related === $modelName) {
                $relatedClass = $meta->related;
                $instance = new $relatedClass();

                $foreignKey = ($meta->foreignKey !== null && $meta->foreignKey !== '') 
                    ? $meta->foreignKey : ModelRelationHelper::defaultForeignKey(static::class);
                    
                $localKey = ($meta->localKey !== null && $meta->localKey !== '')
                    ? $meta->localKey : Str::snakeCase($instance->getPrimary());

                $foreignKeyValue = $this->{Str::camelCase($localKey)} ?? null;

                if ($foreignKeyValue === null) {
                    return [];
                }

                $rows = $instance->getAction()->fetchAll($instance->getTableName(), [
                    $foreignKey => $foreignKeyValue
                ]);

                $results = [];
                foreach ($rows as $row) {
                    $results[] = $instance->mapDataToObject($row);
                }

                return $results;
            }
        }

        throw new \Exception("No property or class with #[HasMany] for {$modelName} was found.");
    }

    /**
     * Retrieves related records in a "has and belongs to many" (many-to-many) relationship.
     *
     * This method fetches related model instances associated with this model
     * through a pivot table. It uses the #[HasAndBelongsToMany] attribute metadata
     * to determine the pivot table and foreign keys.
     *
     * @param string $modelName Fully qualified class name of the related model.
     *
     * @return array An array of instances of the related model.
     *
     * @throws \Exception If no #[HasAndBelongsToMany] attribute matching $modelName is found.
     */
    public function hasAndBelongsToMany(string $modelName): array
    {
        $attributes = $this->collectRelationAttributes(HasAndBelongsToMany::class);

        $refA = new \ReflectionClass($this);
        $refB = new \ReflectionClass(new $modelName);

        foreach ($attributes as $attr) {
            $meta = $attr->newInstance();

            if ($meta->related === $modelName) {
                $relatedClass = $meta->related;
                $instance = new $relatedClass();

                $pivot = ($meta->pivot !== null && $meta->pivot !== '')
                    ? $meta->pivot : Str::snakeCase($refA->getShortName() . $refB->getShortName());

                $foreignPivotKey = ($meta->foreignPivotKey !== null && $meta->foreignPivotKey !== '')
                    ? $meta->foreignPivotKey : Str::snakeCase($refA->getShortName() . '_' . $this->getPrimary());

                $relatedPivotKey = ($meta->relatedPivotKey !== null && $meta->relatedPivotKey !== '')
                    ? $meta->relatedPivotKey : Str::snakeCase($refB->getShortName() . '_' . $instance->getPrimary());

                if ($pivot === null || $foreignPivotKey === null || $relatedPivotKey === null) {
                    return [];
                }

                $primaryKeyValue = $this->{$this->getPrimary()};

                $sql = $this->action->select()
                    ->from(['b' => $instance->getTableName()], [])
                    ->join(['p' => $pivot], "p.{$relatedPivotKey} = b.{$instance->getPrimary()}")
                    ->where("p.{$foreignPivotKey} = :primaryKeyValue")->getQuery();

                $stmt = $this->action->getConnection()->prepare($sql);
                $stmt->bindValue(':primaryKeyValue', $primaryKeyValue);

                $stmt->execute();

                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $results = [];
                foreach ($rows as $row) {
                    $obj = new $modelName();
                    $results[] = $obj->mapDataToObject($row);
                }

                return $results;
            }
        }

        return [];
    }

    /**
     * Collects all attributes of a given relation type (e.g., HasOne, HasMany, BelongsTo)
     * from both the class and its properties using PHP's Reflection API.
     *
     * This method allows for uniform retrieval of relation metadata defined
     * via attributes at both the class level and the property level.
     *
     * @param string $attributeClass The fully qualified class name of the relation attribute to collect.
     *                               Must be a subclass of Attribute (e.g., HasOne::class).
     * @return array An array of ReflectionAttribute instances matching the given attribute class.
     */
    protected function collectRelationAttributes(string $attributeClass): array
    {
        $ref = new \ReflectionClass($this);
        $attributes = $ref->getAttributes($attributeClass);

        foreach ($ref->getProperties() as $prop) {
            $attributes = array_merge($attributes, $prop->getAttributes($attributeClass));
        }

        return $attributes;
    }

    /**
     * Attaches one or more related models to the current model via a pivot table.
     *
     * This method handles many-to-many relationships defined using the #[HasAndBelongsToMany] attribute.
     * It inserts records into the pivot table, avoiding duplicates by checking for existing entries.
     * Additional columns can be added to the pivot table via the $extra parameter.
     *
     * Example usage:
     * $user->attach(Role::class, 3); // attaches role with ID 3
     * $user->attach(Role::class, [1, 2]); // attaches multiple roles
     * $user->attach(Role::class, 5, ['created_at' => date('Y-m-d')]); // with extra pivot data
     *
     * @param string $modelName  Fully qualified class name of the related model.
     * @param int|string|array $relatedId  One or more primary key values of the related model(s).
     * @param array $extra  Optional associative array of additional columns to store in the pivot table.
     *
     * @return bool True on success.
     *
     * @throws \Exception If the parent model's primary key is not set or no matching relationship is found.
     */
    public function attach(string $modelName, int|string|array $relatedId, array $extra = []): bool
    {
        $attributes = $this->collectRelationAttributes(HasAndBelongsToMany::class);
        $refA = new \ReflectionClass($this);
        $refB = new \ReflectionClass(new $modelName);

        foreach ($attributes as $attr) {
            $meta = $attr->newInstance();

            if ($meta->related === $modelName) {
                $related = new $modelName();

                $pivot = $meta->pivot ?: Str::snakeCase($refA->getShortName() . $refB->getShortName());
                $foreignPivotKey = $meta->foreignPivotKey ?: Str::snakeCase($refA->getShortName() . '_' . $this->getPrimary());
                $relatedPivotKey = $meta->relatedPivotKey ?: Str::snakeCase($refB->getShortName() . '_' . $related->getPrimary());

                $foreignValue = $this->{$this->getPrimary()};
                $relatedIds = is_array($relatedId) ? $relatedId : [$relatedId];

                if ($foreignValue === null) {
                    throw new \Exception("Primary key of parent model must be set before attaching.");
                }

                foreach ($relatedIds as $id) {
                    $res = $this->action->select()
                    ->from($pivot, ["COUNT({$foreignPivotKey}) AS QTD"])
                    ->where("{$foreignPivotKey} = ?", $foreignValue)
                    ->where("{$relatedPivotKey} = ?", $id)
                    ->fetch();

                    if ($res['QTD'] == 0) {
                        $data = array_merge([
                            $foreignPivotKey => $foreignValue,
                            $relatedPivotKey => $id,
                        ], $extra);

                        $this->action->insert($pivot, $data);
                    }
                }

                return true;
            }
        }

        throw new \Exception("No #[HasAndBelongsToMany] relation found for {$modelName}.");
    }

    // Remove a associação
    public function detach(string $modelName, int|string|array $relatedId): bool
    {
        return true;
    }

    // Substitui todas as roles do usuário
    public function sync(string $modelName, array $relatedIds): bool
    {
        return true;
    }
}
