<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/System/Drabots_manager';

switch ($task) {
	case "new":
		$toolbar->add("install");
		$toolbar->add("cancel");
	break;

	case "edit":
		$toolbar->add("save");
		$toolbar->add("cancel");
	break;
	default:
		if ($my->is_admin()) {
			$toolbar->add_custom(_INSTALL, "new");
			$toolbar->add_custom(_UNINSTALL, "delete");
			$toolbar->add_split();
		}
		$toolbar->add("edit");
		$toolbar->add("reorder");
	break;
}

?>