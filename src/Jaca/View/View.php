<?php

namespace Jaca\View;

use Jaca\Config\Config;
use Jaca\Config\Constants;
use Jaca\Data\Data;
use Jaca\Http\HttpRequest;
use Jaca\View\Exceptions\TemplateNotFoundException;
use Jaca\View\Helper\Flash\FlashHelper;
use Jaca\View\Helper\Form\Form;
use Jaca\View\Helper\Tag\Tag;

/**
 * Class View
 *
 * Handles rendering of views and layouts, managing helpers and
 * view parameters, and controlling render flow.
 *
 * @package Jaca\View
 */
class View
{
    /**
     * Whether to render the view template.
     *
     * @var bool
     */
    private bool $renderView = true;

    /**
     * Whether to render the layout template.
     *
     * @var bool
     */
    private bool $renderLayout = true;

    /**
     * Associative array of additional parameters passed to the view.
     *
     * @var array
     */
    private array $params = [];

    /**
     * HTTP request instance.
     *
     * @var HttpRequest
     */
    private HttpRequest $request;

    /**
     * Data container shared with the view.
     *
     * @var Data
     */
    private Data $data;

    /**
     * Form helper instance.
     *
     * @var Form
     */
    public Form $form;

    /**
     * Tag helper instance.
     *
     * @var Tag
     */
    public Tag $tag;

    /**
     * Flash message helper instance.
     *
     * @var FlashHelper
     */
    public FlashHelper $flash;

    /**
     * View constructor.
     *
     * Initializes helpers and sets request and data instances.
     *
     * @param HttpRequest $request The current HTTP request.
     * @param Data $data Shared data container.
     * @param FlashHelper $flashHelper Flash message helper.
     */
    public function __construct(HttpRequest $request, Data $data, FlashHelper $flashHelper)
    {
        $this->request = $request;
        $this->data = $data;

        $this->form = new Form($this->data);
        $this->tag = new Tag($this->data);
        $this->flash = $flashHelper;
    }

    /**
     * Set a parameter to be available in the view.
     *
     * @param string $key Parameter name.
     * @param mixed $val Parameter value.
     * @return void
     */
    public function setParam($key, $val): void
    {
        $this->params[$key] = $val;
    }

    /**
     * Get a parameter value by key, or empty string if not set.
     *
     * @param string $key Parameter name.
     * @return mixed The parameter value or empty string if missing.
     */
    public function getParam($key): mixed
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }
        return '';
    }

    /**
     * Enable rendering of the view template.
     *
     * @return void
     */
    public function setRenderView(): void
    {
        $this->renderView = true;
    }

    /**
     * Disable rendering of the view template.
     *
     * @return void
     */
    public function setNoRenderView(): void
    {
        $this->renderView = false;
    }

    /**
     * Enable rendering of the layout template.
     *
     * @return void
     */
    public function setRenderLayout(): void
    {
        $this->renderLayout = true;
    }

    /**
     * Disable rendering of the layout template.
     *
     * @return void
     */
    public function setNoRenderLayout(): void
    {
        $this->renderLayout = false;
    }

    /**
     * Render the view template file.
     *
     * If $return is true, returns the rendered content as string.
     * Otherwise, outputs directly.
     *
     * @param bool $return Whether to return the content instead of echoing.
     * @param string|null $url Optional override path for the view file.
     * @return string|null Rendered content if $return is true, null otherwise.
     *
     * @throws TemplateNotFoundException if the view file is missing.
     */
    public function renderView($return = false, $url = null)
    {
        $headers = $this->request->getHeaders();

        // Example condition on headers (commented out)
        // if (isset($headers['ACCESS-TOKEN']) && $headers['ACCESS-TOKEN'] == ACCESS_TOKEN
        //    && isset($headers['APP-TOKEN']) && $headers['APP-TOKEN'] == APP_TOKEN) {
        //     header('Content-type:application/json;charset=utf-8');
        // } else {
        if ($this->renderView || $return) {
            $moduleName = $this->modulePath();
            $controllerName = $this->data->routeInfo->controllerName;
            $actionName = $this->data->routeInfo->actionName;

            if (!$url) {
                $fileName = '../' . Constants::APP_PATH . Constants::URI_SEPARATOR . $moduleName .
                    'views' . Constants::URI_SEPARATOR . 'scripts' . Constants::URI_SEPARATOR;
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
        // }
    }

    /**
     * Render the layout template file.
     *
     * If layout rendering is disabled, only renders the view.
     *
     * @return void
     *
     * @throws TemplateNotFoundException if the layout file is missing.
     */
    public function renderLayout(): void
    {
        if ($this->renderLayout) {
            $layoutFile = Config::get('view', 'default_layout');

            if (!$layoutFile) {
                $layoutFile = APP_PATH . Constants::URI_SEPARATOR . $this->modulePath() .
                    'views' . Constants::URI_SEPARATOR . 'layouts' .
                    Constants::URI_SEPARATOR . Constants::LAYOUT_FILE;
            }

            if (file_exists($layoutFile)) {
                echo $this->requireOnce($layoutFile);
            } else {
                http_response_code(404);
                throw new TemplateNotFoundException($layoutFile);
            }
        } else {
            $this->renderView();
        }
    }

    /**
     * Render a specific layout file.
     *
     * @param string $file Filename of the layout to render.
     * @return string|null Rendered content if any.
     */
    public function renderLayoutFile(string $file)
    {
        $layoutFile = Config::get('view', 'default_layout');

        if (!$layoutFile) {
            $layoutFile = APP_PATH . Constants::URI_SEPARATOR . $this->modulePath() .
                'views' . Constants::URI_SEPARATOR . 'layouts' .
                Constants::URI_SEPARATOR . $file;
        } else {
            $layoutFile = $layoutFile . '../' . $file;
        }

        return $this->render($layoutFile);
    }

    /**
     * Includes the PHP file once and returns its output as a string.
     *
     * @param string $file The PHP file path.
     * @return string Output of the included file.
     */
    private function requireOnce($file)
    {
        ob_start();
        require_once $file;

        return ob_get_clean();
    }

    /**
     * Render a PHP file and echo its content.
     *
     * @param string $file Path of the file to render.
     * @return void
     *
     * @throws TemplateNotFoundException if file does not exist.
     */
    private function render($file): void
    {
        if (file_exists($file)) {
            echo $this->requireOnce($file);
        } else {
            http_response_code(404);
            throw new TemplateNotFoundException($file);
        }
    }

    /**
     * Render a script file relative to the current module's views/scripts folder.
     *
     * @param string $file The script filename.
     * @return string|null Rendered content.
     */
    public function renderScriptFile($file)
    {
        $load  = APP_PATH . Constants::URI_SEPARATOR . $this->modulePath() .
            'views' . Constants::URI_SEPARATOR;
        $load .= 'scripts' . Constants::URI_SEPARATOR . $file;

        return $this->render($load);
    }

    /**
     * Return the module path portion for the view files.
     *
     * @return string Module path with trailing separator or empty string.
     */
    private function modulePath(): string
    {
        return ($this->data->routeInfo->module ? $this->data->routeInfo->module . Constants::URI_SEPARATOR : '');
    }
}
