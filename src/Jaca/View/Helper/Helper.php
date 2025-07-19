<?php
namespace Jaca\View\Helper;

use Jaca\Data\Data;

class Helper
{
    protected Data $data;

    public function __construct(Data $data)
    {
        $this->data = $data;
    }

    public function init(): void
    {}

    protected function getAttr(array $options = []): string
	{
		$attr = '';
		if (count($options) > 0){
			foreach($options as $url => $opt){
				$attr .= " {$url}=\"{$opt}\"";
			}
		}
		
		return $attr;
	}

    public function getValuesById(string $id): string|null
	{
		if (count($this->data->helper->values) > 0) {
			foreach ($this->data->helper->values as $key => $val) {
				if ($key == $id) {
					return $val;
				}
			}
		}
		return '';
	}

    public function getErrorsListById(string $id): string
	{
		$html = '';
		if (count($this->data->helper->errors) > 0) {
			foreach ($this->data->helper->errors as $key => $errors) {
				if($key == $id) {
					$html .= '<ul class="error">';
					foreach ($errors as $error) {
						$html .= "<li>{$error}</li>";
					}
					$html .= '</ul>';
				}
			}
		}
		return $html;
	}
}