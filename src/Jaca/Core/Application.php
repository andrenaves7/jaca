<?php

namespace Jaca\Core;

use Dotenv\Dotenv;
use Jaca\Http\HttpRequest;
use Jaca\Http\RouteDispatcher;
use Jaca\Http\Router;

/**
 * Class Application
 *
 * The main application class responsible for bootstrapping the app,
 * loading environment variables, registering error handling,
 * and dispatching HTTP requests.
 *
 * @package Jaca\Core
 */
class Application
{
    /**
     * The base path of the application (root directory).
     *
     * @var string
     */
    protected string $basePath;

    /**
     * Application constructor.
     *
     * @param string $basePath The base directory path of the application.
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Runs the application.
     *
     * Loads environment variables, registers error handlers,
     * creates the request, router and dispatcher,
     * and dispatches the incoming HTTP request.
     *
     * @return void
     */
    public function run(): void
    {
        $this->loadEnv();
        ErrorHandler::register();

        $request = new HttpRequest();
        $router = new Router($_SERVER['REQUEST_URI']);
        $dispatcher = new RouteDispatcher();

        $dispatcher->dispatch($router, $request);
    }

    /**
     * Loads environment variables from a `.env` file located at the base path.
     *
     * Uses the vlucas/phpdotenv package.
     *
     * @return void
     */
    protected function loadEnv(): void
    {
        $dotenv = Dotenv::createImmutable($this->basePath);
        $dotenv->safeLoad();
    }
}
