<?php
namespace Jaca\Http\Exceptions;

/**
 * Class ControllerNotFoundException
 *
 * Exception thrown when a specified controller class is not found.
 *
 * @package Jaca\Http\Exceptions
 */
class ControllerNotFoundException extends \Exception
{
    /**
     * Constructs the exception with a detailed message.
     *
     * @param string $message The exception message, typically including controller name.
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
