<?php
namespace Jaca\Model;

use Jaca\Database\ActionFactory;
use Jaca\Database\Interfaces\IAction;
use Jaca\Database\Interfaces\ISelect;
use Jaca\Model\Interfaces\IModel;

abstract class Model extends ModelCore implements IModel
{
    protected IAction $action;

    public function __construct()
    {
        parent::__construct();
        $this->action = ActionFactory::create();
    }

    public function save(): bool
    {
        $pk = $this->getPrimary();
        $pkValue = $this->$pk;

        $isNew = ($pkValue === null || $pkValue === 0);

        // Passa o flag para saber se é insert ou update
        $props = $this->extractColumnValues($isNew);

        if ($isNew) {
            $id = $this->action->insert($this->getName(), $props);
            $this->$pk = $id;
            return true;
        } else {
            return $this->action->update($this->getName(), $props, [$pk => $pkValue]);
        }
    }

    public function delete(): bool
    {
        $pk = $this->getPrimary();print_r([$pk => $this->$pk]);
        return $this->action->delete($this->getName(), [$pk => $this->$pk]);
    }

    public static function find(int $id): ?static
    {
        $instance = new static();
        $data = $instance->action->fetchRow($instance->getName(), [$instance->getPrimary() => $id]);
        return $data ? $instance->mapDataToObject($data) : null;
    }

    public static function findAll(
        array $where = null,
        array $order = null,
        int $limit = null,
        int $offset = null
    ): array {
        $instance = new static();
        $results = $instance->getAction()->fetchAll(
            $instance->getName(),
            $where,
            null,    // groupBy
            $order,
            $limit,
            $offset
        );

        $objects = [];
        foreach ($results as $data) {
            $objects[] = $instance->mapDataToObject($data);
        }

        return $objects;
    }

    public static function findBy(array $conditions): array
    {
        $instance = new static();
        $rows = $instance->getAction()->fetchAll($instance->getName(), $conditions);

        $results = [];
        foreach ($rows as $row) {
            $results[] = $instance->mapDataToObject($row);
        }

        return $results;
    }

    public static function count(array $where = null): int
    {
        $instance = new static();
        return $instance->getAction()->count($instance->getName(), $where);
    }

    public static function exists(array $where = null): bool
    {
        return static::count($where) > 0;
    }

    public static function updateMany(array $data, array $where): bool
    {
        $instance = new static();
        return $instance->getAction()->update($instance->getName(), $data, $where);
    }

    public static function select(): ISelect
    {
        $model = new static();
        return $model->action->select($model)->from($model->getName());
    }

    public function reload(): bool
    {
        $pk = $this->getPrimary();
        if (!isset($this->$pk)) {
            return false;
        }

        $data = $this->getAction()->fetchRow($this->getName(), [$pk => $this->$pk]);

        if (!$data) {
            return false;
        }

        $this->mapDataToObject($data);
        return true;
    }

    public function getAction(): IAction
    {
        return $this->action;
    }
}
