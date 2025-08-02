<?php
namespace Jaca\Http;

use Jaca\Http\Exceptions\ActionNotFoundException;
use Jaca\Http\Exceptions\ControllerNotFoundException;
use Jaca\Support\FlashMessageManager;
use Jaca\Support\SessionManager;
use Jaca\View\Helper\Flash\FlashHelper;

class RouteDispatcher
{
    public function dispatch(Router $router, HttpRequest $request): void
    {
        $controllerClass = $router->getRouteInfo()->controller;
        $action = $router->getRouteInfo()->action;
        $params = $router->getRouteInfo()->params;

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            throw new ControllerNotFoundException($controllerClass);
        }

        $flashMessage = new FlashMessageManager(
            SessionManager::getInstance()
        );

        $flashMessages = new FlashHelper($flashMessage->getAll());

        $controller = new $controllerClass($request, $router->getRouteInfo(), $flashMessages);

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            throw new ActionNotFoundException($controllerClass, $action);
        }

        call_user_func_array([$controller, $action], $params);
        $controller->getView()->renderLayout();
    }
}