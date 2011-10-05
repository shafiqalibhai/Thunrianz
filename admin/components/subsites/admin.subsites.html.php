<?php
if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Subsites component for Lanius CMS
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# HTML output functions
#

function subsites_table() {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=subsites");
	$gui->add("com_header",_AMENU_SUBSITES);
	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>_SUBSITES_SUBPATH,'val'=>'subpath','len'=>'44%' , 'ilink' => 'admin.php?com_option=subsites&task=edit&cid[]=ivar1', 'ivar1' => 'id', 'ivar2' => 'subpath',
						  'explore' => 'ivar2admin.php'),
						  array('title'=>_TITLE,'val'=>'title','len'=>'44%') ,
						  array('title'=>_SUBSITES_STATUS,'val'=>'status','len'=>'10%','align'=>'center')
						 );
	$rs=$conn->Select('#__subsites', 'id, subpath');
	$table_data=array();
	$status = array(_SUBSITES_OFFLINE, _SUBSITES_ONLINE, '<i>'._SUBSITES_INVALID_PATH.'</i>');
	while ($row = $rs->GetArray(1)) {
		$row = $row[0];
		$S = _get_subsite($row['id']);
		$row['title'] =  $S->getVar('d_title');
		if (!isset($row['title']))
			$row['title'] = _NA;
		$online = $S->getVar('d_online');
		if (!isset($online))
			$row['status'] = _NA;
		else
			$row['status'] = $status[$online];
		$table_data[] = $row;
	}

	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();
}

function subsite_edit($clone, $task) {
	global $conn,$d_root;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=subsites");
	if ($task == 'create')
		$head = _SUBSITES_NEW;
	else
		$head = _SUBSITES_EDIT;
	$gui->add("com_header",$head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$head,"");

	if ($clone != 0) {
		$row=$conn->SelectRow('#__subsites', 'subpath',' WHERE id='.$clone);
		$subpath = $row['subpath'];
	} else
		$subpath = '';
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("text",'',sprintf(_SUBSITES_CLONE, ( !$clone ? _SUBSITES_ROOT : _SUBSITES_SUBSITE),
		'<strong>'.fix_root_path($d_root.($clone ? $subpath:'')).'</strong>').'<hr />'._SUBSITES_REMEMBER);
	$gui->add("textfield","subsite_path",_SUBSITES_SUBPATH,  $subpath, $v);
	$gui->add('spacer');
	$S =& _get_subsite($clone);
	if ($task=='create') {
		$title = $S->getVar('d_title').' subsite';
		$online = 0;
	} else {
		$title = $S->getVar('d_title');
		$online = $S->getVar('d_online');
	}
	$gui->add('textfield', 'subsite_title', _SUBSITES_TITLE, $title);
	$gui->add('boolean', 'subsite_online', _SUBSITES_IS_ONLINE, $online);
	
	// only for new subsites
	if ($task=='create') {
		global $d_db;
		if (($d_db!=='gladius') && (strpos($d_db,'sqlite')!==0))
			$gui->add('textfield', 'subsite_prefix', _SUBSITES_TABLE_PREFIX, _get_next_prefix($GLOBALS['d_prefix']), $v);
		else
			$gui->add('text', '', _SUBSITES_TABLE_PREFIX, $GLOBALS['d_prefix']);
		$gui->add('boolean', 'subsite_clean', _SUBSITES_CLEAN, 1);
	} else
		$gui->add('text', '', _SUBSITES_TABLE_PREFIX, $S->getVar('d_prefix'));
	if ($task == 'save') {
		$gui->add("hidden",'p_subpath',"",$subpath);
		$gui->add("hidden",'id',"",$clone);
	} else
		$gui->add("hidden",'src',"",$subpath);

	$gui->add("hidden",'task',"",$task);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function subsite_update($id) {
	global $pathway;
	$pathway->add(_SUBSITE_UPDATE);
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=subsites");
	$gui->add("com_header", _SUBSITE_UPDATE);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_SUBSITE_UPDATE);

	$S =& _get_subsite($id);
//	global $conn;
//	$row=$conn->SelectRow('#__subsites', 'subpath',' WHERE id='.$id);
//	$subpath = $row['subpath'];
	$newconn = new DbFork($S->getVar('d_db'));
	$newconn->SubInitialize($S->name, $S->getVar('d_uid'),
		$S->getVar('d_dbhost'),
		$S->getVar('d_dbusername'), $S->getVar('d_dbpassword'),
		$S->getVar('d_dbname'), $S->getVar('d_prefix'));
	$tables = $newconn->MetaTables();
	if (!in_array('packages', $tables)) {
		$gui->add('text', '', '<h2>'._SUBSITE_FATAL_ERROR.'</h2>');
		$gui->add('text', '', '<p>'._SUBSITE_ANCIENT.'</p>');
		$gui->add("tab_end");
		$gui->add("tab_tail");
		$gui->add("end_form");
		$gui->generate();
		return;
	}
	$ver = $newconn->SelectRow('#__packages', 'version', ' WHERE name=\'Lanius CMS\'');
	// for Drake CMS compatibility
	if (empty($ver)) {
		$ver = $newconn->SelectRow('#__packages', 'version', ' WHERE name=\'Drake CMS\'');
	}
	$ver = explode(' ', $ver['version']);
	$ver = current($ver);
	$parent_ver = explode(' ', cms_version());
	$parent_ver = current($parent_ver);
	if (strnatcmp($parent_ver, $ver)==0) {
		$gui->add('text', '', '<h2>'._SUBSITE_UP_TO_DATE.'</h2>');
		$gui->add('text', '', '<p>'.sprintf(_SUBSITE_NOTHING_TO_DO, xhtml_safe($S->getVar('d_title').' ('.$S->name.')')).'</p>');
		$gui->add("tab_end");
		$gui->add("tab_tail");
		$gui->add("end_form");
		$gui->generate();
		return;
	}
	global $my;
	require com_lang($my->lang, 'database');
	require com_path('dk_compat', 'database');
	global $d_root;
	include $d_root.'admin/classes/dbbackup.php';
	$dbbak = new DbBackup();
	ob_start();
		database_upgrade($newconn, $dbbak, $ver);
	$gui->add('text', '', ob_get_clean());
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function subsite_created($ss) {
	global $pathway;
	$pathway->add(_SUBSITES_CREATED);
	
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=subsites");
	$gui->add("com_header", _SUBSITES_CREATED);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_SUBSITES_CREATED);

	$gui->add('text', '', '<p><a href="'.$ss.'/admin.php" target="_blank">'._SUBSITES_ACCEED.' <strong>'.$ss.'/</strong></a></p>');
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

?>