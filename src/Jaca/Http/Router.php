<?php
namespace Jaca\Http;

use Jaca\Config\Constants;

/**
 * Class Router
 *
 * Parses a given URI and maps it to a controller, action, and parameters.
 * Supports optional module segmentation and flexible delimiters.
 *
 * @package Jaca\Http
 */
class Router
{
    /**
     * Delimiters used for formatting URI segments into PascalCase or camelCase.
     *
     * @var string[]
     */
    private array $delimiters = ['-', '.', '_'];

    /**
     * Stores the parsed routing information.
     *
     * @var RouteInfo
     */
    private RouteInfo $routeInfo;

    /**
     * Router constructor.
     *
     * @param string $uri The URI to be parsed.
     */
    public function __construct(string $uri)
    {
        $this->parseUri($uri);
    }

    /**
     * Parses the given URI and extracts module, controller, action, and parameters.
     *
     * @param string $uri The URI string to be parsed.
     * @return void
     */
    protected function parseUri(string $uri): void
    {
        $uri = trim(parse_url($uri, PHP_URL_PATH), Constants::URI_SEPARATOR);
        $segments = explode(Constants::URI_SEPARATOR, $uri);

        if (isset($segments[0]) && $segments[0] && $this->isModule($segments[0])) {
            $module = $segments[0] ?? 'def';
            $controller = $segments[1] ?? 'index';
            $action = $segments[2] ?? 'index';

            unset($segments[0], $segments[1], $segments[2]);
        } else {
            $module = null;
            $controller = $segments[0] ?? 'index';
            $action = $segments[1] ?? 'index';

            unset($segments[0], $segments[1]);
        }

        $this->routeInfo = new RouteInfo(
            $this->prepareModule($module),
            $this->prepareController($module, $controller),
            $controller,
            $this->prepareAction($action),
            $action,
            $this->prepareParams($segments)
        );
    }

    /**
     * Returns the routing information object.
     *
     * @return RouteInfo
     */
    public function getRouteInfo(): RouteInfo
    {
        return $this->routeInfo;
    }

    /**
     * Checks whether a given name corresponds to a valid module directory.
     *
     * @param string $moduleName The name of the module.
     * @return bool
     */
    private function isModule($moduleName): bool
    {
        $moduleName = $this->prepareModule($moduleName);
        $dirName = Constants::CONTROLLER_PATH . Constants::URI_SEPARATOR . $moduleName;

        return is_dir($dirName);
    }

    /**
     * Converts the module name into PascalCase.
     *
     * @param string|null $module The original module name from the URI.
     * @return string|null
     */
    private function prepareModule(?string $module): ?string
    {
        if (!$module) {
            return null;
        }

        $module = explode($this->delimiters[0], str_replace($this->delimiters, $this->delimiters[0], $module));

        foreach ($module as $key => $value) {
            $module[$key] = ucfirst($value);
        }

        return implode('', $module);
    }

    /**
     * Constructs the full controller class name based on the module and controller name.
     *
     * @param string|null $module The module name, if any.
     * @param string $controller The controller name from the URI.
     * @return string The fully qualified controller class name.
     */
    private function prepareController(?string $module, string $controller): string
    {
        $controller = explode($this->delimiters[0], str_replace($this->delimiters, $this->delimiters[0], $controller));

        foreach ($controller as $key => $value) {
            $controller[$key] = ucfirst($value);
        }

        $controllerClass  = 'App' . Constants::CB_SEPARATOR;
        if ($module) {
            $controllerClass .= ucfirst(strtolower($module)) . Constants::CB_SEPARATOR;
        }
        $controllerClass .= 'Controllers' . Constants::CB_SEPARATOR;
        $controllerClass .= implode('', $controller) . 'Controller';

        return $controllerClass;
    }

    /**
     * Converts the action name into camelCase format.
     *
     * @param string $action The action name from the URI.
     * @return string The formatted action method name.
     */
    private function prepareAction(string $action): string
    {
        $action = explode($this->delimiters[0], str_replace($this->delimiters, $this->delimiters[0], $action));

        foreach ($action as $key => $value) {
            $action[$key] = ucfirst($value);
        }

        return lcfirst(implode('', $action));
    }

    /**
     * Cleans and normalizes extra parameters from the URI.
     *
     * @param array $params URI segments remaining after module/controller/action.
     * @return array Cleaned parameters with '_null_' converted to null.
     */
    private function prepareParams(array $params): array
    {
        $return = [];
        foreach ($params as $value) {
            if (!empty($value)) {
                $return[] = $value !== '_null_' ? $value : null;
            }
        }

        return $return;
    }
}
