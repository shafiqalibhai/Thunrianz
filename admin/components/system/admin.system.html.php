<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function info_page() {
	global $pathway;
	$pathway->add(_SYSTEM_PHP_INFO);
	ob_start();
		phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES);
		$phpinfo = ob_get_clean();
	preg_match('#<body[^>]*>(.*)</body>#siU', $phpinfo, $output);
	$output = preg_replace('#<table#', '<table class="tbldata" ', $output[1]);
	$output = preg_replace('#(\w),(\w)#', '\1, \2', $output);
	$output = preg_replace('#border="0" cellpadding="3" width="600"#', 'border="0" cellspacing="1" cellpadding="4"  width="600"', $output);
	$output = preg_replace('#<td class="\\w+"#', '<td ', $output);
	$output = preg_replace('#<hr />#', '', $output);
	echo '<div align="center">'.$output.'</div>';
}

function version_page() {
	global $pathway;
	$pathway->add(_SYSTEM_VERSION_INFO);
	// this should NOT be internationalized
	echo '<h1>Lanius CMS Version Information</h1><big>';
		version_info();
	echo '</big>';
}

// $require_mode controls how incompatible items are marked
// 1 - incompatible items are removed, 2 - incompatible items are disabled

function &read_updates_xml($xml,$path,$req, $require_mode = 2) {
	$info = array();
	if (!isset($xml))
		return $info;
	$updates = & $xml->getElementByPath($path);
	if (!$updates)
		return $info;
	$update_element = $xml->getElementByPath($path);
	if (is_null($update_element))
		return $info;
	$items = $update_element->getAllChildren();
	
	if ($require_mode) {
		global $d_root;
		require_once $d_root.'admin/classes/install.php';
	}
	
	$msg = '';
	
	foreach($items as $item) {
		$tinfo=false;
		foreach($req as $var) {
			$obj = $item->getElementByPath($var);
			if ($obj)
				$tinfo[$var]=$obj->getValue();
			else
				$tinfo[$var]='';
		}
		if ($tinfo)	{
			if ($require_mode) {
				if (!Install::_requirements_ok($item, $msg)) {
/*					if ($require_mode==2)
						$tinfo['disabled'] = true;
					else */
						continue;
				}
			}
//			$tinfo['type']=$type;
			if (isset($tinfo['size']))
				$tinfo['size'] = convert_bytes($tinfo['size']);
			// fix the package identifier
			$tinfo['id'] .= '-'.$tinfo['version'];
			unset($tinfo['version']);
			$info[] = $tinfo;
		}
	}
	return $info;
}

function new_updates(&$xml, $fmtime) {
	global $d_root, $d_private, $d;

	$gui=new ScriptedUI();
	$gui->add("form","adminform", '', 'admin.php?com_option=system&option=updates');

	if (!isset($xml) || !$fmtime) {
		$gui->add('text', '', _SYSTEM_NO_UPDATES);
		$gui->add('end_form');
		$gui->generate();
		return;
	}

	$gui->add('text','', _SYSTEM_CACHED_UPDATE.' '.$d->DateFormat($fmtime));
	$gui->add('text','', _SYSTEM_UPDATE_NOTICE.' '.'<a href="javascript:ui_lcms_st(\'update\')">'._UPDATE.'</a>');

	$gui->enable_filter=false;
	$gui->add("com_header",_SYSTEM_REP_HEAD);
	$gui->add("tab_link","dtab");
	$gui->add("tab_head");

	$table_head = array ( array('title'=>'radio' , 'val'=>'id' , 'len'=>'1%') ,
					  array('title'=>_NAME,'val'=>'name','len'=>'50%') ,
					  array('title'=>_SYSTEM_TARGET,'val'=>'target','len'=>'10%') ,
					  array('title'=>_OWNER,'val'=>'author','len'=>'10%') ,
					  array('title'=>_CDATE,'Created','val'=>'creationDate','len'=>'10%','align'=>'center'),
					  array('title'=>_SIZE,'Size','val'=>'size','len'=>'10%','align'=>'center')
					 );

	$table_req = array ( 'id', 'name', 'target', 'author', 'version', 'creationDate', 'size');
	
	$gui->add("tab",_PATCHES,_PATCHES_INSTALL,"dtab");
	$items = read_updates_xml($xml, 'patches', $table_req);
	$gui->add("data_table_arr", "maintable", $table_head, $items);
	$gui->add("tab_end");
	
	$table_head = array ( array('title'=>'radio' , 'val'=>'id' , 'len'=>'1%') ,
					  array('title'=>_NAME,'val'=>'name','len'=>'50%') ,
					  array('title'=>_OWNER,'val'=>'author','len'=>'10%') ,
//					  array('title'=>_VER,'val'=>'version','len'=>'10%','align'=>'center') ,
					  array('title'=>_AMENU_HELP_LICENSE,'val'=>'license','len'=>'10%','align'=>'center') ,
					  array('title'=>_CDATE,'Created','val'=>'creationDate','len'=>'10%','align'=>'center'),
					  array('title'=>_SIZE,'Size','val'=>'size','len'=>'10%','align'=>'center')
					 );

	$table_req = array ( 'id', 'name', 'author', 'version', 'license', 'creationDate', 'size');

	$items = read_updates_xml($xml,'templates',$table_req );
	$gui->add("tab",_TEMPLATES,_TEMPLATES_INSTALL,"dtab");
	$gui->add("data_table_arr","maintable",$table_head,$items);
	$gui->add("tab_end");

	$items = read_updates_xml($xml,'languages',$table_req );
	$gui->add("tab",_LANG,_LANG_INSTALL,"dtab");
	$gui->add("data_table_arr","maintable",$table_head,$items);
	$gui->add("tab_end");

	$items = read_updates_xml($xml,'components',$table_req );
	$gui->add("tab",_COMPONENTS,_COMPONENTS_INSTALL,"dtab");
	$gui->add("data_table_arr","maintable",$table_head,$items);
	$gui->add("tab_end");

	$items = read_updates_xml($xml,'modules',$table_req );
	$gui->add("tab",_MODULES,_MODULES_INSTALL,"dtab");
	$gui->add("data_table_arr","maintable",$table_head,$items);
	$gui->add("tab_end");

	$items = read_updates_xml($xml,'drabots',$table_req );
	$gui->add("tab",_DRABOTS,_DRABOTS_INSTALL,"dtab");
	$gui->add("data_table_arr","maintable",$table_head,$items);
	$gui->add("tab_end");

	$gui->add("tab_tail");
	$gui->add("hidden","tab_num","",'');
	$gui->add("tab_sel","dtab","","1");

	$gui->add("end_form");
	$gui->generate();
}

function auto_update(&$xml) {
	$gui=new ScriptedUI();
	$gui->add("form","adminform");
	if ($xml->getName()!='updates')
		$gui->add('text', '', _SYSTEM_INVALID_XML);
	else {
		$remote_ver = $xml->attributes('version');
		$gui->add('text', '', '<h2>'._SYSTEM_REMOTE_VERSION.': '.$remote_ver.'</h2>');
		
		$a = explode(' ',cms_version());
		$a = current($a);
		$b = explode(' ', $remote_ver);
		$b = current($b);
		$delta = strnatcmp($a, $b);
	
		if ($delta<0)
			$gui->add('text', '', _SYSTEM_NEED_UPDATE);
		else if ($delta==0)
			$gui->add('text', '', _SYSTEM_UP_TO_DATE);
		else
			$gui->add('text', '', _SYSTEM_UNKNOWN);
	}
	$gui->add('end_form');
	$gui->generate();
}

function show_log() {
	global $d_root, $d_private, $_DRABOTS;
	$rv = $_DRABOTS->trigger('OnAdminMenuLogPages');
	if (count($rv))
		$total_pages = $rv[0];
	else $total_pages = 1;
	
	include $d_root.'classes/pagenav.php';
	
	$pn = new PageNav(1);
	$pn->SetTotal($total_pages);
	$gui=new ScriptedUI();
	$gui->add("form","adminform");
	
	$html = '<p align="center">'.$pn->NavBar('com_option=system&option=log').'</p><h1>&nbsp;<img src="'.admin_template_pic('log.png').'" alt="Log" />&nbsp;'._SYSTEM_LOG_ENTRIES.'</h1><tt style="	white-space: -moz-pre-wrap !important;  /* Mozilla, since 1999 */
	white-space: pre-wrap;       /* css-3 */
	white-space: -pre-wrap;      /* Opera 4-6 */
	white-space: -o-pre-wrap;    /* Opera 7 */
	word-wrap: break-word;       /* Internet Explorer 5.5+ */ ">';

	ob_start();
	//TODO: show page navigation links
	$_DRABOTS->trigger('OnAdminMenuLogShow', array($pn->Page()), -1);
	$gui->add('text', '', $html.ob_get_clean().'</tt>');
	$gui->add("end_form");
	$gui->generate();
}

function clear_log() {
	global $_DRABOTS;
	$_DRABOTS->trigger('OnAdminMenuLogClear', array(), -1);
}

function insttool_form() {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=system&option=insttool");
	$gui->add("com_header", _AMENU_INSTALL_TOOL);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_AMENU_INSTALL_TOOL);
	
	$gui->add('text', '', text_to_html(_SYSTEM_INSTTOOL_TEXT));
	$gui->add('spacer');

	$gui->add('textfield', 'insttool_action', _SYSTEM_INSTTOOL_ACTION, '');
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function insttool_confirm($action) {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=system&option=insttool");
	
	switch ($action) {
		case 'DROPALL':
		case 'REMALL':
		case 'REINSTALL':
		case 'UNINSTALL':
		break;
		default:
			// in case of invalid action
			global $d;
			CMSResponse::Redir('admin.php?com_option=system&option=insttool', $action.' is not a valid action');
			return;
	}
	
	$gui->add("com_header", $action.' - '.constant('_SYSTEM_'.$action.'_HEAD'));
	$gui->add("tab_head");
	$gui->add("tab_simple","",constant('_SYSTEM_'.$action.'_HEAD'));
	
	$gui->add('text', '', text_to_html(constant('_SYSTEM_'.$action.'_TEXT')));
	$gui->add('spacer');
	$gui->add('text', '', _INSTALL_INSTOOL_GO_ON);
	$gui->add('spacer');

	$gui->add('hidden', 'task', '', 'confirm');
	$gui->add('hidden', 'insttool_action', '', $action);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function packages_view() {
	global $conn;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=system&option=packages");
	$gui->add('spacer');
	$gui->add("com_header", _PACKAGES);

	$table_head = array ( array('title'=>_NAME,'val'=>'name','len'=>'40%'),
						  array('title'=>_TYPE, 'val'=>'type' , 'len'=>'40%') , 
						  array('title'=>_VER, 'val'=>'version' , 'len'=>'20%') , 
					); 
		 
	$table_data=$conn->SelectArray('#__packages', 'name,type,version');  
	$gui->add("data_table_arr","maintable",$table_head,$table_data);

	$gui->add("end_form");
	$gui->generate();					
}

?>