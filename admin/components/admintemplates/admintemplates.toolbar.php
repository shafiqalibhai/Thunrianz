<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/System/Templates_manager';

switch($task) {
	case "new":
		$toolbar->add("install");
		$toolbar->add("cancel");
	break;
	case "info":
		$toolbar->add("back");
	break;
	case "edit": 
		$toolbar->add("save");
		$toolbar->add("cancel");
	break;
	default : 
		$toolbar->add("publish");
		$toolbar->add_split();
		$toolbar->add("new");
		$toolbar->add("edit");
		$toolbar->add("delete");
	break;
}

?>