<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

/**
 * Classe responsável por renderizar o elemento HTML <textarea> em formulários.
 * 
 * Utiliza metadados e valores dinâmicos para gerar os atributos necessários,
 * incluindo validações, preenchimento automático e exibição de erros.
 */
class TextArea extends FormHelper implements IHelper
{
    /**
     * Gera um campo <textarea> com atributos e valores dinâmicos.
     *
     * @param string $id O ID e o name do campo textarea.
     * @param array $options Atributos HTML adicionais (ex: ['class' => 'form-control']).
     * @param mixed|null $value Valor padrão para preenchimento (pode ser sobrescrito por valores submetidos).
     * @return string HTML renderizado do campo <textarea>.
     */
    public function textArea(string $id, array $options = [], $value = null): string
    {
        $metadata = $this->getInputMetadata($id);

        // Aplica metadados ao array de opções
        if ($metadata) {
            if ($metadata->maxlength !== null) {
                $options['maxlength'] = $metadata->maxlength;
            }

            if ($metadata->required) {
                $options['required'] = true;
            }
        }

        // Define o valor do campo (prioridade: argumento > metadata > valor submetido)
        if ($value === null) {
            $value = $metadata?->value ?? $this->getValuesById($id);
        }

        // Escapa o valor para segurança contra XSS
        $value = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

        // Revalida o valor se ainda estiver vazio
        if (strlen(trim($value)) === 0) {
            $value = htmlspecialchars((string)$this->getValuesById($id), ENT_QUOTES, 'UTF-8');
        }

        $attr  = $this->getAttr($options);
        $erros = $this->getErrorsListById($id);

        return "<textarea id=\"{$id}\" name=\"{$id}\"{$attr}>{$value}</textarea>{$erros}";
    }
}
