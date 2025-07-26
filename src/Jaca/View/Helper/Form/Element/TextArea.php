<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

/**
 * Helper para renderizar um campo <textarea> em formulários.
 * Usa metadados para configurar atributos e preencher valores.
 */
class Textarea extends FormHelper implements IHelper
{
    /**
     * Gera o HTML de um campo <textarea>.
     *
     * @param string $id Identificador e nome do campo.
     * @param array $options Atributos adicionais para o textarea (ex: class, rows).
     * @param mixed|null $value Valor inicial do campo (pode ser sobrescrito por valores submetidos).
     * @return string HTML do textarea renderizado.
     */
    public function textarea(string $id, array $options = [], $value = null): string
    {
        $metadata = $this->getInputMetadata($id);

        if ($metadata) {
            if ($metadata->maxlength !== null) {
                $options['maxlength'] = $metadata->maxlength;
            }

            if ($metadata->required) {
                $options['required'] = true;
            }

            // Suporte a atributos extras comuns para textarea
            foreach (['rows', 'cols', 'placeholder', 'class', 'style'] as $attr) {
                if (isset($metadata->$attr)) {
                    $options[$attr] = $metadata->$attr;
                }
            }
        }

        // Define valor inicial considerando prioridade: argumento > metadata > valores submetidos
        if ($value === null || $value === '') {
            $value = $metadata?->value ?? $this->getValuesById($id);
        }

        $attr = $this->getAttr($options);
        $valueEsc = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        $idEsc = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');

        $erros = $this->getErrorsListById($id);

        return "<textarea id=\"{$idEsc}\" name=\"{$idEsc}\"{$attr}>{$valueEsc}</textarea>{$erros}";
    }
}
