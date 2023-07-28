<?php

namespace Aladser\Core;

class View
{
    public function generate($template_view, $content_view, $content_css, $content_js, $pageName, $data = null)
    {
        require_once(
            dirname(__DIR__, 1)
            . DIRECTORY_SEPARATOR
            . 'views'
            . DIRECTORY_SEPARATOR
            . $template_view
        );
    }
}
