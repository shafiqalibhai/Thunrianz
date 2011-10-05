<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/System/Modules_manager';

switch($option) {
case "install":
	switch($task) {
		case "new":
		$toolbar->add("install");
		$toolbar->add("cancel");
		
		break;

		default : 
		$toolbar->add_custom(_INSTALL,"new");
		$toolbar->add_custom(_UNINSTALL, "delete");
		
		break;
	}break;

case "manage":
	switch($task) {
		case "new":
		if(isset($item_type))$toolbar->add("create");
		else
			$toolbar->add_custom(_NAV_NEXT ,'next', 'javascript:if (document.adminform.mod_id.value==0) alert(\''.js_encode(_IFC_LIST_ERR).'\'); else document.adminform.submit()');
		$toolbar->add("cancel");
		break;

		case "edit":
		$toolbar->add("save");
		$toolbar->add("cancel");
		break;

		default : 
		$toolbar->add("new");
		$toolbar->add("edit");
		$toolbar->add("reorder");
		$toolbar->add("delete");
		break;
	}
}
?>