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
     * Handles dynamic access to related models based on relationship attributes.
     *
     * This method allows calling relationship methods like $user->roles() or $post->author()
     * without explicitly defining them. It detects which relationship (BelongsTo, HasOne,
     * HasMany, HasAndBelongsToMany) matches the requested name, based on the metadata
     * defined in attributes like #[BelongsTo], #[HasMany], etc.
     *
     * The method resolves the called name (e.g. 'user') to a related model class
     * and dispatches the appropriate relationship loader method.
     *
     * Example usage:
     * $role = Role::find(1);
     * $user = $role->user(); // Automatically resolves #[BelongsTo(User::class)]
     *
     * @param string $name The called method name (e.g. 'user', 'roles').
     * @param array $args Arguments passed to the method (not used).
     *
     * @return mixed The related model(s) returned by the resolved relationship method.
     *
     * @throws \Exception If no matching relationship is found for the given method name.
     */
    public function __call(string $name, array $args)
    {
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

        throw new \Exception("No relationship found for '{$name}' in " . static::class);
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
}
