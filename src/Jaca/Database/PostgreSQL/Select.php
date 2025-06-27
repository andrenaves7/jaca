<?php
namespace Jaca\Database\PostgreSQL;

use Jaca\Database\Exceptions\DatabaseQueryException;
use Jaca\Database\Interfaces\IAction;
use Jaca\Database\Interfaces\ISelect;

class Select implements ISelect
{
    const LEFT = 'LEFT';
    const RIGHT = 'RIGHT';
    const INNER = 'INNER';

    private IAction $action;
    private array $table = [];
    private array $columns = [];
    private array $where = [];
    private array $orWhere = [];
    private array $order = [];
    private array $group = [];
    private array $join = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private string $sql = '';
    private array $bindings = [];
    private int $paramCounter = 0;

    public function __construct(?IAction $action = null)
    {
        $this->action = $action ?? new Action();
    }

    public function __toString(): string
    {
        return $this->sql;
    }

    public function from(array|string $table, array $columns = []): self
    {
        $table = $this->prepareTableName($table);

        foreach ($columns as $key => $val) {
            $columns[$key] = $this->isFunction($val) ? $val : $table[1] . '.' . $this->escapeIdentifier($val);
        }

        $this->table[] = $table[0];
        $this->columns = array_merge($this->columns, $columns);
        return $this->setQuery();
    }

    public function where(string $condition, float|int|string $value = null, bool $isString = true): self
    {
        $param = $this->generateParamName();
        $this->bindings[$param] = $value;
        $this->where[] = str_replace('?', ":$param", $condition);
        return $this->setQuery();
    }

    public function orWhere(string $condition, float|int|string $value = null, bool $isString = true): self
    {
        $param = $this->generateParamName();
        $this->bindings[$param] = $value;
        $this->orWhere[] = str_replace('?', ":$param", $condition);
        return $this->setQuery();
    }

    public function order(string|array $order): self
    {
        $this->order[] = is_array($order) ? implode(', ', $order) : $order;
        return $this->setQuery();
    }

    public function group(string|array $group): self
    {
        $this->group[] = is_array($group) ? implode(', ', $group) : $group;
        return $this->setQuery();
    }

    public function limit(int $limit, int $offset = 0): self
    {
        $this->limit = $limit;
        return $this->offset($offset)->setQuery();
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this->setQuery();
    }

    public function join(string|array $table, string $on, array $columns = []): self
    {
        return $this->addJoin(self::INNER, $table, $on, $columns);
    }

    public function joinLeft(string|array $table, string $on, array $columns = []): self
    {
        return $this->addJoin(self::LEFT, $table, $on, $columns);
    }

    public function joinRight(string|array $table, string $on, array $columns = []): self
    {
        return $this->addJoin(self::RIGHT, $table, $on, $columns);
    }

    public function getQuery(): string
    {
        return $this->sql;
    }

    public function fetch(): ?array
    {
        return $this->executePrepared(false);
    }

    public function fetchAll(): ?array
    {
        return $this->executePrepared(true);
    }

    private function executePrepared(bool $all): ?array
    {
        $stmt = $this->action->getConnection()->prepare($this->sql);
        foreach ($this->bindings as $param => $value) {
            $stmt->bindValue(":$param", $value);
        }
        if (!$stmt->execute()) {
            throw new DatabaseQueryException('Erro ao executar query: ' . implode(', ', $stmt->errorInfo()));
        }
        return $all ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function addJoin(string $type, string|array $table, string $on, array $columns): self
    {
        $table = $this->prepareTableName($table);

        foreach ($columns as $key => $val) {
            $columns[$key] = $this->isFunction($val) ? $val : $table[1] . '.' . $this->escapeIdentifier($val);
        }

        $this->columns = array_merge($this->columns, $columns);
        $this->join[] = "$type JOIN {$table[0]} ON $on";
        return $this;
    }

    private function setQuery(): self
    {
        $columns = $this->columns ? implode(', ', $this->columns) : '*';
        $from = implode(', ', $this->table);
        $joins = implode(' ', $this->join);

        $whereBlock = $this->buildWhereBlock();
        $group = $this->group ? 'GROUP BY ' . implode(', ', $this->group) : '';
        $order = $this->order ? 'ORDER BY ' . implode(', ', $this->order) : '';
        $limit = $this->limit !== null ? "LIMIT {$this->limit}" : '';
        $offset = $this->offset !== null ? "OFFSET {$this->offset}" : '';

        $this->sql = trim("SELECT $columns FROM $from $joins $whereBlock $group $order $limit $offset");
        return $this;
    }

    private function buildWhereBlock(): string
    {
        $clauses = [];

        if ($this->where) {
            $clauses[] = '(' . implode(' AND ', $this->where) . ')';
        }

        if ($this->orWhere) {
            $clauses[] = '(' . implode(' OR ', $this->orWhere) . ')';
        }

        return $clauses ? 'WHERE ' . implode(' OR ', $clauses) : '';
    }

    private function prepareTableName(string|array $table): array
    {
        if (is_array($table)) {
            $alias = key($table);
            $name = $table[$alias];
            return ["{$this->escapeIdentifier($name)} AS {$this->escapeIdentifier($alias)}", $alias];
        }

        return [$this->escapeIdentifier($table), $table];
    }

    private function escapeIdentifier(string $name): string
    {
        return '"' . str_replace('"', '""', $name) . '"';
    }

    private function isFunction(string $field): bool
    {
        return preg_match('/^[A-Z_]+\s*\(/i', trim($field)) === 1;
    }

    private function generateParamName(): string
    {
        return '__param_' . $this->paramCounter++;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }
}
