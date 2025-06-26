<?php
namespace Jaca\Http;

use Jaca\Config\Constants;

class Router
{
	private array $delimiters = ['-', '.', '_'];
	private RouteInfo $routeInfo;

    public function __construct(string $uri)
    {
        $this->parseUri($uri);
    }

    protected function parseUri(string $uri): void
    {
        $uri = trim(parse_url($uri, PHP_URL_PATH), Constants::URI_SEPARATOR);
        $segments = explode(Constants::URI_SEPARATOR, $uri);

        if (isset($segments[0]) && $segments[0] && $this->isModule($segments[0])) {
            $module = isset($segments[0]) && $segments[0]? $segments[0]: 'def';
			$controller = isset($segments[1]) && $segments[1]? $segments[1]: 'index';
			$action = isset($segments[2]) && $segments[2]? $segments[2]: 'index';
			
			unset($segments[0], $segments[1], $segments[2]);
        } else {
            $module = 'def';
			$controller = isset($segments[0]) && $segments[0]? $segments[0]: 'index';
			$action = isset($segments[1]) && $segments[1]? $segments[1]: 'index';
			
			unset($segments[0], $segments[1]);
        }

		$this->routeInfo = new RouteInfo(
			$this->prepareModule($module),
			$this->prepareController($module, $controller),
			$controller,
			$this->prepareAction($action),
			$action,
			$this->prepareParams($segments));
    }

	public function getRouteInfo(): RouteInfo
	{
		return $this->routeInfo;
	}

    private function isModule($moduleName): bool
	{
		$moduleName = $this->prepareModule($moduleName);
		$dirName = Constants::CONTROLLER_PATH . Constants::URI_SEPARATOR . $moduleName;
		
		if (is_dir($dirName)) {
			return true;
		} else {
			return false;
		}
	}

    private function prepareModule(string $module): string
	{
		$module = explode($this->delimiters[0], str_replace($this->delimiters, $this->delimiters[0], $module));
	
		foreach ($module as $key => $value) {
			$module[$key] = ucfirst($value);
		}
	
		return implode('', $module);
	}

    private function prepareController(string $module, string $controller): string
	{
		$controller = explode($this->delimiters[0], str_replace($this->delimiters, $this->delimiters[0], $controller));
		
		foreach ($controller as $key => $value) {
			$controller[$key] = ucfirst($value);
		}
		
        $controllerClass  = 'App' . Constants::CB_SEPARATOR;
		$controllerClass .= ucfirst(strtolower($module)) . Constants::CB_SEPARATOR;
		$controllerClass .= 'Controllers' . Constants::CB_SEPARATOR;
		$controllerClass .= implode('', $controller) . 'Controller';
		
		return $controllerClass;
	}

    private function prepareAction(string $action): string
	{
		$action = explode($this->delimiters[0], str_replace($this->delimiters, $this->delimiters[0], $action));
	
		foreach ($action as $key => $value) {
			$action[$key] = ucfirst($value);
		}
	
		return lcfirst(implode('', $action)) . 'Action';
	}

    private function prepareParams(array $params): array
	{
		$return = [];
		foreach ($params as $value) {
			if (!empty($value)) {
				if ($value != '_null_') {
					$return[] = $value;
				} else {
					$return[] = null;
				}
			}
		}
	
		return $return;
	}
}
