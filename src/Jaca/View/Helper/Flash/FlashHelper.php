<?php
namespace Jaca\View\Helper\Flash;

class FlashHelper {
    private array $messages;

    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    public function show(): string
    {
        $msg = '';
        foreach ($this->messages as $key => $val) {
            $class = ltrim(strstr($key, '.', false), '.');
            $msg .= '<div class="flash-message ' . $class . '">' . $val . '</div>';
        }

        return $msg;
    }
}
