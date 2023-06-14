<?php
class View
{
	// $content_view, $content_css, $pageName используются в $template_view
	function generate($template_view, $content_view, $content_css, $content_js, $pageName, $data=null)
	{
		include 'application/views/'.$template_view;
	}
}