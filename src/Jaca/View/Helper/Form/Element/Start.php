<?php

namespace Jaca\View\Helper\Form\Element;

use Jaca\Model\Interfaces\IModel;
use Jaca\Support\Str;
use Jaca\View\Helper\Exceptions\ViewHelperMissingParameterException;
use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

/**
 * Class Start
 *
 * This class is responsible for generating the opening <form> tag in a view.
 * It supports model-based form generation and can auto-generate the form ID
 * based on the model class name if one is not provided explicitly.
 *
 * Implements IHelper interface to be used as a view helper.
 *
 * Example usage in a view:
 *     <?= (new Start())->start($model, null, '/submit', 'post', ['class' => 'form-horizontal']) ?>
 *
 * @package Jaca\View\Helper\Form\Element
 */
class Start extends FormHelper implements IHelper
{
    /**
     * Generates the opening <form> tag with the appropriate attributes.
     *
     * @param IModel|null $model   Optional model to bind to the form. Used to auto-generate form ID if not set.
     * @param string|null $id      Optional ID for the form element. If omitted and a model is provided, a default ID is generated.
     * @param string|array|null $action The form action URL. Can be a string or an array of path segments.
     * @param string $method       HTTP method to use in the form (e.g., 'post', 'get').
     * @param array $options       Optional additional HTML attributes as key-value pairs.
     *
     * @return string              The generated opening <form> tag.
     *
     * @throws ViewHelperMissingParameterException if no ID is provided and none can be generated.
     */
    public function start(?IModel $model = null, ?string $id = null, string|array|null $action = null, string $method = 'post', array $options = []): string
    {
        self::setModel($model);

        // Auto-generate form ID based on the model class name if not explicitly provided
        if ($model && !$id) {
            $id = str_replace('\\', '_', Str::snakeCase($model::class));
        }

        // If still no ID, throw an exception to ensure form ID uniqueness and correctness
        if (!$id) {
            throw new ViewHelperMissingParameterException('id');
        }

        // Generate additional attributes for the form tag
        $attr = $this->getAttr($options);

        // If the action is an array, convert it to a path string
        if (is_array($action)) {
            $action = count($action) > 0 ? implode('/', $action) : '';
        }

        return "<form id=\"{$id}\" action=\"{$action}\" method=\"{$method}\"{$attr} >";
    }
}
