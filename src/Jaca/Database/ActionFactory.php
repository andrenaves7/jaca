<?php
namespace Jaca\Database;

use Jaca\Database\MySQL\Action as MySQLAction;
use Jaca\Database\PostgreSQL\Action as PgSQLAction;
use Jaca\Database\Exceptions\DatabaseConnectionException;

class ActionFactory
{
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
                throw new DatabaseConnectionException("Driver de banco '{$driver}' não suportado.");
        }
    }
}
