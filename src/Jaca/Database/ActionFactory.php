<?php
namespace Jaca\Database;

use Jaca\Database\MySQL\Action as MySQLAction;
use Jaca\Database\PostgreSQL\Action as PgSQLAction;
use Jaca\Database\Exceptions\DatabaseConnectionException;

/**
 * Factory to create database action instances
 * based on the configured driver.
 */
class ActionFactory
{
    /**
     * Creates an IAction instance according to the database driver.
     *
     * @param \PDO|null $connection Optional PDO connection. If not provided, the connection is managed internally.
     * 
     * @return \Jaca\Database\Interfaces\IAction Database-specific action instance.
     * 
     * @throws DatabaseConnectionException If the configured driver is not supported.
     */
    public static function create(?\PDO $connection = null): \Jaca\Database\Interfaces\IAction
    {
        $driver = strtolower($_ENV['DB_ADAPTER'] ?? 'pgsql');
        
        switch (strtolower($driver)) {
            case 'mysql':
                return new MySQLAction($connection);
            case 'pgsql':
            case 'postgresql':
                return new PgSQLAction($connection);
            default:
                throw new DatabaseConnectionException("Database driver '{$driver}' is not supported.");
        }
    }
}
