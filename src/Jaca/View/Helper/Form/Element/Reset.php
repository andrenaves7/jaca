<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

/**
 * Helper para renderizar um botão de reset em formulários.
 *
 * @package Jaca\View\Helper\Form\Element
 */
class Reset extends FormHelper implements IHelper
{
    /**
     * Gera o HTML para um botão <input type="reset">.
     *
     * @param string $id Identificador do botão reset.
     * @param string|null $value Texto exibido no botão (default: 'Reset').
     * @param array $options Atributos HTML adicionais para o botão.
     * @return string HTML do botão reset.
     */
    public function reset(string $id, ?string $value = null, array $options = []): string
    {
        $value = $value ?? 'Reset';

        $attributes = '';
        foreach ($options as $attr => $val) {
            $attrEsc = htmlspecialchars($attr, ENT_QUOTES, 'UTF-8');
            $valEsc = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
            $attributes .= " {$attrEsc}=\"{$valEsc}\"";
        }

        $idEsc = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $valueEsc = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return "<input type=\"reset\" id=\"{$idEsc}\" value=\"{$valueEsc}\"{$attributes} />";
    }
}
