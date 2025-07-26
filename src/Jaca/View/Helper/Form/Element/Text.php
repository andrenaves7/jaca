<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

class Text extends FormHelper implements IHelper
{
    public function text(string $id, ?string $value = null, string $type = 'text', array $options = []): string
    {
        $value = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        $attr  = $this->getAttr($options);
        $errors = $this->getErrorsListById($id);

        $html  = "<input type=\"{$type}\" id=\"{$id}\" name=\"{$id}\" value=\"{$value}\"{$attr} />";
        $html .= $errors;

        return $html;
    }
}