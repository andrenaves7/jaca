<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

class End extends FormHelper implements IHelper
{
    public function end(): string
    {
        FormHelper::setModel(null);
        return '</form>';
    }
}