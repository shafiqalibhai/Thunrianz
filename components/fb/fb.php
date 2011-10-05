<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Frontend file browser component for Lanius CMS
# @author legolas558
#
# separate menu generation which allows client/server caching and faster admin backend menu generation
# NOTE: the destination window (when used) is set into the popup.opener.opener_window custom property
#
# Valid GET parameters:
#
# fi - Form Index - defaults to 0 when not used
# fi_name - FIeld NAME - defaults to empty string if not used
# preview - can be 0 = no preview, 1 = image preview, 2 = auto image preview (no double click needed) - default 2
# absurl - return absolute urls in field container (if used) - default 0
# onplink - Open into New Page link - offers a hyperlink of the currently selected file - default 1
# dirs - Allows directories to be selected - default 0
# path - path (and filename) of previously selected file, can also be the simple path without filename
#

require com_path('html');

$root_path = in_session('fb_root_path');
if (!isset($root_path)) {
	CMSResponse::Unauthorized();
	return;
}

$excluded_dirs = in($d_uid.'-fb_excluded_dirs', __ARR, $_SESSION);
if (!isset($excluded_dirs)) {
	CMSResponse::Unauthorized();
	return;
}

// extension data is passed through session
$ext = in_session('fb_ext', __ARR);

// upload of any file is available to administrators only, extensions list must always be set
if ($my->is_admin()) {} else if (!isset($ext)) {
	CMSResponse::Forbidden();
	exit();
}

/*** analyze the GET request variables and populate the FileBrowser options ***/

$fb = new FileBrowser();

$fb->fi = in_num('fi', $_GET, 0);

$fb->fi_name = in('fi_name', __NOHTML, $_GET, '');

$fb->preview = in_num('preview', $_GET, 2);

$fb->absurl = in_num('absurl', $_GET, 0);

$fb->onplink = in_num('onplink', $_GET, 1);

$fb->dirs = in_num('dirs', $_GET, 0);

$fb->files = in_num('files', $_GET, 1);

$fb->file_upload = in_num('file_upload', $_GET, 1);

$path = in_raw('path', $_GET, '');

// if path is empty, set to root path
if (!strlen($path)) {
	$path = $root_path;
	$cur_file = '';
} else {
// make all necessary checks on specified path
	if ($path=='../')
		$path = '';
	// check if this is a safe path
	if (!is_safe_path($path)) {
		CMSResponse::BadRequest();
		exit();
	}
	
	// check if the chosen path is allowed (browsable)
	if (!is_allowed_path($path, array($root_path))) {
		CMSResponse::Forbidden();
		exit();
	}
	
	// in case of backward request
	if (strlen($path)) {
		// if the path has a filename part, check it
		if ($path[strlen($path)-1]!='/') {
			$cur_file = basename($path);
			if (preg_match('/\\.[\\/\\\\]?/A', $cur_file)) {
				CMSResponse::BadRequest();
				exit();
			}
			$path = dirname($path).'/';
			$cur_file = $path.$cur_file;
		} else
			$cur_file = '';
	} else $cur_file = '';
	
	// if the path is under one of the excluded directories, fail
	if (is_allowed_path($path, $excluded_dirs)) {
//		echo $path.'<br>';
//		var_dump($excluded_dirs);die;
		CMSResponse::Forbidden();
		exit();
	}
}

// check the file extension (if current file specified)
if ($ext && strlen($cur_file) && !in_array(file_ext($cur_file), $ext))
	$cur_file = '';
	
if (!empty($_FILES)) {
	// disallow file upload if it was not active in this interface (weak protection here)
	if ( ! $fb->file_upload ) {
		CMSResponse::Forbidden();
		exit();
	}
	// upload is currently supported only to subdirectories of $root_path for non-admin users
	//L: not necessary as already checked above
/*	if ( (strpos($path, $root_path)!==0) && !$my->is_admin() ) {
		CMSResponse::Forbidden();
		exit();
	}	*/

	include $d_root.'includes/upload.php';
	
	// admins can upload anything, hence they will have extension set to blank
	$fb_upload = in_upload('fb_upload', $d->SitePath().$path, 0, ($ext ? $ext : null), false);
	if (is_string($fb_upload)) {	// if no file was uploaded
		if (!strlen($fb_upload)) {	// and if there is an error message
			CMSResponse::Back($fb_upload);
			exit();
		}
	} else
		$cur_file = substr($fb_upload[0], strlen($d_root));
}

$fb->path = $path;
$fb->cur_file = $cur_file;
$fb->ext = $ext;
$fb->root_path = $root_path;
$fb->excluded_dirs = $excluded_dirs;

/*
$_SESSION[$d_uid.'-fb_ext'] =
$_SESSION[$d_uid.'-fb_root_path'] =
$_SESSION[$d_uid.'-fb_excluded_dirs'] = null;
*/

$fb->ShowInterface();

?>