<?php

namespace Jaca\Controller;

use Jaca\Http\HttpRequest;
use Jaca\Http\RouteInfo;
use Jaca\View\View;

abstract class Controller
{
    protected HttpRequest $request;

    protected RouteInfo $routeInfo;

    protected View $view;

    public function __construct(HttpRequest $request, RouteInfo $routeInfo)
	{
		$this->request = $request;
        $this->routeInfo = $routeInfo;
        $this->view = new View($request, $routeInfo);
		
		$this->init();
	}

    protected function init(): void 
    {}

    public function getRequest(): HttpRequest
    {
        return $this->request;
    }

    public function getView(): View
    {
        return $this->view;
    }
}