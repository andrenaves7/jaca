<?php

namespace Jaca\Routing;

class Router
{
    public function get($route, $callback)
    {
        echo "Rota registrada: $route";
    }
}
