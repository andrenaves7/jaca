<?php

namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

/**
 * Class Radio
 *
 * Helper class for generating groups of HTML radio inputs.
 * It respects metadata configuration such as `required` and default values,
 * and integrates with form error rendering.
 *
 * @package Jaca\View\Helper\Form\Element
 */
class Radio extends FormHelper implements IHelper
{
    /**
     * Render a set of radio buttons.
     *
     * @param string $id         The base id and name of the radio group.
     * @param array $elements    The list of radio options [value => label].
     * @param mixed|null $value  The default selected value (optional).
     * @param array $options     Additional HTML attributes (e.g. ['class' => '...']).
     * @return string            The HTML string with the radio inputs and errors if any.
     */
    public function radio(string $id, array $elements = [], $value = null, array $options = []): string
    {
        // Retrieve metadata for the input (e.g., validation rules)
        $metadata = $this->getInputMetadata($id);

        // If metadata marks this field as required, add required to options
        if ($metadata && $metadata->required) {
            $options['required'] = true;
        }

        // Convert additional options to HTML attributes
        $attr = $this->getAttr($options);
        $output = '';
        $currentValue = $this->getValuesById($id);

        // Fallback: use metadata or given default if currentValue is empty
        if ($value === null) {
            $value = $metadata?->value ?? $currentValue;
        }

        // Render each radio option
        $i = 0;
        foreach ($elements as $key => $label) {
            $checked = ($currentValue !== '') 
                ? ($currentValue == $key ? ' CHECKED' : '') 
                : ($value == $key ? ' CHECKED' : '');

            $radioId = "{$id}-{$i}";
            $output .= '<input id="' . $radioId . '" type="radio" name="' . $id . '" value="' . $key . '"' . $checked . $attr . '>';
            $output .= ' <label for="' . $radioId . '"' . $attr . '>' . $label . '</label>&nbsp;';
            $i++;
        }

        // Append any validation errors
        $errors = $this->getErrorsListById($id);

        return $output . $errors;
    }
}
