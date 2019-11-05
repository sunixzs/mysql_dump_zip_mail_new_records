<?php

if (!defined('MODE') || MODE != 'INDEX') {
    die('Direct access denied!');
}

class Logger
{
    /**
     * @param string|array $content
     */
    public static function log($content)
    {
        if (is_array($content)) {
            $content = implode("\n", $content);
        }

        echo '<div style="border-top: 1px solid black; padding: .5em; background: white, color: grey;">' . nl2br(htmlspecialchars($content)) . '</div>';
        flush();
        ob_flush();
    }
}
