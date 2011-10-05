<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Tarball backup component for Lanius CMS
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# HTML output functions
#

function tarbak_fname($tarbak_tgz) {
	global $time;
	return lc_strftime('%Y-%m-%d', $time).'_'.get_domain(true).'_'.random_string(8).'.'.( $tarbak_tgz ? 'tgz' : 'tar');
}

// skip the cache directory, the database directory and the backups directory
$skip_folders = array( $d_private.'backup/', $d_private.'cache/' );

//legolas558: tarball archiving
function manage_tar() {
global $d_root,$d_private, $d_subpath;
$gui=new ScriptedUI();
$gui->add("form","adminform", '', 'admin.php?com_option=backup');
$gui->add("com_header",_SYSTEM_TAR_BACKUP);

$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
					  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
					  array('title'=>_NAME,'val'=>'id','len'=>'70%','info'=>_DOWNLOAD,'ilink'=>
					  // due to the hacky $d_private containing the subpath
					  $d_subpath.$d_private.'backup/ivar1','ivar1'=>'id') ,
					  array('title'=>_CDATE,'val'=>'created','len'=>'15%','date'=>'1','align'=>'center'),
					  array('title'=>_MDATE,'val'=>'modified','len'=>'15%','date'=>'1','align'=>'center'),
					  array('title'=>_SIZE,'val'=>'size','len'=>'15%','align'=>'center') ,
					 );
$backup_dir=$d_root.$d_private."backup/";
$table_data=read_dir($backup_dir,"file",true, array('tar', 'tgz'));
$gui->add("data_table_arr","maintable",$table_head,$table_data);
$gui->add("end_form");
$gui->generate();
}

	function continue_job($total, $done) {
		global $gui;
		$gui->add("text","", '<strong>'.((int)($done*100/$total)).'%</strong> '._BACKUP_COMPLETE.'<br/>' );
		$gui->add("hidden", "step","", 2);

		global $tarbak_tgz, $tarbak_dbf, $tarbak_dl, $tarbak_user_only;
		$gui->add("hidden", "tarbak_tgz","", $tarbak_tgz);
		$gui->add("hidden", "tarbak_dbf","", $tarbak_dbf);
		$gui->add("hidden", "tarbak_dl","", $tarbak_dl);
		$gui->add("hidden", "tarbak_user_only","", $tarbak_user_only);
		return array(array('name'=> _NEXT , 'onclick'=>'javascript:document.adminform.submit()'));
	}

function update_tarball($tar_fname, $step) {
	global $skip_folders, $d_root, $d_private, $gui;
	
	//TODO: do not add CMS core directories if not already present

	function update_cb(&$tar) {
		global $gui,$d;
		if (!$tar->Finished()) {
			$gui=new ScriptedUI();
			$gui->add("form","adminform", '', 'admin.php?com_option=backup');
			$gui->add("com_header",_SYSTEM_TAR_BACKUP);

			$gui->add("tab_head");
			$gui->add("tab_simple","", _BACKUP_UPDATE_IN_PROGRESS);

			$button_arr = continue_job($tar->progress->total, $tar->progress->done);

			$gui->add('spacer');
			$gui->add("buttons","","",$button_arr);

			$gui->add("tab_tail");
			$gui->add("end_form");
			$gui->generate();
		} else
			CMSResponse::Redir('admin.php?com_option=backup', 'Archive updated successfully');
	}
	
	include $d_root.'admin/classes/tarbackup.php';
	global $d_uid;
	$tar = new TarBackup($_SESSION[$d_uid.'-tarbackup']);
	
	$tar->logcb = '_logmessage';
	
	function _logmessage($s) {
		echo $s."\n";
		die;
	}

	// if this is the first step
	if ($step == 0) {

		$tar_fname = $d_root.$d_private.'backup/'.$tar_fname;
		global $d;
		$tar->BeginUpdate($d_root.$d->SubsitePath(), $tar_fname, $skip_folders);

		if (rev_path_matches($tar->dellist, $d_private.'backup/')) {
			include $d_root.'admin/classes/dbbackup.php';
			$dbbak = new DbBackup();
			$dest = $d_root.$d_private.'backup/';
			$chosen = $dbbak->Backup($dest, false); unset($dbbak);
			if (strlen($chosen))
				$tar->AddFile($d_private.'backup/'.$chosen);
		}

	} else if ($step == 2) {

		if (!isset($tar->progress)) {
			CMSResponse::Back(_BACKUP_TARBAK_RESTART);
			return;
		}
	}
	
	// try to run for about 4 minutes
	$max_time = shift_timeout(250);

	if (!$tar->PerformUpdate('update_cb', $max_time))
		CMSResponse::Back($tar->ErrorMsg());
}

function new_tarball($step) {
global $gui;
	$gui=new ScriptedUI();
	$gui->add("form","adminform", '', 'admin.php?com_option=backup');
	$gui->add("com_header",_SYSTEM_TAR_BACKUP);
	$gui->add("hidden", "task", '', 'new_tar');

	function continue_cb(&$tar) {
		global $gui, $tarbak_dl,  $d_root;

		if (!$tar->Finished())
			$button_arr = continue_job($tar->progress->total, $tar->progress->done);
		else {
			// backup finished correctly
			$fn = $tar->GetTarballName();
			global $my, $d;
			$d->log(3, $my->LogInfo().' created tarball backup '.substr($fn, strlen($d_root)));

			if ($tarbak_dl) {
				ob_clean();
				include_once $d_root.'includes/download.php';
				download($fn, filesize($fn));
				unlink($fn);
				exit;
			}
			
			global $d;
			$root = $d_root.$d->SubsitePath();
			$rp = substr($fn, strlen($root));
			$bn = basename($rp);
			$gui->add("text","", _SYSTEM_TARBAK_COMPLETE.'<br/><br/>'.
			'<big><a href="'.$rp.'">'.$bn.'</a></big> '.convert_bytes(filesize($root.$rp)));
			$gui->add("hidden", "step","", 0);
			$gui->add("hidden", "tarbak_del","", $bn);
			$button_arr = array(
						array('name'=> _TB_BACK , 'onclick'=>"document.location='admin.php?com_option=backup'"),
						array('name'=> _DELETE , 'onclick'=>'javascript:document.adminform.submit()')
			);
			$gui->add('spacer');
			$gui->add("text","", '<strong>'._BACKUP_NOTE.':</strong> '._BACKUP_NOTICE);
		}
		$gui->add('spacer');
		$gui->add("buttons","","",$button_arr);
	}

	// global definitions
	global $d, $d_private, $d_root;	
	$backup_path = $d_root.$d_private.'backup/';
	$backup_writable = is__writable($backup_path);	

	switch ($step) {
	case 0:

	$gui->add("tab_head");
	$gui->add("tab_simple","", _BACKUP_TARBAK_OPTIONS);

	if (shift_timeout(250)<30)
		$gui->add('text', '', _WARNING_NOT_ENOUGH_TIME);
	
	if (!$backup_writable) {
		$gui->add("hidden", 'tarbak_dl', '', 1);
		$gui->add('text', '', sprintf(_BACKUP_TARBAK_NW_BACKUP, '<strong>'.fix_root_path($backup_path).'</strong>'));
	} else {
		$gui->add("text","", sprintf(_BACKUP_TARBAK_INTRO , '<strong>'.fix_root_path($backup_path).'</strong>'));
		$gui->add('spacer');

		$gui->add('text', '', _BACKUP_TARBAK_START);
		$gui->add("boolean", 'tarbak_dl', '&nbsp;'._BACKUP_TARBAK_AUTO_DL, 0);
	}
	$gui->add('spacer');
	$gui->add("text","", _BACKUP_TARBAK_GZ);
	$gui->add("boolean", 'tarbak_tgz', '&nbsp;'._BACKUP_TARBAK_COMPRESS, 1);
	$private = substr($d_private, strlen($d->SubsitePath()));
	$gui->add("text","", sprintf(_BACKUP_USER_DATA_ONLY_DESC, $private, $private) );
	$gui->add("boolean", 'tarbak_user_only', '&nbsp;'._BACKUP_USER_DATA_ONLY, 1);

	$gui->add("text","", _BACKUP_TARBAK_FF);
	$gui->add("boolean", 'tarbak_sql', '&nbsp;'._BACKUP_TARBAK_SQL, true);

	$gui->add("text","", _BACKUP_TARBAK_DBF_DESC);
	$gui->add("boolean", 'tarbak_dbf', '&nbsp;'._BACKUP_TARBAK_DBF, false);

	$gui->add("hidden", "step","", 1);

	$button_arr = array(array('name'=> ($backup_writable ? _CONTINUE:_BACKUP_CREATE_N_DOWNLOAD ) , 'onclick'=>'javascript:document.adminform.submit()'));
	$gui->add('spacer');
	$gui->add("buttons","","",$button_arr);

	break;
	case 1:
	global $d_root;

	// in case of timeout before step 2 we reset and force the script to try again

	$tarbak_sql = in_num('tarbak_sql', $_POST, 1);

	if ($tarbak_sql) {
		include $d_root.'admin/classes/dbbackup.php';
		$dbbak = new DbBackup();
		//FIXME: subsite path included in $d_private
		$dest = $d_root.$d_private.'backup/';
		$chosen = $dbbak->Backup($dest, false);
	}
	
	// fallthrough allowed
	case 2:

	global $tarbak_dl, $tarbak_tgz, $tarbak_dbf;//, $tarbak_inst;
	$tarbak_dl = in_num('tarbak_dl', $_POST, 0);
	$tarbak_tgz = in_num('tarbak_tgz', $_POST, 0);
	$tarbak_dbf = in_num('tarbak_dbf', $_POST, 0);
	$tarbak_user_only = in_num('tarbak_user_only', $_POST, 1);
//	$tarbak_inst = in_num('tarbak_inst', $_POST, 0);
	
	// skip DB directory files
	if (!$tarbak_dbf) {
		global $d_dbname;
		$skip_folders[] = $d_private.$d_dbname.'/';
	}

	$gui->add("tab_head");
	$gui->add("tab_simple","", _BACKUP_TARBAK_ARCHIVING);
	global $d_root;

	include $d_root.'admin/classes/tarbackup.php';

//	if ($tarbak_inst)	$skip[] = 'admin/classes/pcl/';
	global $tar, $skip_folders,$d_private,$d_uid;
	
	if ($tarbak_user_only) {
		$skip_folders[] = 'admin/';
		$skip_folders[] = 'classes/';
		$skip_folders[] = 'docs/';
		$skip_folders[] = 'drabots/';
		$skip_folders[] = 'editor/';
		$skip_folders[] = 'media/common/';
		$skip_folders[] = 'includes/';
		$skip_folders[] = 'install/';
		$skip_folders[] = 'lang/';
		$skip_folders[] = 'modules/';
		$skip_folders[] = 'templates/';
		$skip_folders[] = 'components/';
		// dirty hack
		$skip_folders[] = 'index.php';
		$skip_folders[] = 'index2.php';
		$skip_folders[] = 'admin.php';
		$skip_folders[] = 'admin2.php';
		$skip_folders[] = 'core.php';
		$skip_folders[] = 'php.ini';
		$skip_folders[] = 'sitemap.xml';
		$skip_folders[] = 'version.php';
	}

//	$_SESSION[$d_uid.'-tarbackup'] = null;

	$tar = new TarBackup($_SESSION[$d_uid.'-tarbackup']);

	if ($step == 1) {
		if ($backup_writable)
			$dest = $backup_path;
		else // here we use the system temporary folder
			$dest = $GLOBALS['d_temp'];
		$tar->BeginCreation($d_root.$d->SubsitePath(), $dest.tarbak_fname($tarbak_tgz), $skip_folders);

		if ($tarbak_sql && strlen($chosen))
			//FIXME: a duplicate of the sql is currently created...why? - to be verified
			// remove length of subsite because already in $d_private (FIXME!!!)
			$tar->AddFile(substr($d_private, strlen($d->SubsitePath())).'backup/'.$chosen);

	} else if (!isset($tar->progress)) {
			echo 'TarBackup: session data lost, please restart';
			return;
	}

	if (!$tar->CreateBackup('continue_cb', shift_timeout(250) ))
		die($tar->errstr);

	break;
	}

$gui->add("tab_tail");
$gui->add("end_form");
$gui->generate();

}

function restore_tarball($fname) {
	global $pathway, $d_root, $d_private;
	$pathway->add("Restore tarball backup");
	require_once $d_root.'admin/classes/pcl/pcltar.lib.php';
	PclTarExtract($d_root.$d_private.'backup/'.$fname, $d_root);
	$pcl_success = (PclErrorCode()==1);
	if (!$pcl_success)
		echo PclErrorString();
	else
		echo "Extraction successful";
}

?>