<?php
namespace Jaca\Model\Validation\Attributes;

use Jaca\Model\Attributes\Column;
use Jaca\Model\Validation\Exceptions\ModelRequiredException;
use Jaca\Model\Validation\Interfaces\IValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Unique implements IValidator
{
    private ?string $message;
    private ?string $table;
    private ?string $field;

    public function __construct(?string $message = null, ?string $table = null, ?string $field = null) {
        $this->message = $message;
        $this->table = $table;
        $this->field = $field;
    }

    public function validate(string $property, mixed $value, ?object $model = null): ?string
    {
        if ($model === null) {
            throw new ModelRequiredException('Model instance required for Unique validation.');
        }

        $table = $this->table ?? $model->getTableName();
        $field = $this->field ?? $this->getField($model, $property);

        // Monta consulta usando o método getAction
        $select = $model->getAction()->select()
            ->from($table, ["COUNT(*) as qtd"])
            ->where("{$field} = ?", $value);

        // Ignora o próprio registro, se já tiver id (edição)
        $primary = $model->getPrimary();
        $primaryValue = $model->{$primary} ?? null;
        if (!empty($primaryValue)) {
            $select->where("{$primary} <> ?", $primaryValue);
        }

        $result = $select->fetch();

        if (isset($result['qtd']) && $result['qtd'] > 0) {
            return "O valor do campo '{$property}' já está em uso.";
        }

        return null;
    }

    private function getField(object $model, string $property): string
    {
        $reflection = new \ReflectionClass($model);
        $propertyReflection = $reflection->getProperty($property);

        // Busca o nome da coluna real no banco (se tiver #[Column])
        $columnName = $property;
        $columnAttr = $propertyReflection->getAttributes(Column::class);
        if (!empty($columnAttr)) {
            $column = $columnAttr[0]->newInstance();
            if (!empty($column->name)) {
                $columnName = $column->name;
            }
        }

        return $columnName;
    }
}
