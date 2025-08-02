<?php
namespace Jaca\Http;

/**
 * Class RouteInfo
 *
 * Holds routing information resolved from the URI, including module, controller, action, and parameters.
 * Used to guide request dispatching and view rendering.
 *
 * @package Jaca\Http
 */
class RouteInfo
{
    /**
     * @var string|null The module name, if defined.
     */
    public ?string $module;

    /**
     * @var string The fully qualified controller class name.
     */
    public string $controller;

    /**
     * @var string The short name of the controller, used for referencing in views or logs.
     */
    public string $controllerName;

    /**
     * @var string The name of the controller method to be executed.
     */
    public string $action;

    /**
     * @var string A friendly name for the action, often used in views or breadcrumbs.
     */
    public string $actionName;

    /**
     * @var array The list of parameters to be passed to the controller action.
     */
    public array $params;

    /**
     * RouteInfo constructor.
     *
     * @param string|null $module The module name, if any.
     * @param string $controller The full controller class name.
     * @param string $controllerName A user-friendly controller name.
     * @param string $action The controller method to call.
     * @param string $actionName A user-friendly action name.
     * @param array $params Parameters extracted from the route.
     */
    public function __construct(
        ?string $module,
        string $controller, 
        string $controllerName, 
        string $action, 
        string $actionName, 
        array $params
    ) {
        $this->module = $module;
        $this->controller = $controller;
        $this->controllerName = $controllerName;
        $this->action = $action;
        $this->actionName = $actionName;
        $this->params = $params;
    }
}
