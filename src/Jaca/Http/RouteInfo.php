<?php
namespace Jaca\Http;

class RouteInfo
{
    public string $module;
    public string $controller;
    public string $controllerName;
    public string $action;
    public string $actionName;
    public array $params;

    public function __construct(string $module, string $controller, 
        string $controllerName, string $action, string $actionName, array $params)
    {
        $this->module = $module;
        $this->controller = $controller;
        $this->controllerName = $controllerName;
        $this->action = $action;
        $this->actionName = $actionName;
        $this->params = $params;
    }
}