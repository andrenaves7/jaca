<?php
namespace Jaca\View\Helper\Flash;

/**
 * Class FlashHelper
 *
 * Responsible for rendering flash messages as HTML blocks.
 * Each message is wrapped in a <div> with a CSS class based on its key.
 *
 * Example:
 *   'flash.success' => 'Saved successfully'
 *   Will render:
 *   <div class="flash-message success">Saved successfully</div>
 *
 * @package Jaca\View\Helper\Flash
 */
class FlashHelper {
    /**
     * Array of flash messages to render.
     * Keys typically include a namespace prefix (e.g., '_flash.success').
     *
     * @var array<string, mixed>
     */
    private array $messages;

    /**
     * FlashHelper constructor.
     *
     * @param array<string, mixed> $messages Associative array of flash messages.
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
    }

    /**
     * Renders the flash messages as HTML.
     *
     * Extracts the CSS class from the message key by removing everything before the first dot.
     *
     * @return string Rendered HTML containing all flash messages.
     */
    public function show(): string
    {
        $msg = '';
        foreach ($this->messages as $key => $val) {
            // Get everything after the first dot to use as the CSS class
            $class = ltrim(strstr($key, '.', false), '.');
            $msg .= '<div class="flash-message ' . htmlspecialchars($class) . '">' . htmlspecialchars((string)$val) . '</div>';
        }

        return $msg;
    }
}
