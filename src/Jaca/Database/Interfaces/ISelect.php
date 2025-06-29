<?php
namespace Jaca\Database\Interfaces;

use Jaca\Model\Interfaces\IModel;

interface ISelect
{
    public function __toString(): string;
    public function from(string|array $table, array $columns = []): self;
    public function where(string $condition, float|int|string $value = null, bool $isString = true): self;
    public function orWhere(string $condition, float|int|string $value = null, bool $isString = true): self;
    public function order(string|array $order): self;
    public function group(string|array $group): self;
    public function limit(int $limit, int $offset = 0): self;
    public function offset(int $offset): self;
    public function join(string|array $table, string $on, array $columns = []): self;
    public function joinLeft(string|array $table, string $on, array $columns = []): self;
    public function joinRight(string|array $table, string $on, array $columns = []): self;
    public function getQuery(): string;
    public function fetch(): array|IModel|null;
    public function fetchAll(): array|IModel|null;
}