<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Component/Event';

switch($task) {
	case "new":
		$toolbar->add("create");
		$toolbar->add("cancel");
	break;
	case "edit":
		$toolbar->add("save");
		$toolbar->add("cancel");
	break;
	default : 
		$toolbar->add("publish");
		$toolbar->add("unpublish");
		$toolbar->add_split();
		$toolbar->add("new");
		$toolbar->add("edit");
		$toolbar->add("delete");
	break;
}
?>