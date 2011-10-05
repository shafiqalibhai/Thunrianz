<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

if ($task=='test_email') return;

$d__help_context = 'Administration/System/Global_configuration';

if (is__writable($d_root.$d_private.'config.php'))
	$toolbar->add('save');
$toolbar->add("back");

?>