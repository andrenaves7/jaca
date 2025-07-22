<?php
namespace Jaca\View\Helper\Exceptions;

class ViewHelperMissingParameterException extends \Exception
{
    public function __construct(string $param)
    {
        parent::__construct("The param '$param' is required.");
    }
}