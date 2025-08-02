<?php

namespace Jaca\Controller;

use Jaca\Controller\Interfaces\IController;
use Jaca\Data\Data;
use Jaca\Http\HttpRequest;
use Jaca\Http\RouteInfo;
use Jaca\Model\ModelCore;
use Jaca\Support\FlashMessageManager;
use Jaca\Support\SessionManager;
use Jaca\View\Helper\DataHelper;
use Jaca\View\Helper\Flash\FlashHelper;
use Jaca\View\View;

abstract class Controller implements IController
{
    protected Data $data;
    protected HttpRequest $request;
    protected RouteInfo $routeInfo;
    protected View $view;
    protected FlashMessageManager $flashMessage;

    public function __construct(HttpRequest $request, RouteInfo $routeInfo, FlashHelper $flashHelper)
	{
        $this->data = new Data();
        $this->data->helper = new DataHelper();
        $this->data->routeInfo = $routeInfo;

        $this->flashMessage = new FlashMessageManager(
            SessionManager::getInstance()
        );

		$this->request = $request;
        $this->routeInfo = $routeInfo;
        $this->view = new View($request, $this->data, $flashHelper);
		
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

    public function bindModel(?ModelCore $model): void
    {
        $this->data->helper->errors = $model->getErrors();
		$this->data->helper->values = $model->toArray();
    }
}