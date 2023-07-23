<?php
namespace core;

class View
{
    public function generate($template_view, $content_view, $content_css, $content_js, $pageName, $data = null)
    {
        include "application/views/$template_view";
    }
}
