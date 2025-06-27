<?php
namespace Jaca\Model;

use Jaca\Database\ActionFactory;
use Jaca\Database\Interfaces\IAction;
use Jaca\Model\Attributes\Column;
use Jaca\Model\Attributes\Hidden;
use Jaca\Model\Attributes\PrimaryKey;
use Jaca\Model\Attributes\Table;

abstract class Model
{
    protected IAction $action;

    protected string $name;

    protected string $primary = 'id';

    public function __construct()
    {
        $this->action = ActionFactory::create();

        $reflection = new \ReflectionClass($this);

        $attributes = $reflection->getAttributes(Table::class);

        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes(PrimaryKey::class);
            if (!empty($attrs)) {
                $this->primary = $property->getName();
                break;
            }
        }

        if (!empty($attributes)) {
            $tableAttr = $attributes[0]->newInstance();
            $this->name = $tableAttr->name;
        } else {
            $class = (new \ReflectionClass($this))->getShortName();
            $this->name = $this->inflectTableName($class);
        }
    }

    public function fetchAll($where = null, $group = null, $order = null, $limit = null, $offset = null)
	{
		return $this->action->fetchAll($this->name, $where, $group, $order, $limit, $offset);
	}

    public function fetchRow($where = null, $order = null)
	{
		return $this->action->fetchRow($this->name, $where, $order);
	}

    public function insert(array $data)
	{
		return $this->action->insert($this->name, $data);
	}

    public function update(array $data, $where = null)
	{
		return $this->action->update($this->name, $data, $where);
	}

    public function delete($where = null)
	{
		return $this->action->delete($this->name, $where);
	}

    public function getAction()
	{
		return $this->action;
	}

    public function getColumns(): array
    {
        $columns = [];
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            // Ignora campos com #[Hidden]
            if (!empty($property->getAttributes(Hidden::class))) {
                continue;
            }

            $columnAttr = $property->getAttributes(Column::class);
            if (!empty($columnAttr)) {
                $column = $columnAttr[0]->newInstance();
                $columns[] = $column->name ?? $property->getName();
            } else {
                // Se não tiver #[Column], considera o nome da propriedade
                $columns[] = $property->getName();
            }
        }

        return $columns;
    }

    private function inflectTableName(string $className): string
    {
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        return $snake . 's';
    }
}