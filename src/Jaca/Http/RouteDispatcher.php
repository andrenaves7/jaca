<?php
namespace Jaca\Http;

use Jaca\Http\Exceptions\ActionNotFoundException;
use Jaca\Http\Exceptions\ControllerNotFoundException;
use Jaca\Support\FlashMessageManager;
use Jaca\Support\SessionManager;
use Jaca\View\Helper\Flash\FlashHelper;

/**
 * Class RouteDispatcher
 *
 * Responsible for dispatching the HTTP request to the appropriate controller and action,
 * handling route resolution and injecting necessary dependencies like request and flash messages.
 *
 * @package Jaca\Http
 */
class RouteDispatcher
{
    /**
     * Dispatches the current route by instantiating the corresponding controller and invoking the action.
     *
     * If the controller or action does not exist, it throws a 404 exception.
     *
     * @param Router $router The router instance with resolved route information.
     * @param HttpRequest $request The current HTTP request object.
     * @return void
     *
     * @throws ControllerNotFoundException If the specified controller class does not exist.
     * @throws ActionNotFoundException If the specified action method does not exist in the controller.
     */
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
