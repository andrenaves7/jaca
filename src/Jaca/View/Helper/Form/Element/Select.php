<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

class Select extends FormHelper implements IHelper
{
    /**
     * Gera um elemento <select> com opções dinâmicas.
     *
     * @param string $id        Nome/id do campo
     * @param array  $values    Array de opções (valor => label)
     * @param array  $options   Atributos adicionais para o select
     * @param mixed  $selected  Valor selecionado manualmente (opcional)
     *
     * @return string HTML do <select> gerado
     */
    public function select(string $id, array $values = [], array $options = [], $selected = null): string
    {
        // Obtém metadados definidos no Model (ex: required, value)
        $metadata = $this->getInputMetadata($id);

        // Se for campo obrigatório, adiciona o atributo 'required'
        if ($metadata && $metadata->required) {
            $options['required'] = true;
        }

        // Monta os atributos HTML
        $attr = $this->getAttr($options);

        // Obtém mensagens de erro (caso existam)
        $erros = $this->getErrorsListById($id);

        // Determina o valor selecionado, se não for informado
        if ($selected === null) {
            $selected = $metadata?->value ?? $this->getValuesById($id);
        }

        // Início do <select>
        $idEsc = htmlspecialchars($id, ENT_QUOTES);
        $select = "<select id=\"{$idEsc}\" name=\"{$idEsc}\" {$attr}>";

        // Monta as <option>
        foreach ($values as $value => $label) {
            $valueEsc = htmlspecialchars((string)$value, ENT_QUOTES);
            $labelEsc = htmlspecialchars((string)$label, ENT_QUOTES);
            $isSelected = ($selected == $value) ? ' selected="selected"' : '';
            $select .= "<option value=\"{$valueEsc}\"{$isSelected}>{$labelEsc}</option>";
        }

        // Finaliza e retorna
        $select .= "</select>{$erros}";
        return $select;
    }
}
