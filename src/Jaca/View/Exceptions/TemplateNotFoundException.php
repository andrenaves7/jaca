<?php

namespace Jaca\View\Exceptions;

/**
 * Exception thrown when a view template file is not found.
 *
 * @package Jaca\View\Exceptions
 */
class TemplateNotFoundException extends \Exception
{
    /**
     * TemplateNotFoundException constructor.
     *
     * @param string $template The template file path that was not found.
     */
    public function __construct(string $template)
    {
        parent::__construct("Template '$template' not found.", 404);
    }
}
