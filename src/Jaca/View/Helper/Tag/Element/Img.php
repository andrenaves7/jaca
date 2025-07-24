<?php
namespace Jaca\View\Helper\Tag\Element;

use Jaca\View\Helper\Helper;
use Jaca\View\Helper\Interfaces\IHelper;

class Img extends Helper implements IHelper
{
    public function img(string $src, array $options = []): string
	{
		$attr = $this->getAttr($options);
		return "<img src=\"{$src}\" {$attr} />";
	}
}