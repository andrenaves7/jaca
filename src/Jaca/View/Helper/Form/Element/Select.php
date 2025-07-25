<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\Model\Attributes\HasOne;
use Jaca\Model\ModelRelationHelper;
use Jaca\Support\Str;
use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

/**
 * Helper para renderizar um elemento <select> em formulários.
 *
 * @package Jaca\View\Helper\Form\Element
 */
class Select extends FormHelper implements IHelper
{
    /**
     * Gera o HTML para um campo select com opções dinâmicas.
     *
     * @param string $id Identificador e nome do campo select.
     * @param array $values Array associativo com valor => label das opções.
     * @param array $options Atributos HTML adicionais para o select.
     * @param mixed|null $selected Valor selecionado (opcional).
     * @return string HTML do select gerado.
     */
    public function select(string $id, array $values = [], array $options = [], $selected = null): string
    {
        $metadata = $this->getInputMetadata($id);

        if (empty($values)) {
            $relation = ModelRelationHelper::getRelationMeta(FormHelper::getModel(), $id, HasOne::class);

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
            }
        }

        if ($metadata && $metadata->required) {
            $options['required'] = true;
        }

        $attr = $this->getAttr($options);
        $errors = $this->getErrorsListById($id);

        // Determina o valor selecionado (prioridade: argumento > metadata > valor submetido)
        if ($selected === null) {
            $selected = $metadata?->value ?? $this->getValuesById($id);
        }

        $idEsc = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $select = "<select id=\"{$idEsc}\" name=\"{$idEsc}\" {$attr}>";

        foreach ($values as $value => $label) {
            $valueEsc = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            $labelEsc = htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8');
            $isSelected = ($selected == $value) ? ' selected="selected"' : '';
            $select .= "<option value=\"{$valueEsc}\"{$isSelected}>{$labelEsc}</option>";
        }

        $select .= "</select>{$errors}";

        $label = $this->getLabel($id, $metadata->label);

        return $label . $select;
    }
}
