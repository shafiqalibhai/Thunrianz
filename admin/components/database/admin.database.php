<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## database component
# @author legolas558
#
#

require com_path('html');

include $d_root.'admin/classes/dbbackup.php';
$dbbak = new DbBackup();

switch ($option) {

	case 'query':
//		$pathway->add(_DB_QUERY);
		database_page(in_raw('query', $_POST));
		break;

default:
case 'manage_sql':

//global $d_root, $d_private;

switch($task) {
	case 'rebase':
		$pathway->add(_DB_REBASE);
		$dbbak->Rebase();
		break;
	case "upload" :
		include $d_root.'admin/classes/upload.php';
		// d_private contains the subsite path
		if (Upload::upload_files($d_root.$d_private."backup/", array('sql', 'php', 'gz'), '_database_direct_upload') !== true)
			CMSResponse::Redir("admin.php?com_option=database&option=manage_sql");
		break;
	case "download":
		$fn = in_path('fn', $_GET, '');
		ob_end_clean();
		if (!strlen($fn))
			CMSResponse::BadRequest();
		else {
			$full_fn = $d_root.$d_private.'backup/'.$fn;
			if (!is_file($full_fn)) {
				CMSResponse::NotFound();
				break;
			} else {
				$sz = filesize($full_fn);
				// we always download it via PHP, otherwise DOES NOT WORK (tm) most times
				include $d_root.'includes/download.php';
				download($full_fn, $sz);
			}
		}
		exit();
		break;
	case "upload_gui" :
		include $d_root.'admin/classes/upload.php';
		Upload::upload_interface("admin.php?com_option=database&option=manage_sql",$d_root.
									$d_private.'backup/', array('sql', 'php', 'gz'));
		break;
case "delete" :
	include $d_root.'admin/classes/fs.php';
	$fs = new FS();

	$cid = in('cid', __ARR | __PATH, $_POST, array());
	foreach($cid as $dfile )
		$fs->unlink ($d_root.$d_private.'backup/'.$dfile);
	CMSResponse::Redir("admin.php?com_option=database&option=manage_sql");
	break;
case "restore_sql" :
	$pathway->add(_DATABASE_RESTORE);
	include com_path('db_restore');
	global $sqlfile;
	$sqlfile = in('cid', __ARR0 | __PATH, $_POST);
	$basefn = $d_root.$d_private.'backup/'.$sqlfile;
	$msg = database_restore($basefn, $dbbak);
	
	if ($msg === true)
		break;
	if (strlen($msg))
		CMSResponse::Back($msg);
	
	break;
case "backup_sql":
case "backup_sql_gz":
	$file_written = $dbbak->Backup($d_root.$d_private.'backup/', true, ($task=='backup_sql_gz'));
	if (!$file_written)
		exit;
	else
		CMSResponse::Redir("admin.php?com_option=database&option=manage_sql");
	break;
default:
	manage_sql();
	break;
}

}

// used to process directly uploaded file when directory is not writable
function _database_direct_upload(&$data, $orig_fname, $orig_xtype) {
	include com_path('db_restore');
	global $dbbak;
	// split_job = false
	$msg = database_restore($orig_fname, $dbbak, false, $data);
	if ($msg!==true && strlen($msg))
		CMSResponse::Back($msg);
//	if ($msg === true)
	return true;
}

?>
