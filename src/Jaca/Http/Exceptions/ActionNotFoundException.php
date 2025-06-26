<?php

namespace Jaca\Http\Exceptions;

class ActionNotFoundException extends \Exception
{
    public function __construct(string $controller, string $action)
    {
        parent::__construct("Action '$action' not found in controller '$controller'.", 404);
    }
}
