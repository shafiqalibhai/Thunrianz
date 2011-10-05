<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

switch($option) {
	case "install":
		if (!$my->is_admin()) {
			CMSResponse::BackendUnauthorized();
			break;
		}
	switch($task) {
		case "delete" :
			include $d_root.'admin/classes/uninstall.php';
			$cid = in('cid', __ARR|__PATH, $_REQUEST);
			if (!isset($cid))
				CMSResponse::Back(_FORM_NC);
			$uninstall= new UnInstall("module");
			foreach($cid as $id) {
				$uninstall->name($id);
				$msg = $uninstall->go();
				if ($msg!=='')
					break;
			}
			CMSResponse::Redir('admin.php?com_option=modules&option=install', $msg);
			break;
		case "new":
			include $d_root.'admin/classes/install.php';
			$web_path = in_raw('web_path', $_REQUEST, '');
			if ($web_path!='')
				$web_path = remote_update($web_path);
			else
				$web_path = 'http://';

			Install::install_interface("admin.php?com_option=modules&option=install",_MODULES_INSTALL, $web_path);
			break;
		case 'install':
			include $d_root.'admin/classes/install.php';
			$install=new Install("module");
			CMSResponse::Redir('admin.php?com_option=modules&option=manage', $install->go());
			break;
		default:
			modules_table(); break;
	}
	break;

	case "manage":
		switch($task) {
		case 'massop':
			$easydb->MassOp('modules','admin.php?com_option=modules&option=manage');
			break;
		case "orderup":
		case "orderdown":
		case "reorder":
		case "publish":
		case "unpublish":
			$cid = in_arr('cid', __NUM, $_POST);
			$easydb->data_table($cid, $task, 'modules', "admin.php?com_option=modules&option=manage");
			break;
		case "delete":
			$cid = in_arr('cid', __NUM, $_POST);
			$easydb->delete_np("modules",$cid,"iscore='2'");
			CMSResponse::Redir("admin.php?com_option=modules&option=manage");
			break;
		case "save":
				$showon="";
				$module_showon = in('module_showon', __ARR|__NUM, $_POST, array());
				// skip the default setting
				if (count($module_showon) && !(($module_showon[0]===0) && count($module_showon==1))) {
					foreach($module_showon as $link) {
						$showon.="_".$link."_";
					}
				}
/*				$module_params='';
				$vars = in_prefix('param_', __RAW, $_POST);
				if (isset($vars))
					foreach ($vars as $var => $val)
						$module_params.=$var.'='.$val."\n";*/
				//SECURITY HERE PLEASE
				$module_params = in_prefix('param_', __RAW, $_POST);
//				$module_position = in_sql('module_position', $_POST);
				if (isset($module_params))
					$module_params = sql_encode(serialize($module_params));
				else
					$module_params = serialize(array());
				
				$menu_instance = in_num('menu_instance', $_POST, 0);

			$module_title = in_sql('module_title', $_POST);
			$module_position = in_sql('module_position', $_POST);
			$module_access = in_num('module_access', $_POST);
			$module_showtitle = in_num('module_showtitle', $_POST);
			$module_id = in_num('module_id', $_POST);
			
				$conn->Update('#__modules',  "title='$module_title', position = '$module_position' , access = $module_access , showtitle= $module_showtitle, showon='$showon',params = '$module_params',instance=$menu_instance", " WHERE id = $module_id");
				$table_page = in_num('table_page', $_REQUEST, 1);
				CMSResponse::Redir("admin.php?com_option=modules&option=manage&table_page=".$table_page);
				break;
		case "new_mod":
			$mod_id = in_num('mod_id', $_POST);
			if (!isset($mod_id))
				CMSResponse::Back(_FORM_NC);
			$rsar=$conn->SelectRow('#__modules', 'title,module', " WHERE id=$mod_id");
			$order=$easydb->neworder("modules");
			$conn->Insert('#__modules', '(title,ordering,position,module,iscore)',
						"'".sql_encode($rsar['title'])." ("."new Instance".")',$order,'left','".$rsar['module']."',2");
			CMSResponse::Redir('admin.php?com_option=modules&option=manage&task=edit&cid[]='.$conn->Insert_ID());
			break;
		case "new":
			new_mod();
			break;
		case "edit":
			$mod_id = in('cid', __ARR0|__NUM, $_REQUEST);
			module_edit($mod_id);
			break;
		default:
			modules_manage_table();
			break;
		} break;

}

?>