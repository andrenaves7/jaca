<?php
namespace Jaca\View\Helper\Form;

use Jaca\Data\Data;
use Jaca\Model\Attributes\Column;
use Jaca\Model\Attributes\Label;
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

    /**
     * Retrieves the metadata for a given input field ID by analyzing the associated model's properties.
     *
     * This method uses reflection to inspect model attributes such as Column, Label, and Required
     * to determine the field's metadata, including name, value, label, and validation constraints.
     * It normalizes property and column names to increase robustness in matching.
     *
     * @param string $id The input field identifier (can be in snake_case or camelCase).
     * @return InputMetadata|null Returns an InputMetadata object if found, or null if not matched.
     */
    protected function getInputMetadata(string $id): ?InputMetadata
    {
        $model = FormHelper::getModel();

        if (!$model) {
            return null;
        }

        static $reflectionCache = [];

        $className = get_class($model);

        if (!isset($reflectionCache[$className])) {
            $reflectionCache[$className] = new \ReflectionClass($model);
        }

        $refClass = $reflectionCache[$className];
        $normalizedId = Str::snakeCase($id);

        foreach ($refClass->getProperties() as $property) {
            $propName = $property->getName();
            $columnAttr = $property->getAttributes(Column::class)[0] ?? null;
            $labelAttr = $property->getAttributes(Label::class)[0] ?? null;

            $column = $columnAttr ? $columnAttr->newInstance() : null;
            $columnName = $column?->name ?? $propName;

            // Normalize identifiers for comparison
            $normalizedColumn = Str::snakeCase($columnName);
            $normalizedProp = Str::snakeCase($propName);

            // Compare all variations in normalized form
            $matches = (
                $normalizedId === $normalizedColumn ||
                $normalizedId === $normalizedProp
            );

            if ($matches) {
                // Label priority: Column attribute > Label attribute > derived from column name
                $labelValue = $column->label ?? $labelAttr?->newInstance()?->label ?? Str::title($columnName);

                $value = $property->isInitialized($model) ? $property->getValue($model) : null;
                $required = count($property->getAttributes(Required::class)) > 0;

                return new InputMetadata(
                    name: $columnName,
                    value: $value,
                    label: $labelValue,
                    required: $required,
                    maxlength: $column?->length,
                    type: $column?->type ?? 'string',
                    nullable: $column?->nullable ?? false
                );
            }
        }

        return null;
    }

    protected function getLabel(string $id, string $label): string
    {
        return '<label for="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '">' . 
            htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . 
            '</label>';
    }
}