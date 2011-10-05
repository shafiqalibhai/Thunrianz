<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Tarball backup component for Lanius CMS
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# Toolbar creation
#

$d__help_context = 'Administration/System/Tarball_backup';

switch ($task) {
	case 'new_tar':
	case 'restore_tar':
		$toolbar->add('back');
	break;
	default:
		$toolbar->add_custom(_TB_CREATE,'new_tar');
		$toolbar->add_custom_list(_UPDATE,"update_tar");
		$toolbar->add_custom_list(_BACKUP_RESTORE,"restore_tar");
		$toolbar->add("delete");
}


?>