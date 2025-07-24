<?php
namespace Jaca\View\Helper\Tag\Element;

use Jaca\View\Helper\Helper;
use Jaca\View\Helper\Interfaces\IHelper;

class Link extends Helper implements IHelper
{
    public function link(array|string $link, string $text, array $options = []): string
	{
		$attr = $this->getAttr($options);
	
		if (is_array($link)) {
			if (count($link) > 0) {
				$link = implode('/', $link) . '/';
			} else {
				$link = '';
			}
		}
	
		$url = "href=\"{$link}\"";
	
		return "<a {$url}{$attr}>{$text}</a>";
	}
}