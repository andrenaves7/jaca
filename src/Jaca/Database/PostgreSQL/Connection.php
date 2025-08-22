<?php
namespace Jaca\Database\PostgreSQL;

use Jaca\Database\Exceptions\DatabaseConnectionException;
use Jaca\Database\Interfaces\IConnection;

/**
 * Singleton class for managing PostgreSQL database connection.
 */
class Connection implements IConnection
{
    /**
     * @var Connection|null Singleton instance
     */
    private static ?Connection $instance = null;

    /**
     * @var string Database host
     */
    private string $host;

    /**
     * @var string Database port
     */
    private string $port;

    /**
     * @var string Database schema / database name
     */
    private string $schema;

    /**
     * @var string Database username
     */
    private string $user;

    /**
     * @var string Database password
     */
    private string $pass;

    /**
     * @var \PDO|null PDO connection instance
     */
    private ?\PDO $connection = null;

    /**
     * Private constructor to enforce singleton pattern.
     * Reads connection settings from environment variables and connects to the database.
     */
    private function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $this->port = $_ENV['DB_PORT'] ?? '5432';
        $this->schema = $_ENV['DB_SCHEMA'] ?? '';
        $this->user = $_ENV['DB_USER'] ?? 'postgres';
        $this->pass = $_ENV['DB_PASS'] ?? '';

        $this->connect();
    }

    /**
     * Returns the singleton instance of the connection.
     *
     * @return Connection
     */
    public static function getInstance(): Connection
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establishes the PDO connection to the PostgreSQL database.
     *
     * @throws DatabaseConnectionException
     */
    private function connect(): void
    {
        try {
            $port = $this->port !== '' ? " port=$this->port;" : '';
            $dsn  = 'pgsql:host=' . $this->host . ';' . $port . 'dbname=' . $this->schema;
            $this->connection = new \PDO($dsn, $this->user, $this->pass);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $env = $_ENV['APP_ENV'] ?? null;
            if ($env === 'dev') {
                throw new DatabaseConnectionException('Error connecting to database: ' . $e->getMessage());
            } else {
                throw new DatabaseConnectionException('Error connecting to database.');
            }
        }
    }

    /**
     * Returns the PDO connection instance.
     *
     * @return \PDO
     */
    public function getConnection(): \PDO
    {
        return $this->connection;
    }
}
