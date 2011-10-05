<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Component/Forum';

switch($option) {
	case "categories":
	case 'sections':
		switch($task) {
			case "new":
				$toolbar->add("create");
				$toolbar->add("cancel");
			break;
			case "edit":
				$toolbar->add("save");
				$toolbar->add("cancel");
			break;
			default:
				if ($option=='sections') {
					$toolbar->add_custom_list(_FORUM_MANAGE_CATEGORIES, 'categories');
					$toolbar->add_split();
				}
				$toolbar->add("reorder");
				$toolbar->add_split();
				$toolbar->add("new");
				$toolbar->add("edit");
				$toolbar->add("delete");
			break;
		}
	break;

}
?>