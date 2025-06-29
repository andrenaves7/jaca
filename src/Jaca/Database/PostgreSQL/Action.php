<?php
namespace Jaca\Database\PostgreSQL;

use Jaca\Database\Exceptions\DatabaseQueryException;
use Jaca\Database\Interfaces\IAction;
use Jaca\Database\Interfaces\ISelect;
use Jaca\Model\Interfaces\IModel;

class Action implements IAction
{
    private \PDO $connection;

    public function __construct(?\PDO $connection = null)
    {
        $this->connection = $connection ?? Connection::getInstance()->getConnection();
    }

    public function setReturning($returning): void
    {
        // Pode ser implementado para suportar RETURNING do PostgreSQL, se quiser
    }

    public function getConnection(): \PDO
    {
        return $this->connection;
    }

    public function fetchAll(string $table, mixed $where = null, mixed $group = null, mixed $order = null, int $limit = null, int $offset = null): array
    {
        $params = [];

        if ($where instanceof Select) {
            $sql = $where->getQuery();
            $params = $where->getBindings();
            $stmt = $this->connection->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
        } else {
            [$whereSql, $whereParams] = $this->buildWhere($where);
            $params = $whereParams;

            $group = $this->mountGroupBy($group);
            $order = $this->mountOrderBy($order);

            $limitSql = $limit !== null ? 'LIMIT :__limit' : '';
            $offsetSql = $offset !== null ? 'OFFSET :__offset' : '';

            $sql = trim("SELECT * FROM {$this->escapeIdentifier($table)} {$whereSql} {$group} {$order} {$limitSql} {$offsetSql}");
            $stmt = $this->connection->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            if ($limit !== null) {
                $stmt->bindValue(':__limit', (int)$limit, \PDO::PARAM_INT);
            }
            if ($offset !== null) {
                $stmt->bindValue(':__offset', (int)$offset, \PDO::PARAM_INT);
            }
        }

        if (!$stmt->execute()) {
            throw new DatabaseQueryException('Erro ao executar query: ' . implode(', ', $stmt->errorInfo()));
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function fetchRow(string $table, mixed $where = null, mixed $order = null): ?array
    {
        $params = [];

        if ($where instanceof Select) {
            $sql = $where->getQuery();
            $params = $where->getBindings();
            $stmt = $this->connection->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
        } else {
            [$whereSql, $whereParams] = $this->buildWhere($where);
            $params = $whereParams;

            $orderSql = $this->mountOrderBy($order);

            $sql = trim("SELECT * FROM {$this->escapeIdentifier($table)} {$whereSql} {$orderSql} LIMIT 1");
            $stmt = $this->connection->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
        }

        if (!$stmt->execute()) {
            throw new DatabaseQueryException('Erro ao executar query: ' . implode(', ', $stmt->errorInfo()));
        }

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function insert(string $table, array $data): bool|string
    {
        $columns = implode(', ', array_map([$this, 'escapeIdentifier'], array_keys($data)));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->escapeIdentifier($table)} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->connection->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        if ($stmt->execute()) {
            return $this->connection->lastInsertId();
        }

        return false;
    }

    public function update(string $table, array $data, mixed $where = null): bool
    {
        $setParts = [];
        $params = [];

        foreach ($data as $key => $value) {
            $paramKey = '__set_' . preg_replace('/\W+/', '_', $key);
            $setParts[] = $this->escapeIdentifier($key) . " = :{$paramKey}";
            $params[$paramKey] = $value;
        }

        [$whereSql, $whereParams] = $this->buildWhere($where);
        $params = array_merge($params, $whereParams);

        $setSql = implode(', ', $setParts);
        $sql = "UPDATE {$this->escapeIdentifier($table)} SET {$setSql} {$whereSql}";

        $stmt = $this->connection->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }

    public function delete(string $table, mixed $where = null): bool
    {
        [$whereSql, $params] = $this->buildWhere($where);

        $sql = "DELETE FROM {$this->escapeIdentifier($table)} {$whereSql}";
        $stmt = $this->connection->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        return $stmt->execute();
    }

    public function count(string $table, ?array $where = null): int
    {
        [$whereSql, $params] = $this->buildWhere($where);
        $sql = "SELECT COUNT(*) as total FROM {$table} {$whereSql}";
        
        $stmt = $this->connection->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        if (!$stmt->execute()) {
            throw new DatabaseQueryException('Erro ao executar count: ' . implode(', ', $stmt->errorInfo()));
        }

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }

    public function quote(string $string): string
    {
        return $this->connection->quote(trim($string));
    }

    public function querySQL(string $sql, bool $all = true): mixed
    {
        if ($this->connection instanceof \PDO) {
            $res = $this->connection->query($sql, \PDO::FETCH_ASSOC);

            if ($all) {
                return $res->fetchAll();
            } else {
                return $res->fetch();
            }
        } else {
            throw new DatabaseQueryException('Erro ao executar query.');
        }
    }

    public function executeSQL(string $sql): bool
    {
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute();
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    public function select(?IModel $model = null): ISelect
    {
        return new Select($this, $model);
    }

    private function mountGroupBy(mixed $group): string
    {
        $sql = '';
        if (is_array($group)) {
            $sql .= implode(', ', $group);
        } else {
            $sql = $group;
        }

        $sql = $sql ? 'GROUP BY ' . $sql : '';

        return ' ' . $sql . ' ';
    }

    private function mountOrderBy(mixed $order): string
    {
        $sql = '';
        if (is_array($order)) {
            $sql .= implode(', ', $order);
        } else {
            $sql = $order;
        }

        $sql = $sql ? 'ORDER BY ' . $sql : '';

        return ' ' . $sql . ' ';
    }

    private function buildWhere(mixed $where): array
    {
        if (is_array($where)) {
            $conditions = [];
            $params = [];

            foreach ($where as $key => $value) {
                $paramKey = '__' . preg_replace('/\W+/', '_', $key);
                $conditions[] = $this->escapeIdentifier($key) . " = :{$paramKey}";
                $params[$paramKey] = $value;
            }

            $sql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
            return [$sql, $params];
        }

        if ($where === null) {
            return ['', []];
        }

        throw new \InvalidArgumentException("WHERE deve ser um array associativo ou objeto Select.");
    }

    private function escapeIdentifier(string $name): string
    {
        return '"' . str_replace('"', '""', $name) . '"';
    }
}
