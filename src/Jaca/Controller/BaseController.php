<?php

namespace Jaca\Controller;

class BaseController
{
    public function render($view)
    {
        echo "Renderizando: $view";
    }
}
