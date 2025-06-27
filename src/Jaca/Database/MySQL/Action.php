<?php
namespace Jaca\Database\MySQL;

use Jaca\Database\Exceptions\DatabaseQueryException;
use Jaca\Database\Interfaces\IAction;
use Jaca\Database\Interfaces\ISelect;

class Action implements IAction
{
    private \PDO $connection;

    public function __construct(?\PDO $connection = null)
    {
        $this->connection = $connection ?? Connection::getInstance()->getConnection();
    }

    public function setReturning($returning): void
    {}

    public function getConnection(): \PDO
    {
        return $this->connection;
    }

    public function fetchAll(string $table, mixed $where = null, mixed $group = null, mixed $order = null, int $limit = null, int $offset = null): array
    {
        $params = [];

        if ($where instanceof Select) {
            $sql = $where->getQuery();
            $stmt = $this->connection->prepare($sql);
        } else {
            [$whereSql, $whereParams] = $this->buildWhere($where);
            $params = array_merge($params, $whereParams);

            $group = $this->mountGroupBy($group);
            $order = $this->mountOrderBy($order);

            // MySQL não suporta bind para LIMIT/OFFSET
            $limitSql = '';
            if ($limit !== null) {
                $limit = (int) $limit;
                $limitSql = 'LIMIT ' . $limit;
            }

            $offsetSql = '';
            if ($offset !== null) {
                $offset = (int) $offset;
                $offsetSql = 'OFFSET ' . $offset;
            }

            $sql = trim("SELECT * FROM {$table} {$whereSql} {$group} {$order} {$limitSql} {$offsetSql}");
            $stmt = $this->connection->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
        }

        if (!$stmt->execute()) {
            throw new DatabaseQueryException('Erro ao executar query: ' . implode(', ', $stmt->errorInfo()));
        }

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function fetchRow(string $table, mixed $where = null, mixed $order = null): array|null
    {
        $params = [];

        if ($where instanceof Select) {
            $sql = $where->getQuery();
            $stmt = $this->connection->prepare($sql);
        } else {
            [$whereSql, $whereParams] = $this->buildWhere($where);
            $params = array_merge($params, $whereParams);

            $orderSql = $this->mountOrderBy($order);

            $sql = trim("SELECT * FROM {$table} {$whereSql} {$orderSql} LIMIT 1");
            $stmt = $this->connection->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
        }

        if (!$stmt->execute()) {
            throw new DatabaseQueryException('Erro ao executar query: ' . implode(', ', $stmt->errorInfo()));
        }

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function insert(string $table, array $data): bool|string
	{
		$columns = implode(', ', array_keys($data));
		$values  = ':' . implode(', :', array_keys($data));
		$sql     = trim('INSERT INTO ' . $table . ' (' . $columns . ') VALUES (' . $values . ')');
		$res     = $this->connection->prepare($sql);
	
		foreach ($data as $key => $value) {
			$res->bindValue(':' . $key, $value);
		}
		
		if ($res->execute()) {
			return $this->connection->lastInsertId();
		}

        return false;
	}

    public function update(string $table, array $data, mixed $where = null): bool
    {
        $setParts = [];
        $params = [];

        // Cria os binds para os dados
        foreach ($data as $key => $value) {
            $paramKey = '__set_' . preg_replace('/\W+/', '_', $key);
            $setParts[] = "{$key} = :{$paramKey}";
            $params[$paramKey] = $value;
        }

        [$whereSql, $whereParams] = $this->buildWhere($where);
        $params = array_merge($params, $whereParams);

        $setSql = implode(', ', $setParts);
        $sql = "UPDATE {$table} SET {$setSql} {$whereSql}";

        $stmt = $this->connection->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        return $stmt->execute();
    }

    public function delete(string $table, mixed $where = null): bool
    {
        [$whereSql, $params] = $this->buildWhere($where);

        $sql = "DELETE FROM {$table} {$whereSql}";
        $stmt = $this->connection->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }

        return $stmt->execute();
    }

    public function quote(string $string): string
	{
        return $this->connection->quote(trim($string));
	}

    public function querySQL(string $sql, bool $all = true): mixed
	{
		if ($this->connection instanceof \PDO) {
			$res = $this->connection->query($sql, \PDO::FETCH_ASSOC);
			
			if ($all == true) {
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
		$res = $this->connection->prepare($sql);
		
		return $res->execute();
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

    public function select(): ISelect
	{
		return new Select($this);
	}

    private function mountGroupBy(mixed $group): string
	{
		$sql = '';
		if (is_array($group)) {
			$sql .= implode(', ', $group);
		} else {
			$sql = $group;
		}
		
		$sql = $sql? 'GROUP BY ' . $sql: $sql;
		
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
		
		$sql = $sql? 'ORDER BY ' . $sql: $sql;
		
		return ' ' . $sql . ' ';
	}

    private function buildWhere(mixed $where): array
    {
        if (is_array($where)) {
            $conditions = [];
            $params = [];

            foreach ($where as $key => $value) {
                $paramKey = '__' . preg_replace('/\W+/', '_', $key);
                $conditions[] = "{$key} = :{$paramKey}";
                $params[$paramKey] = $value;
            }

            $sql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
            return [$sql, $params];
        }

        if ($where === null) {
            return ['', []];
        }

        throw new DatabaseQueryException('WHERE deve ser um array associativo ou objeto Select.');
    }
}