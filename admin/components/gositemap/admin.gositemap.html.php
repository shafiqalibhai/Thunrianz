<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function create_sitemap() {
	global $pathway;
	$pathway->add(_SITEMAP_HEAD);
	$gui=new ScriptedUI();
	$gui->add("form","adminform");
	$gui->add("com_header", _SITEMAP_HEAD);
	$gui->add('text', '', _SITEMAP_DESC);
	$gui->add("end_form");
	$gui->generate();
}

?>