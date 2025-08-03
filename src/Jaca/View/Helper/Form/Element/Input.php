<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\Model\Attributes\Enum;
use Jaca\Model\Attributes\HasOne;
use Jaca\Model\Attributes\Types\DataType;
use Jaca\Model\ModelRelationHelper;
use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;
use Jaca\Support\Str;

/**
 * Class Input
 *
 * Helper class responsible for rendering input form elements based on model metadata.
 * Supports rendering different input types, including text, textarea, password, hidden,
 * as well as select inputs for relations and enum attributes.
 *
 * Extends FormHelper and implements IHelper interface.
 *
 * @package Jaca\View\Helper\Form\Element
 */
class Input extends FormHelper implements IHelper
{
    
    /**
     * Generates the HTML for an input form element.
     *
     * The method uses model metadata to determine the input type, label, validations,
     * and possible options for select inputs. It supports:
     * - Primary key fields rendered as hidden inputs without labels.
     * - HasOne relations rendered as select dropdowns with related model data.
     * - Enum attributes rendered as select dropdowns with enum options.
     * - Different data types rendered as appropriate input types or textarea.
     *
     * @param string $id The field identifier (name) for the input element.
     * @param array $options Optional HTML attributes and options for the input element.
     * @return string The generated HTML string for the form input wrapped in a div.form-group.
     *
     * @throws \InvalidArgumentException If the data type specified in metadata is unsupported.
     */
    public function input(string $id, array $options = []): string
    {
        $div = '<div class="form-group ' . $id . '">';
        $closeDiv = '</div>';
        $metadata = $this->getInputMetadata($id);
        $model = FormHelper::getModel();
        $val = $metadata?->value ?? $this->getValuesById($id);
        $label = $div . $this->getLabel($id, $metadata->label);

        if ($metadata && $metadata->required) {
            $options['required'] = true;
        }

        if ($metadata && $metadata->maxlength !== null) {
            $options['maxlength'] = $metadata->maxlength;
        }

        // Se for chave primária, oculta
        if (ModelRelationHelper::isPrimary($model, $id)) {
            return (new Text($this->data))->text($id, $val, DataType::HIDDEN, $options) . $closeDiv;
        }

        // Se for relação HasOne, monta select com os dados relacionados
        if (ModelRelationHelper::hasOne($model, $id)) {
            $relation = ModelRelationHelper::getRelationMeta($model, $id, HasOne::class);

            if ($relation) {
                $instance = new $relation->related();
                $localKey = ($relation->localKey !== null && $relation->localKey !== '')
                        ? $relation->localKey : Str::snakeCase($instance->getPrimary());
                $localLabel = ModelRelationHelper::getLabelField($relation->related);

                $relationValues = $relation->related::findAll();
                $values = ['' => ''];

                foreach ($relationValues as $v) {
                    $values[$v->$localKey] = $v->$localLabel;
                }

                return $label . (new Select($this->data))->select($id, $values, $options, $val) . $closeDiv;
            }
        }

        if (ModelRelationHelper::enum($model, $id)) {
            $relation = ModelRelationHelper::getRelationMeta($model, $id, relationAttributeClass: Enum::class);
            
            if ($relation) {
                $values = ['' => ''];

                foreach ($relation->values as $key => $v) {
                    $values[$key] = $v;
                }
                return $label . (new Select($this->data))->select($id, $values, $options, $val) . $closeDiv;
            }
        }
        
        return $label . match ($metadata->type) {
            DataType::TEXT => (new Text($this->data))->text($id, $val, DataType::TEXT, $options),
            DataType::LONG_TEXT => (new Textarea($this->data))->textarea($id, $options, $val),
            DataType::DATETIME => (new Text($this->data))->text($id, $val, DataType::TEXT, $options),
            DataType::HIDDEN => (new Text($this->data))->text($id, $val, DataType::HIDDEN, $options),
            DataType::PASSWORD => (new Text($this->data))->text($id, $val, DataType::PASSWORD, $options),

            default => throw new \InvalidArgumentException("Tipo de dado não suportado: {$metadata->type}")
        } . $closeDiv;
    }
}
