<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;
use Jaca\Support\Str;
use Jaca\View\Helper\Exceptions\ViewHelperMissingParameterException;

/**
 * Helper para iniciar um formulário HTML (<form>).
 *
 * @package Jaca\View\Helper\Form\Element
 */
class Start extends FormHelper implements IHelper
{
    /**
     * Gera a tag <form> de abertura com atributos configurados.
     *
     * @param \Jaca\Model\Interfaces\IModel|null $model Model para associação dos dados (opcional).
     * @param string|null $id ID do formulário (se não informado, gerado a partir do model).
     * @param string|null $action URL para envio do formulário.
     * @param string $method Método HTTP do formulário (default 'post').
     * @param array $options Atributos HTML adicionais para a tag form.
     * @throws ViewHelperMissingParameterException Se o ID não for informado nem inferido.
     * @return string Tag <form> completa de abertura.
     */
    public function start(?\Jaca\Model\Interfaces\IModel $model = null, ?string $id = null, ?string $action = null, string $method = 'post', array $options = []): string
    {
        FormHelper::setModel($model);

        // Se model existe e id não foi fornecido, gera id baseado na classe do model
        if ($model && !$id) {
            $id = str_replace('\\', '_', Str::snakeCase(get_class($model)));
        }

        if (!$id) {
            throw new ViewHelperMissingParameterException('id');
        }

        // Trata o atributo action: se for array, monta string url
        if (is_array($action)) {
            $action = count($action) > 0 ? implode('/', $action) : '';
        }

        // Escapa valores para segurança
        $idEsc = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $actionEsc = htmlspecialchars($action ?? '', ENT_QUOTES, 'UTF-8');
        $methodEsc = htmlspecialchars($method, ENT_QUOTES, 'UTF-8');

        $attr = $this->getAttr($options);

        return "<form id=\"{$idEsc}\" action=\"{$actionEsc}\" method=\"{$methodEsc}\"{$attr}>";
    }
}
