<?php
namespace Jaca\View\Helper\Form\Element;

use Jaca\View\Helper\Helper;
use Jaca\View\Helper\Interfaces\IHelper;

class Submit extends Helper implements IHelper
{
    public function submit(string $id, ?string $value = null, array $options = []): string
	{
		$attr = $this->getAttr($options);
		return "<input type=\"submit\" id=\"{$id}\" value=\"{$value}\"{$attr} />";
	}
}