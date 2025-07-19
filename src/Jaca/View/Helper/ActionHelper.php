<?php
namespace Jaca\View\Helper;

use Jaca\Config\Config;
use Jaca\Data\Data;
use Jaca\View\Helper\Exceptions\HelperNotFoundException;

/**
 * Class ActionHelper
 *
 * Base class for dynamic helper resolution in views.
 * 
 * This class allows the invocation of custom or internal helpers by method name.
 * It follows the order:
 *  1. Internal Jaca helper
 *  2. App-level helper
 *  3. Custom registered helper (from config/helpers.php)
 *
 * @package Jaca\View\Helper
 */
class ActionHelper
{
    /**
     * The data values
     * 
     * @var Data
     */
    protected Data $data;

    /**
     * The helper group name (used to resolve internal helpers).
     *
     * Example: if $helper is "Form", the internal class searched will be
     * Jaca\View\Helper\Form\Element\MethodName
     *
     * @var string|null
     */
    public ?string $helper = null;

    /**
     * Constructor. Calls the init() method.
     */
    public function __construct(Data $data)
    {
        $this->data = $data;

        $this->init();
    }

    /**
     * Method to be optionally overridden by child helpers.
     * Called automatically after construction.
     *
     * @return void
     */
    public function init(): void
    {}

    /**
     * Magic method used to dynamically resolve and call helper classes and methods.
     *
     * @param string $method The method name being called.
     * @param array $args The arguments passed to the method.
     * @return mixed The result of the helper method call.
     * 
     * @throws HelperNotFoundException If no valid helper is found or method is not defined.
     */
    public function __call(string $method, array $args)
    {
        $methodUc = ucfirst($method);
        
        $gynClass = "Jaca\\View\\Helper\\{$this->helper}\\Element\\{$methodUc}";
        $appClass = "App\\Helpers\\{$methodUc}Helper";

        $customHelpers = Config::get('helpers', 'helpers');
        $customClass = $customHelpers[$methodUc] ?? $appClass;

        $candidates = [$gynClass, $appClass];

        if ($customClass && !in_array($customClass, $candidates)) {
            $candidates[] = $customClass;
        }

        $instance = null;
        foreach ($candidates as $className) {
            if (class_exists($className)) {
                try {
                    $instance = new $className($this->data);
                    break;
                } catch (\Throwable $e) {
                    // Optionally log or handle the constructor failure here
                    continue;
                }
            }
        }

        if (!$instance) {
            $tried = implode(', ', $candidates);
            throw new HelperNotFoundException("No helper class found for '{$method}'. Tried: {$tried}");
        }

        $instance->init();

        if (!method_exists($instance, $method)) {
            throw new HelperNotFoundException("Method '{$method}' not found in class " . get_class($instance));
        }

        return call_user_func_array([$instance, $method], $args);
    }
}
