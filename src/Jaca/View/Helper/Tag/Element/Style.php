<?php
namespace Jaca\View\Helper\Tag\Element;

use Jaca\View\Helper\Helper;
use Jaca\View\Helper\Interfaces\IHelper;

class Style extends Helper implements IHelper
{
    public function style(string $link): string
	{
		return "<link rel=\"stylesheet\" href=\"{$link}\" />";
	}
}