<?php

namespace Jaca\Http\Exceptions;

class ControllerNotFoundException extends \Exception
{
    public function __construct(string $controller)
    {
        parent::__construct("Controller '$controller' not found.", 404);
    }
}
