<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Tarball backup component for Lanius CMS
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# Creates a backup of the whole site
#

//out_session('tarbackup', null);
//var_dump($_SESSION);die;

include com_path($d_type);

switch ($task) {
	default:
		manage_tar();
	break;
	case 'delete':
		$msg = '';
		include_once $d_root.'admin/classes/fs.php';
		$fs = new FS();
		if (null !== ($cid = in('cid', __ARR | __PATH, $_POST))) {
			foreach($cid as $dfile ) {
				$fs->unlink($d_root.$d_private.'backup/'.$dfile);
			}
		}
		CMSResponse::Redir("admin.php?com_option=backup");
		break;
	case 'update_tar':
		if (null !== ($cid = in('cid', __ARR | __PATH, $_POST))) {
			foreach($cid as $dfile )
				update_tarball($dfile, in_num('step', $_POST, 0));
		}
	break;
	case 'restore_tar':
		if (null !== ($cid = in('cid', __ARR0 | __PATH, $_POST))) {
			restore_tarball($cid, in_num('step', $_POST, 0));
		}
	break;
	case 'new_tar':
		if (null !==($tarbak_del = in_path('tarbak_del'))) {
			include_once $d_root.'admin/classes/fs.php';
			$fs = new FS();
			$fs->unlink($d_root.$d_private.'backup/'.$tarbak_del);
			CMSResponse::Redir('admin.php?com_option=backup');
		} else
			new_tarball(in_num('step', $_POST, 0));
	break;
}

?>