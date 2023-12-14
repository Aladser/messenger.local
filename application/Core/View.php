<?php

namespace App\Core;

use function App\config;

class View
{
    public function generate(
        $page_name,
        $template_view,
        $content_view,
        $head = null,
        $content_css = null,
        $js_script_list = null,
        $data = null
    ): void {
        $app_name = config('APP_NAME');

        require_once dirname(__DIR__, 1)
            .DIRECTORY_SEPARATOR
            .'views'
            .DIRECTORY_SEPARATOR
            .$template_view;
    }
}
