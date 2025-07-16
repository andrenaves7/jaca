<?php
namespace Jaca\View\Helper\Form;

use Jaca\View\Helper\ActionHelper;

class Form extends ActionHelper
{
    public function init(): void
    {
        $this->helper = 'Form';
    }
}