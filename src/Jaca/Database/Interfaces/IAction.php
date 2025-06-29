<?php
namespace Jaca\Database\Interfaces;

use Jaca\Model\Interfaces\IModel;

interface IAction
{
    public function setReturning($returning);
    public function getConnection();
    public function fetchAll(string $table, mixed $where = null, mixed $group = null, mixed $order = null, int $limit = null, int $offset = null): array;
    public function fetchRow(string $table, mixed $where, mixed $order = null): array|null;
    public function insert(string $table, array $data): bool|string;
    public function update(string $table, array $data, mixed $where = ''): bool;
    public function delete(string $table, mixed $where): bool;
    public function count(string $table, ?array $where = null): int;
    public function quote(string $string): string;
    public function querySQL(string $sql, bool $all = true): mixed;
    public function executeSQL(string $sql): bool;
	public function beginTransaction(): void;
	public function commit(): void;
	public function rollBack(): void;
	public function select(?IModel $model = null): ISelect;
}