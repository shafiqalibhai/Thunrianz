<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include_once(com_path("html"));

if ($option == 'version') {
	version_page();
	return;
}

function insttool_do($action) {
	global $d_root, $conn, $d_db;
	require $d_root.'install/install_lib.php';
	switch ($action) {
		case 'REINSTALL':
			$apwdh = admin_pwd();
			install_cms($conn, $d_db, true);
			admin_insert($conn, $apwdh);
			echo "Current admin user record re-inserted";
			break;
		case 'DROPALL':
			require $d_root.'admin/classes/fs.php';
			global $d_private;
			$fs = new FS();
			$fs->remove($d_root.$d_private.'config.php');
			install_sql($conn, $d_root.'admin/includes/database.sql.php', null, true);
			echo 'All tables dropped and config.php deleted';
			break;
		case 'REMALL':
			require $d_root.'admin/classes/fs.php';
			$fs = new FS();
			$fs->purge($d_root);
			echo 'All files and directories under '.fix_root_path($d_root).' have been deleted';
			// bye bye cruel world
			exit();
			break;
		case 'UNINSTALL':
			install_sql($conn, $d_root.'admin/includes/database.sql.php', null, true);
			require $d_root.'admin/classes/fs.php';
			$fs = new FS();
			$fs->purge($d_root);
			echo 'All tables dropped and all files/directories under '.fix_root_path($d_root).' deleted';
			exit();
			break;
		default:
			CMSResponse::Back(_FORM_NC);
			return;
	}
	global $pathway;
	$pathway->add($action);
}

function &prospect_updates_xml() {
	global $d_root, $d_private;
	$updates = (string)@file_get_contents($d_root.$d_private.'cache/updates.xml');
	if (!strlen($updates))
		$xml=null;
	else {
		$xml = new AnyXML();
		if (!@$xml->fromString($updates)) {
			@unlink($d_root.$d_private.'cache/updates.xml');
			$xml = null;
		}
	}
	return $xml;
}

switch ( $option ) {
case 'insttool':
	if (!$my->is_admin()) {
		CMSResponse::BackendUnauthorized();
		return;
	}
	switch ($task) {
		default:
			insttool_form();
		break;
		case 'ask_confirm':
			$action = in_raw('insttool_action', $_POST, '', 20);
			if (!strlen($action))
				CMSResponse::Back(_FORM_NC);
			insttool_confirm($action);
		break;
		case 'confirm':
			$action = in_raw('insttool_action', $_POST, '');
			if (!strlen($action))
				break;
			insttool_do($action);
		break;
	}
	break;
break;
case "info" :
	info_page();
	break;
case 'log':
	$_DRABOTS->loadCoreBotGroup('logger');
	if ($task==='clear')
		clear_log();
	$pathway->add(_SYSTEM_LOG);
	show_log();
	break;
case 'autoupdate':
	$pathway->add(_SYSTEM_AUW);
	$fn = $d_root.$d_private.'cache/updates.xml';
	$core_ver = current(explode(' ', $d_version));
	$url = $d__server.'index2.php?option=nest&no_html=1&task=list&core_ver='.$core_ver;
	if (!get_url($url, $fn)) {
		echo sprintf(_SYSTEM_URL_ERROR, $url);
		break;
	}
	
	$xml =& prospect_updates_xml();
	if (!isset($xml)) {
		echo _SYSTEM_CANNOT_RETRIEVE_UPDATES;
		break;
	}
	
	auto_update($xml);
break;

case 'packages':
	packages_view();
	break;

case "updates":
	switch($task) {
	case "install" :
		$tab_num = in_num('tab_num', $_POST);
		$package_id = in('cid', __ARR0, $_POST);
		switch ($tab_num) {
			case 1: CMSResponse::Redir('admin.php?com_option=patch&task=new&web_path='.$package_id);break;
			case 2: CMSResponse::Redir('admin.php?com_option=templates&task=new&web_path='.$package_id);break;
			case 3: CMSResponse::Redir('admin.php?com_option=language&task=new&web_path='.$package_id);break;
			case 4: CMSResponse::Redir('admin.php?com_option=components&task=new&web_path='.$package_id);break;
			case 5: CMSResponse::Redir('admin.php?com_option=modules&option=install&task=new&web_path='.$package_id);break;
			case 6: CMSResponse::Redir('admin.php?com_option=drabots&option=install&task=new&web_path='.$package_id);break;
		}
	break;
	case 'update':
		$core_ver = current(explode(' ', $d_version));
		$url = $d__server.'index2.php?option=nest&no_html=1&task=list&core_ver='.$core_ver;
		if (!get_url($url, $d_root.$d_private.'cache/updates.xml'))
			echo sprintf(_SYSTEM_CANNOT_RETRIEVE_SERVLET, '<em>'.xhtml_safe($url).'</em>');
	default:
		$xml =& prospect_updates_xml();
		new_updates($xml, isset($xml)?(int)@filemtime($d_root.$d_private.'cache/updates.xml'):0);
		break;
	}
break;
}

?>