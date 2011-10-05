<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/System/Users_manager';

switch($task) {
	case "new":
		$toolbar->add("create");
		$toolbar->add("cancel");
	break;
	case "edit":
		$toolbar->add("save");
		$toolbar->add("cancel");
	break;
	case 'create':
		$toolbar->add('back');
	break;
	default : 
		$toolbar->add_custom(_USER_ACTIVATE, 'publish');
		$toolbar->add_custom(_USER_DEACTIVATE, 'unpublish');
		$toolbar->add_split();
		$toolbar->add("new");
		$toolbar->add("edit");
		$toolbar->add("delete");
	break;
}
?>