<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Helper;
use Jaca\View\Helper\Interfaces\IHelper;

class End extends Helper implements IHelper
{
    public function end(): string
    {
        return '</form>';
    }
}