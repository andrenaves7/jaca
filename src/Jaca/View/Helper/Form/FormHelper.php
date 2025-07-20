<?php
namespace Jaca\View\Helper\Form;

use Jaca\Data\Data;
use Jaca\Model\Attributes\Column;
use Jaca\Model\Interfaces\IModel;
use Jaca\Model\Validation\Attributes\Required;
use Jaca\Support\Str;
use Jaca\View\Helper\Helper;

abstract class FormHelper extends Helper
{
    protected static ?IModel $model = null;

    public static function setModel(?IModel $model): void
    {
        self::$model = $model;
    }

    public static function getModel(): ?IModel
    {
        return self::$model;
    }

    public function __construct(Data $data)
    {
        parent::__construct($data);

        $model = FormHelper::getModel();

        if ($model) {
            $this->data->helper->errors = $model->getErrors();
            $this->data->helper->values = $model->toArray();
        }
    }

    protected function getInputMetadata(string $id): ?InputMetadata
    {
        $model = FormHelper::getModel();

        if (!$model) {
            return null;
        }

        $refClass = new \ReflectionClass($model);

        foreach ($refClass->getProperties() as $property) {
            $propName = $property->getName();
            $columnAttr = $property->getAttributes(Column::class)[0] ?? null;

            $column = $columnAttr ? $columnAttr->newInstance() : null;
            $columnName = $column?->name ?? $propName;

            // Compara todas as variações possíveis para robustez
            $matches = (
                $id === $columnName ||
                $id === $propName ||
                $id === Str::camelCase($columnName) ||
                $id === Str::snakeCase($columnName)
            );

            if ($matches) {
                $value = $property->isInitialized($model) ? $property->getValue($model) : null;
                $required = count($property->getAttributes(Required::class)) > 0;

                return new InputMetadata(
                    name: $columnName,
                    value: $value,
                    required: $required,
                    maxlength: $column?->length,
                    type: $column?->type ?? 'string',
                    nullable: $column?->nullable ?? false
                );
            }
        }

        return null;
    }
}