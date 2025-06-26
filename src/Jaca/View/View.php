<?php

namespace Jaca\View;

use Jaca\Config\Config;
use Jaca\Config\Constants;
use Jaca\Http\HttpRequest;
use Jaca\Http\RouteInfo;
use Jaca\View\Exceptions\TemplateNotFoundException;

class View
{
    private bool $renderView = true;
    private bool $renderLayout = true;
    private array $params;
    private HttpRequest $request;
    private RouteInfo $routeInfo;

    public function __construct(HttpRequest $request, RouteInfo $routeInfo)
    {
        $this->request = $request;
        $this->routeInfo = $routeInfo;
    }

    public function setParam($key, $val): void
	{
		$this->params[$key] = $val;
	}

	public function getParam($key): mixed
	{
		if (isset($this->params[$key])) {
			return $this->params[$key];
		}
		return '';
	}

    public function setRenderView(): void
	{
		$this->renderView = true;
	}
	
	public function setNoRenderView(): void
	{
		$this->renderView = false;
	}
	
	public function setRenderLayout(): void
	{
		$this->renderLayout = true;
	}
	
	public function setNoRenderLayout(): void
	{
		$this->renderLayout = false;
	}

    public function renderView($return = false, $url = null)
	{
	    $headers = $this->request->getHeaders();
		//if(isset($headers['ACCESS-TOKEN']) && $headers['ACCESS-TOKEN'] == ACCESS_TOKEN && isset($headers['APP-TOKEN']) && $headers['APP-TOKEN'] == APP_TOKEN) {
		if(isset($headers['ACCESS-TOKEN']) && isset($headers['APP-TOKEN'])) {
	        //header('Content-type:application/json;charset=utf-8');
	    } else {
    		if ($this->renderView || $return) {
    			$moduleName = $this->routeInfo->module;
    			$controllerName = $this->routeInfo->controllerName;
    			$actionName = $this->routeInfo->actionName;
    			if (!$url) {
    				$fileName = '../' . Constants::APP_PATH . Constants::URI_SEPARATOR . $moduleName . 
                        Constants::URI_SEPARATOR . 'views' . 
                        Constants::URI_SEPARATOR . 'scripts' . Constants::URI_SEPARATOR;
    				$fileName .= $controllerName . Constants::URI_SEPARATOR . $actionName . '.phtml';
    			} else {
    				$fileName = __FILE__ . Constants::URI_SEPARATOR . $url;
    			}
    			
    			if (file_exists($fileName)) {
    				$res = $this->requireOnce($fileName);
    				if ($return) {
    					return $res;
    				} else {
    					echo $res;
    				}
    			} else {
    				http_response_code(404);
                    throw new TemplateNotFoundException($fileName);
    			}
    		}
	    }
	}

    public function renderLayout(): void
	{
		if($this->renderLayout) {
			$layoutFile = Config::get('view', 'default_layout');

            if (!$layoutFile) {
                $layoutFile = '../' . Constants::APP_PATH . Constants::URI_SEPARATOR . $this->routeInfo->module .
                    Constants::URI_SEPARATOR . 'views' . Constants::URI_SEPARATOR . 'layouts' . 
                    Constants::URI_SEPARATOR . Constants::LAYOUT_FILE;
            }
			
			if(file_exists($layoutFile)) {
				echo $this->requireOnce($layoutFile);
			} else {
				http_response_code(404);
                throw new TemplateNotFoundException($layoutFile);
			}
		} else {
			$this->renderView();
		}
	}

    public function renderLayoutFile(string $file)
	{
		$layoutFile = Config::get('view', 'default_layout');
		if (!$layoutFile) {
            $layoutFile = '../' . Constants::APP_PATH . Constants::URI_SEPARATOR . $this->routeInfo->module .
                Constants::URI_SEPARATOR . 'views' . Constants::URI_SEPARATOR . 'layouts' . 
                Constants::URI_SEPARATOR . $file;
        } else {
            $layoutFile = $layoutFile . '../' . $file;
        }
		
		return $this->render($layoutFile);
	}

    private function requireOnce($file)
	{
		ob_start();
		require_once $file;
		
		return ob_get_clean();
	}

    private function render($file): void
	{
		if (file_exists($file)) {
			echo $this->requireOnce($file);
		} else {
            http_response_code(404);
            throw new TemplateNotFoundException($file);
        }
	}
}
