<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Helper;
use Jaca\View\Helper\Interfaces\IHelper;

class Start extends Helper implements IHelper
{
    public function start($id, $action = null, $method = 'post', array $options = array()): string
    {
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