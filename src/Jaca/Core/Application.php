<?php

namespace Jaca\Core;

use Dotenv\Dotenv;
use Jaca\Http\HttpRequest;
use Jaca\Http\RouteDispatcher;
use Jaca\Http\Router;

class Application
{
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function run(): void
    {
        $this->loadEnv();
        ErrorHandler::register();

        $request = new HttpRequest($_SERVER);
        $router = new Router($_SERVER['REQUEST_URI']);
        $dispatcher = new RouteDispatcher();

        $dispatcher->dispatch($router, $request);
    }

    protected function loadEnv(): void
    {
        $dotenv = Dotenv::createImmutable($this->basePath);
        $dotenv->safeLoad();
    }
}
