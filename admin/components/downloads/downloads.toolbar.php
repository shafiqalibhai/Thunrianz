<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Component/Downloads';

switch($task) {
	case "new":
		$toolbar->add("create");
		$toolbar->add("cancel");
	break;
	case "edit":
		$toolbar->add("save");
		$toolbar->add("cancel");
		if ($option=='items')
			$toolbar->add_custom(_DELETE, 'itemdelete');
	break;
	default : 
		if ($option=='items') {
			$toolbar->add("publish");
			$toolbar->add("unpublish");
			$toolbar->add_split();
		}
		$toolbar->add("new");
		$toolbar->add("edit");
		$toolbar->add("reorder");
		if ($option == 'items')
			$toolbar->add("delete");
		else
			$toolbar->add_custom(_DELETE, 'delete', 'if (confirm(\''.js_enc(_DOWNLOADS_DELETE_CATEGORY).'\')) ui_lcms_st(\'delete\');');
	break;
}
?>