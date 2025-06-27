<?php
namespace Jaca\Database\PostgreSQL;

use Jaca\Database\Exceptions\DatabaseConnectionException;
use Jaca\Database\Interfaces\IConnection;

class Connection implements IConnection
{
    private static ?Connection $instance = null;
    private string $host;
    private string $port;
    private string $schema;
    private string $user;
    private string $pass;
    private ?\PDO $connection = null;

    private function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $this->port = $_ENV['DB_PORT'] ?? '5432';
        $this->schema = $_ENV['DB_SCHEMA'] ?? '';
        $this->user = $_ENV['DB_USER'] ?? 'postgres';
        $this->pass = $_ENV['DB_PASS'] ?? '';

        $this->connect();
    }

    public static function getInstance(): Connection
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void
    {
        try {
            $port = $this->port !== '' ? " port=$this->port;" : '';
            $dsn  = 'pgsql:host=' . $this->host . ';' . $port . 'dbname=' . $this->schema;
            $this->connection = new \PDO($dsn, $this->user, $this->pass);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {exit($e->getMessage());
            $env = $_ENV['APP_ENV'] ?? null;
            if ($env === 'dev') {
                throw new DatabaseConnectionException('Erro ao conectar ao banco de dados: ' . $e->getMessage());
            } else {
                throw new DatabaseConnectionException('Erro ao conectar ao banco de dados.');
            }
        }
    }

    public function getConnection(): \PDO
    {
        return $this->connection;
    }
}
