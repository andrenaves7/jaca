<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

/**
 * Generates a reset button for HTML forms.
 *
 * This helper builds a <input type="reset"> element with customizable
 * attributes and value.
 *
 * @package Jaca\View\Helper\Form\Element
 */
class Reset extends FormHelper implements IHelper
{
    /**
     * Renders a reset button element.
     *
     * @param string $id The element's ID and name attribute.
     * @param string|null $value The label/text to appear on the button.
     * @param array $options Additional HTML attributes (e.g., class, style).
     * @return string The rendered HTML reset input element.
     */
    public function reset(string $id, string $value = null, array $options = []): string
    {
        $attributes = '';
        foreach ($options as $key => $opt) {
            $escapedKey = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            $escapedVal = htmlspecialchars($opt, ENT_QUOTES, 'UTF-8');
            $attributes .= " {$escapedKey}=\"{$escapedVal}\"";
        }

        $escapedId = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
        $escapedValue = htmlspecialchars($value ?? 'Reset', ENT_QUOTES, 'UTF-8');

        return "<input type=\"reset\" id=\"{$escapedId}\" name=\"{$escapedId}\" value=\"{$escapedValue}\"{$attributes} />";
    }
}
