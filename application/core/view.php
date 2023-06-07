<?php
class View
{
	function generate($template_view, $content_view, $content_css, $pageName)
	{
		include 'application/views/'.$template_view;
	}
}