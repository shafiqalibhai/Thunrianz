<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

// properly set the global variable of subpath
global $fm_subpath;
$fm_subpath = $d->SubsitePath();

include $d_root.'admin/classes/upload.php';

// declared here because used many timesb
include $d_root.'admin/classes/fs.php';
$fs = new FS();

// get the path of the directory or file to be viewed
//L: already got in toolbar code
//$view = in_raw('view', $_REQUEST, '');
// if it's empty, show the directory list
// disable path escalation
if (!is_safe_path($view)) {
	CMSResponse::BadRequest();
	break;
}

switch($task) {
	case "delete":
		$cid = in_arr('cid', __RAW, $_POST);
		if (isset($cid)) {
			$prev = dirname($view).'/';
			foreach($cid as $var) {
				if (!strlen($var))
					continue;
				// do not delete previous directory '..', issue 508
				if ($var === $prev)
					continue;
				// delete the file or directory
				if (substr($var, -1)=='/')
					$fs->deldir($var);
				else
					$fs->remove($var);
			}
		}
		CMSResponse::Redir("admin.php?com_option=fm&view=".$view);
	break;
	case 'rename':
		$path = trim(in('cid', __ARR0, $_POST));
		if (!valid_path($path)) {
			CMSResponse::BadRequest();
			break;
		}
		rename_form($path, $view);
	break;
	case 'rename_save':
		$fm_file = in_raw('fm_file', $_POST);
		$fm_ofile = in_raw('fm_ofile', $_POST);
		if (!valid_path($fm_file) || !valid_path($fm_ofile)) {
			CMSResponse::BadRequest();
			break;
		}
		$fs->rename($fm_ofile, $fm_file);
		CMSResponse::Redir("admin.php?com_option=fm&view=$view");
	break;
	case "save" :
		$fm_file = in_raw('fm_file', $_POST);
		$chmod = in_raw('chmod', $_GET);
		$items = in_arr('items', __RAW, $_POST);
		$file_mode = in_raw('file_mode', $_POST);
		if (!isset($fm_file) && !isset($chmod))	//L: beware of picture saving and RO file saving!
			CMSResponse::Back('No file name');
		if (isset($fm_file)) {
			$view = dirname($fm_file).'/';
		} else if(isset($chmod)) {
			$view = dirname($items[0]).'/';
			if ($view == './')
				$view = '';
			foreach($items as $item) {
			      $new_mode = octdec("0".$file_mode);
			      $fs->chmod($item,$new_mode);
			}
			CMSResponse::Redir("admin.php?com_option=fm&view=".$view);
		}
		if ($view == './')
			$view = '';
		$fm_data = in_raw('fm_data', $_POST);
		if (!isset($fm_data))	//L: beware of picture saving and RO file saving!
			CMSResponse::Back('No file data');
		$fs->put_contents($d_root.$fm_subpath.$fm_file, $fm_data);
		CMSResponse::Redir("admin.php?com_option=fm&view=".$view);
	break;

	case 'upload':
		Upload::upload_files($d->SitePath().$view);
		CMSResponse::Redir("admin.php?com_option=fm&view=$view");
		break;
	case 'upload_gui':
		Upload::upload_interface("admin.php?com_option=fm&view=$view", $d_root.CMS::SubsitePath().$view);
		break;
	case "new":
		edit_file($view);
		break;

	case "directory":
		create_dir($view);
		break;

	case "create" :
		//TODO: CORRECT input of $fm_file
		$fm_file = in_raw('fm_file', $_REQUEST);
		$fs->mkdir($d_root.$fm_subpath.$fm_file);
		CMSResponse::Redir("admin.php?com_option=fm&view=$view");
		break;
	case "chmod":
		$cid = in('cid', __ARR, $_REQUEST);
		if (isset($cid))
			chmod_cid($cid);
	break;
//	case "edit" :
//	case "view" :
		break;
	default:
		if (!strlen($view)) {
			dir_table('');
			break;
		}
		if (substr($view, -1)=='/') {
			if (!is_dir($d_root.$fm_subpath.$view)) {
				CMSResponse::NotFound();
				break;
			}
			dir_table($view);
		} else
			edit_file($view);
	    break;
}

?>
