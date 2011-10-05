<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/Packages';

switch($task) {
	default:
	case "new":
		$toolbar->add("install");
		$toolbar->add("cancel");
	break;
	case "info": 
		$toolbar->add("cancel");
	break;
}

?>