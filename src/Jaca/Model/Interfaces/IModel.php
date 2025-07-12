<?php
namespace Jaca\Model\Interfaces;

use Jaca\Database\Interfaces\IAction;
use Jaca\Database\Interfaces\ISelect;

/**
 * Interface IModel
 * 
 * Defines the contract for all model classes in the application.
 * Models implementing this interface must provide persistence methods,
 * querying capabilities, and access to underlying database actions.
 * 
 * @package Jaca\Model\Interfaces
 */
interface IModel
{
    /**
     * Saves the current model instance to the database.
     *
     * @return bool True on success, false on failure.
     */
    public function save(): bool;

    /**
     * Deletes the current model instance from the database.
     *
     * @return bool True on success, false on failure.
     */
    public function delete(): bool;

    /**
     * Finds a model instance by its primary key.
     *
     * @param int|string $id The primary key value.
     * @return static|null The found model instance or null if not found.
     */
    public static function find(int|string $id): ?static;

    /**
     * Retrieves multiple model instances matching optional criteria.
     *
     * @param array|null $where Conditions to filter the query.
     * @param array|null $order Order specification.
     * @param int|null $limit Maximum number of records to retrieve.
     * @param int|null $offset Offset for records retrieval.
     * @return static[] Array of model instances.
     */
    public static function findAll(array $where = null, array $order = null, int $limit = null, int $offset = null): array;

    /**
     * Finds model instances by specific conditions.
     *
     * @param array $conditions Associative array of field => value conditions.
     * @return static[] Array of matching model instances.
     */
    public static function findBy(array $conditions): array;

    /**
     * Counts the number of records matching optional conditions.
     *
     * @param array|null $where Optional filter conditions.
     * @return int Number of matching records.
     */
    public static function count(array $where = null): int;

    /**
     * Checks if records exist matching optional conditions.
     *
     * @param array|null $where Optional filter conditions.
     * @return bool True if records exist, false otherwise.
     */
    public static function exists(array $where = null): bool;

    /**
     * Returns a new select query builder instance.
     *
     * @return ISelect Query builder interface instance.
     */
    public static function select(): ISelect;

    /**
     * Returns the database action handler associated with this model.
     *
     * @return IAction Database action interface instance.
     */
    public function getAction(): IAction;

    /**
     * Reloads the current model instance from the database.
     *
     * @return bool True on success, false otherwise.
     */
    public function reload(): bool;

    /**
     * Updates multiple records matching conditions.
     *
     * @param array $data Associative array of fields to update.
     * @param array $where Conditions to select which records to update.
     * @return bool True on success, false otherwise.
     */
    public static function updateMany(array $data, array $where): bool;
}
