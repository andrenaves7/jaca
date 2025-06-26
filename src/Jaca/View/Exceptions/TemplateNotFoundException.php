<?php

namespace Jaca\View\Exceptions;

class TemplateNotFoundException extends \Exception
{
    public function __construct(string $template)
    {
        parent::__construct("Template '$template' not found.", 404);
    }
}
