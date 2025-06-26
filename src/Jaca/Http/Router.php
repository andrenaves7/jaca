<?php
namespace Jaca\Http;

class Router
{
    private const URI_SEPARATOR = '/';
    private const CB_SEPARATOR = '\\';
    private const CONTROLLER_PATH = 'App';

    private array $delimiters = ['-', '.', '_'];
	private string $module;
	private string $controller;
	private string $action;
	private array $params;

    public function __construct(string $uri)
    {
        $this->parseUri($uri);
    }

    protected function parseUri(string $uri): void
    {
        $uri = trim(parse_url($uri, PHP_URL_PATH), self::URI_SEPARATOR);
        $segments = explode(self::URI_SEPARATOR, $uri);

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

        $this->module = $this->prepareModule($module);
		$this->controller = $this->prepareController($module, $controller);
		$this->action = $this->prepareAction($action);
		$this->params = $this->prepareParams($segments);
    }

	public function getModule(): string
	{
		return $this->module;
	}

    public function getController(): string
    {
        return $this->controller;
    }

    public function getAction(): string
    {
        return $this->action;
    }

	public function getParams(): array
	{
		return $this->params;
	}

    private function isModule($moduleName): bool
	{
		$moduleName = $this->prepareModule($moduleName);
		$dirName = self::CONTROLLER_PATH . self::URI_SEPARATOR . $moduleName;
		
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
		
        $controllerClass  = 'App' . self::CB_SEPARATOR;
		$controllerClass .= ucfirst(strtolower($module)) . self::CB_SEPARATOR;
		$controllerClass .= 'Controllers' . self::CB_SEPARATOR;
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
