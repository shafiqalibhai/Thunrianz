<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Subsites component for Lanius CMS
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# toolbar generation script
#

$d__help_context = 'Administration/System/Subsites';

switch($task) {
	case 'new':
		$toolbar->add_custom(_TB_CREATE, 'create');
		$toolbar->add("cancel");
	break;
	case 'edit':
		$toolbar->add("save");
	case 'redir':
	case 'create':
		$toolbar->add_custom(_TB_BACK, "cancel", "document.location='admin.php?com_option=subsites'");
	break;
	case 'update':
		$toolbar->add("back");
	break;
	default:
		$toolbar->add("publish");
		$toolbar->add("unpublish");
		$toolbar->add_split();
		$toolbar->add_custom_list(_UPDATE, "update", "if (".
				"confirm('".js_enc(_SUBSITES_UPDATE_CONFIRM)."')) ui_lcms_st('update')");
		$toolbar->add("new");
		$toolbar->add("edit");
		$toolbar->add("delete");
	break;
}

?>