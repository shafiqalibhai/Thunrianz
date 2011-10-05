<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/Content';

switch($option) {
	case "archive":
		$toolbar->add_custom_list(_TB_UNARCHIVE,'unarchive');
		$toolbar->add("delete");
		break;
	default:
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
		if ($option!='categories') {
			$toolbar->add("publish");
			$toolbar->add("unpublish");
			if ($option=='items')
				$toolbar->add_custom_list(_TB_ARCHIVE,'archive');
			$toolbar->add_split();
		}
		$toolbar->add("new");
		$toolbar->add("edit");
		$toolbar->add("reorder");
		$toolbar->add("delete");

		break;
	}
}

?>