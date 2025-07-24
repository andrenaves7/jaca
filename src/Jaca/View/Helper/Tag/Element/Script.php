<?php
namespace Jaca\View\Helper\Tag\Element;

use Jaca\View\Helper\Helper;
use Jaca\View\Helper\Interfaces\IHelper;

class script extends Helper implements IHelper
{
    public function script(string $link): string
	{
		return "<script src=\"{$link}\" type=\"text/javascript\"></script>";
	}
}