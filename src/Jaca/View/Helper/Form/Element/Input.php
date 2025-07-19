<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Helper;
use Jaca\View\Helper\Interfaces\IHelper;

class Input extends Helper implements IHelper
{
    public function input(string $id, ?string $value = null, string $type = 'text', array $options = []): string
    {
        $attr  = $this->getAttr($options);
		$erros = '';
		if ($value === null) {
			$value = $this->getValuesById($id);
		}
		$erros = $this->getErrorsListById($id);
	
		$html  = "<input type=\"{$type}\" id=\"{$id}\" name=\"{$id}\" value=\"{$value}\"{$attr} />";
		$html .= $erros;
	
		return $html;
    }
}