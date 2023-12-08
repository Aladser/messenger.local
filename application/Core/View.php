<?php

namespace Aladser\Core;

use function Aladser\config;

class View
{
    public function generate($template_view, $content_view, $content_css, $content_js, $pageName, $data = null): void
    {
        $app_name = config('APP_NAME');

        require_once dirname(__DIR__, 1)
            .DIRECTORY_SEPARATOR
            .'views'
            .DIRECTORY_SEPARATOR
            .$template_view;
    }
}
