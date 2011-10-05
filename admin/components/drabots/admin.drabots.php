<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

switch($task) {
	case "install" : 
		if (!$my->is_admin()) {
			CMSResponse::BackendUnauthorized();
			break;
		}
		include $d_root.'admin/classes/install.php';
		$install=new Install("drabot");
		CMSResponse::Redir('admin.php?com_option=drabots&option=install',$install->go());	
		break;
	case "delete" :
		if (!$my->is_admin()) {
			CMSResponse::BackendUnauthorized();
			break;
		}
		include $d_root.'admin/classes/uninstall.php';
		$cid = in('cid', __ARR|__PATH, $_REQUEST);
		if (!isset($cid))
			CMSResponse::Redir('admin.php?com_option=drabots', _FORM_NC);
		$uninstall= new UnInstall('drabot');
		foreach($cid as $id) {
			$row = $conn->SelectRow('#__drabots', 'element', ' WHERE id='.$id);
			if (empty($row))
				continue;
			$uninstall->name($row['element']);
			$msg = $uninstall->go();
			if ($msg!=='')
				break;
		}
		CMSResponse::Redir('admin.php?com_option=drabots&option=install', $msg);
		break;
	case "new":
		if (!$my->is_admin()) {
			CMSResponse::BackendUnauthorized();
			break;
		}
		include $d_root.'admin/classes/install.php';
		$web_path = in_raw('web_path', $_REQUEST, '');
		if ($web_path!='')
			$web_path = remote_update($web_path);
		else
			$web_path = 'http://';
		Install::install_interface("admin.php?com_option=drabots&option=install",_DRABOTS_INSTALL, $web_path);
	break;
	case "orderup":
	case "orderdown":
	case "reorder":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		$easydb->data_table($cid, $task, "drabots","admin.php?com_option=drabots&option=manage");
		break;
	case "save":
		$drabot_showon = in('drabot_showon', __ARR|__NUM, $_POST);
		if ($drabot_showon)
			$showon = '_'.implode('_', $drabot_showon).'_';
		else $showon = '';
		//SECURITY HERE PLEASE
		$drabot_params = in_prefix('param_', __RAW, $_POST);
		if (isset($drabot_params))
			$drabot_params = sql_encode(serialize($drabot_params));
		else
			$drabot_params = serialize(array());
//		$drabot_params = '';
//		if (isset($vars))
//			foreach ($vars as $var => $val)
//				$drabot_params.=$var.'='.$val."\n";
		$drabot_access = in_num('drabot_access', $_POST);
		$drabot_id = in_num('drabot_id', $_POST);
		$conn->Update('#__drabots', "showon='$showon', access = $drabot_access,  params = '$drabot_params'",
				" WHERE id = $drabot_id");
		CMSResponse::Redir("admin.php?com_option=drabots&option=manage");
		break;
	case "edit":
		$id = in('cid', __ARR0|__NUM, $_REQUEST);
		if (isset($id))
			drabot_edit($id);
		break;
	case 'massop':
		$easydb->MassOp('drabots','admin.php?com_option=drabots&option=manage');
		break;
	default:
		drabots_manage_table();
		break;
}

?>