<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

/**
 * Helper para renderizar um grupo de botões rádio (<input type="radio">).
 *
 * @package Jaca\View\Helper\Form\Element
 */
class Radio extends FormHelper implements IHelper
{
    /**
     * Renderiza um conjunto de radio buttons.
     *
     * @param string $id Identificador do grupo de radio buttons (name e prefixo do id).
     * @param array $elements Array associativo com valor => label dos radios.
     * @param mixed|null $value Valor selecionado (opcional).
     * @param array $options Atributos HTML adicionais para os inputs.
     * @return string HTML com os inputs radio renderizados.
     */
    public function radio(string $id, array $elements, $value = null, array $options = []): string
    {
        $metadata = $this->getInputMetadata($id);

        if ($metadata && $metadata->required) {
            $options['required'] = true;
        }

        $attr = $this->getAttr($options);

        $output = '';
        $val = $this->getValuesById($id);

        // Se o valor passado é null, tenta pegar do metadata ou do valor salvo
        if ($value === null) {
            $value = $metadata?->value ?? $val;
        }

        $i = 0;
        $idEsc = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');

        foreach ($elements as $key => $label) {
            $keyEsc = htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8');
            $labelEsc = htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8');

            $checked = ($value == $key) ? ' checked' : '';

            $inputId = "{$idEsc}-{$i}";

            $output .= "<input id=\"{$inputId}\" type=\"radio\" name=\"{$idEsc}\" value=\"{$keyEsc}\"{$checked}{$attr}>"
                . " <label for=\"{$inputId}\">{$labelEsc}</label>&nbsp;";

            $i++;
        }

        $errors = $this->getErrorsListById($id);

        $label = $this->getLabel($id, $metadata->label);

        return $label . $output . $errors;
    }
}
