<?php
namespace Jaca\Http\Exceptions;

/**
 * Class ActionNotFoundException
 *
 * Exception thrown when a specified action method is not found in a controller.
 *
 * @package Jaca\Http\Exceptions
 */
class ActionNotFoundException extends \Exception
{
    /**
     * Constructs the exception with a detailed message.
     *
     * @param string $controller The controller class name where the action was expected.
     * @param string $action The action method name that was not found.
     */
    public function __construct(string $controller, string $action)
    {
        parent::__construct("Action '$action' not found in controller '$controller'.", 404);
    }
}
