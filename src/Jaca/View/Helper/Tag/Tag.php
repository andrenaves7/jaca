<?php
namespace Jaca\View\Helper\Tag;

use Jaca\View\Helper\ActionHelper;

class Tag extends ActionHelper
{
    public function init(): void
    {
        $this->helper = 'Tag';
    }
}