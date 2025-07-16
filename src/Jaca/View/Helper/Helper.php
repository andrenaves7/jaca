<?php
namespace Jaca\View\Helper;

class Helper
{
    public function __construct()
    {}

    public function init(): void
    {}

    protected function getAttr(array $options = array()): string
	{
		$attr = '';
		if (count($options) > 0){
			foreach($options as $url => $opt){
				$attr .= " {$url}=\"{$opt}\"";
			}
		}
		
		return $attr;
	}
}