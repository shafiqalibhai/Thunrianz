<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/System/Components_manager';

switch($task) {
	case "new":
		$toolbar->add("install");
		$toolbar->add("cancel");
	break;
	default : 
		$toolbar->add_custom(_INSTALL, 'new');
		$toolbar->add_custom(_UNINSTALL, 'delete','',true);
}
?>