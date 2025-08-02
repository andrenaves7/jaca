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

/**
 * Abstract base controller class implementing common functionality
 * for all application controllers.
 *
 * Provides properties and methods to handle requests, routing info,
 * view rendering, and flash messaging.
 *
 * @package Jaca\Controller
 */
abstract class Controller implements IController
{
    /**
     * Shared data container instance.
     *
     * @var Data
     */
    protected Data $data;

    /**
     * HTTP request instance.
     *
     * @var HttpRequest
     */
    protected HttpRequest $request;

    /**
     * Route information for the current request.
     *
     * @var RouteInfo
     */
    protected RouteInfo $routeInfo;

    /**
     * View instance for rendering templates.
     *
     * @var View
     */
    protected View $view;

    /**
     * Flash message manager for temporary messages between requests.
     *
     * @var FlashMessageManager
     */
    protected FlashMessageManager $flashMessage;

    /**
     * Controller constructor.
     *
     * Initializes data container, flash messages, view, and stores
     * request and routing information.
     *
     * @param HttpRequest $request The HTTP request object.
     * @param RouteInfo $routeInfo The route information object.
     * @param FlashHelper $flashHelper Helper for flash messages in views.
     */
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

    /**
     * Initialization hook method.
     *
     * Can be overridden by subclasses for custom setup logic.
     *
     * @return void
     */
    protected function init(): void 
    {}

    /**
     * Returns the current HTTP request.
     *
     * @return HttpRequest
     */
    public function getRequest(): HttpRequest
    {
        return $this->request;
    }

    /**
     * Returns the view instance.
     *
     * @return View
     */
    public function getView(): View
    {
        return $this->view;
    }

    /**
     * Binds a model to the controller's data helper,
     * copying validation errors and model values.
     *
     * @param ModelCore|null $model The model to bind, or null.
     * @return void
     */
    public function bindModel(?ModelCore $model): void
    {
        $this->data->helper->errors = $model->getErrors();
        $this->data->helper->values = $model->toArray();
    }
}
