<?php

namespace Jaca\View;

class View
{
    public function make($template, $data = [])
    {
        echo "Template: $template";
    }
}
