<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\Model\Interfaces\IModel;
use Jaca\View\Helper\Form\FormHelper;
use Jaca\View\Helper\Interfaces\IHelper;

class Start extends FormHelper implements IHelper
{
    public function start(string $id, ?IModel $model = null, ?string $action = null, string $method = 'post', array $options = []): string
    {
		FormHelper::setModel($model);
		
        $attr = $this->getAttr($options);
		
		if (is_array($action)) {
			if (count($action) > 0) {
				$action = implode('/', $action);
			} else {
				$action = '';
			}
		}
		return "<form id=\"{$id}\" action=\"{$action}\" method=\"{$method}\"{$attr} >";
    }
}