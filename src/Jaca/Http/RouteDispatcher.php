<?php
namespace Jaca\Http;

use Jaca\Http\Exceptions\ActionNotFoundException;
use Jaca\Http\Exceptions\ControllerNotFoundException;

class RouteDispatcher
{
    public function dispatch(Router $router, HttpRequest $request): void
    {
        $controllerClass = $router->getController();
        $action = $router->getAction();
        $params = $router->getParams();

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            throw new ControllerNotFoundException($controllerClass);
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            throw new ActionNotFoundException($controllerClass, $action);
        }

        $reflection = new \ReflectionMethod($controllerClass, $action);
        $injectRequest = $reflection->getNumberOfParameters() > 0 &&
                         ($reflection->getParameters()[0]->getType()?->getName() ?? '') === HttpRequest::class;

        $args = $injectRequest ? array_merge([$request], $params) : $params;

        call_user_func_array([$controller, $action], $args);
    }
}