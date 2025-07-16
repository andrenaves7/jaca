<?php
namespace Jaca\View\Helper\Exceptions;

class HelperNotFoundException extends \Exception
{
    public function __construct(string $helper)
    {
        parent::__construct("The helper '$helper' is not defined.");
    }
}