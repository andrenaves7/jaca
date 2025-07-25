<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

class Input extends FormHelper implements IHelper
{
    public function input(string $id, ?string $value = null, string $type = 'text', array $options = []): string
    {
        $metadata = $this->getInputMetadata($id);

        if ($metadata) {
            if ($metadata->maxlength !== null) {
                $options['maxlength'] = $metadata->maxlength;
            }

            if ($metadata->required) {
                $options['required'] = true;
            }
        }

        if ($value === null) {
            $value = $metadata?->value ?? $this->getValuesById($id);
        }

        $value = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        $attr  = $this->getAttr($options);
        $errors = $this->getErrorsListById($id);

        $html  = "<input type=\"{$type}\" id=\"{$id}\" name=\"{$id}\" value=\"{$value}\"{$attr} />";
        $html .= $errors;

        $label = $this->getLabel($id, $metadata->label);

        return $label . $html;
    }
}