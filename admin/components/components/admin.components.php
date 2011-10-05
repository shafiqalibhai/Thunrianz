<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

switch($task) {
	case 'massop':
		$easydb->MassOp('components','admin.php?com_option=components', '', 'admin_access');
	break;
	case "delete" :
		if (!$my->is_admin()) {
			CMSResponse::BackendUnauthorized();
			break;
		}
		if (null === ($cid = in('cid', __ARR|__NUM, $_POST)))
			break;
		include_once $d_root.'admin/classes/uninstall.php';
		$uninstall= new UnInstall("component");
		foreach($cid as $id) {
			$row = $conn->SelectRow('#__components', 'option_link', ' WHERE id='.$id);
			$uninstall->name(substr($row['option_link'],4));
			$msg = $uninstall->go();
			if ($msg!=='')
				break;
		}
		CMSResponse::Redir('admin.php?com_option=components', $msg);
		break;
	case "install" : 
		include $d_root.'admin/classes/install.php';
		$install=new Install('component');
		CMSResponse::Redir('admin.php?com_option=components', $install->go());
		break;
	case "uninstall":
		//FIXME
		trigger_error('NOT IMPLEMENTED');
	
		CMSResponse::Redir("admin.php?com_option=components");	break;
	case "new":
		global $d_root;
		include_once $d_root.'admin/classes/install.php';
		$web_path = in_raw('web_path', $_REQUEST, '');
		if (strlen($web_path))
			$web_path = remote_update($web_path);
		else
			$web_path = 'http://';

		Install::install_interface("admin.php?com_option=components&option=install",_COMPONENTS_INSTALL,$web_path);
		break;
	default: components_table(); break;
} 


?>