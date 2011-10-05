<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Patch manager component
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# main features
#

include_once(com_path('html'));

switch ($task) {
	case 'info':
		info_page();
	break;
	case 'install':
		include $d_root.'admin/classes/install.php';
		$install = new Install('patch');
		CMSResponse::Back($install->go());
	break;
	default:
	case 'new':
		include $d_root.'admin/classes/install.php';
		$web_path = in_raw('web_path', $_REQUEST, '');
		if ($web_path!=='')
			$web_path = remote_update($web_path);
		else
			$web_path = 'http://';
		Install::install_interface('admin.php?com_option=patch', _SYSTEM_INSTALL_PATCH, $web_path);
	break;
}

?>