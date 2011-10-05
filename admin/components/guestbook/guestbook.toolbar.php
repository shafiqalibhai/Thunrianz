<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Component/Guestbook';

switch($task) {
	case "edit":
		$toolbar->add("save");
		$toolbar->add("cancel");
	break;

	default : 
		$toolbar->add("edit");
		$toolbar->add("delete");
	break;
}
?>