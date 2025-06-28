<?php
namespace Jaca\Model;

use Jaca\Database\ActionFactory;
use Jaca\Database\Interfaces\IAction;
use Jaca\Model\Attributes\Column;
use Jaca\Model\Attributes\Hidden;
use Jaca\Model\Attributes\PrimaryKey;
use Jaca\Model\Attributes\Table;
use Jaca\Model\Validation\Interfaces\IValidator;

abstract class Model
{
    protected IAction $action;
    protected string $name;
    protected string $primary = 'id';
    private array $errors = [];

    public function __construct()
    {
        $this->action = ActionFactory::create();

        $reflection = new \ReflectionClass($this);

        // Inicializa todas as propriedades públicas com null, se ainda não tiverem sido inicializadas
        $this->fillAttributes($reflection);
        $this->setName($reflection);
        $this->setPrimary($reflection);
    }

    private function fillAttributes(\ReflectionClass $reflection): void
    {
        foreach (($reflection)->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isInitialized($this)) {
                $type = $property->getType();
                if ($type && !$type->isBuiltin()) {
                    $property->setValue($this, null); // Classes/Objetos → null
                } elseif ($type && $type instanceof \ReflectionNamedType) {
                    switch ($type->getName()) {
                        case 'int':
                            $property->setValue($this, 0);
                            break;
                        case 'float':
                            $property->setValue($this, 0.0);
                            break;
                        case 'string':
                            $property->setValue($this, '');
                            break;
                        case 'bool':
                            $property->setValue($this, false);
                            break;
                        default:
                            $property->setValue($this, null);
                            break;
                    }
                } else {
                    $property->setValue($this, null);
                }
            }
        }
    }

    private function setName(\ReflectionClass $reflection): void
    {
        $attributes = $reflection->getAttributes(Table::class);

        if (!empty($attributes)) {
            $tableAttr = $attributes[0]->newInstance();
            $this->name = $tableAttr->name;
        } else {
            $class = (new \ReflectionClass($this))->getShortName();
            $this->name = $this->inflectTableName($class);
        }
    }

    private function setPrimary(\ReflectionClass $reflection): void
    {
        foreach ($reflection->getProperties() as $property) {
            $attrs = $property->getAttributes(PrimaryKey::class);
            if (!empty($attrs)) {
                $this->primary = $property->getName();
                break;
            }
        }
    }

    private function inflectTableName(string $className): string
    {
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        return $snake . 's';
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrimary(): string
    {
        return $this->primary;
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

    public function isValid(): bool
    {
        $this->errors = [];
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $value = $this->{$property->getName()};
            $attributes = $property->getAttributes();

            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                if ($instance instanceof IValidator) {
                    $error = $instance->validate($property->getName(), $value, $this);
                    if ($error !== null) {
                        $this->errors[$property->getName()][] = $error;
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}