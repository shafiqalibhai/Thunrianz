<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Upload functions
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# file upload facility

define('_DKUPL_NO_PROTECTION', 0);

define('_DKUPL_RANDOM_PREFIX', 1);

define('_DKUPL_CUSTOM_EXT', 2);

define('_DKUPL_EXT', 'bin');

// returns '' (no file), an error string or an array(moved file, original filename, mime type)
// the moved file path depends on the $dest_folder parameter; if not specified, it is relative
function in_upload($elem, $dest_folder = null, $protection = _DKUPL_NO_PROTECTION, $allowed_ext = null, $overwrite = true, $max_sz = null, $full_data = false) {
	if (empty($_FILES[$elem]['name']))
		return '';
	$tmp_name = $_FILES[$elem]['tmp_name'];
	
	// check the file size
	$file_sz = (int)@$_FILES[$elem]['size'];
	if (!isset($max_sz))
		$max_sz = return_bytes($GLOBALS['d_max_upload_size']);
	
	if (!is_uploaded_file($tmp_name)) {
		switch ($_FILES[$elem]['error']){
			case 0: // possible attack or malformed browser upload
				return _UPLOAD_INVALID;
			break;
			case 1: // the upload_max_filesize ini setting was not respected
				return sprintf(_UPLOAD_TOO_BIG_PHP, convert_bytes($file_sz),
					ini_get('upload_max_filesize'));
			break;
			case 2: // the maximum file size was exceeded. May not have considered MAX_FILE_SIZE field
				return sprintf(_UPLOAD_TOO_BIG, convert_bytes($max_sz));
			break;
			case 3: // upload was not completed
				return _UPLOAD_BROKEN;
			break;
			case 4: // no file was uploaded - this case should not be used
				return '';
			break;
		}
		// for possible custom error codes
		return sprintf(_UPLOAD_UNKOWN_ERROR, $_FILES[$elem]['error']);
	}
	
	if ($file_sz > $max_sz )
		return sprintf(_UPLOAD_TOO_BIG, convert_bytes($file_sz), convert_bytes($max_sz));
	// sanitize original filename - do not apply urldecode() (thanks to EgiX)
	$orig_name = $_FILES[$elem]['name'];
	// extract extension and keep it away
	$p = strrpos($orig_name, '.');
	if  ($p === FALSE)
		$ext = '';
	else {
		// sanitize also extension
		$ext = unix_name(substr($orig_name, $p+1));
		$orig_name = substr($orig_name, 0, $p);
	}
	// check if extension is allowed
	if (isset($allowed_ext)) {
		if (($ext==='') || (!in_array($ext, $allowed_ext)))
			return sprintf(_UPLOAD_DISALLOWED_EXT, implode(', ',$allowed_ext));
	}
	// sanitize basename
	$orig_name = unix_name(basename($orig_name));
	// re-add original extension
	if (strlen($ext)) $orig_name .= '.'.$ext;
	$phys_name = $orig_name;
	// check if filename contains .php, we disallow this because some misconfigured Apache hosts will run any file containing the .php string!!!
	global $my;
	if (($my->gid<4) && (strpos(raw_strtoupper($orig_name), '.PHP') !== false)) {
		// always log the upload attempt
		global $d;
		$d->log(2, sprintf("%s attempted to upload file called %s (%d bytes)", $my->LogInfo(), $orig_name, $file_sz));
		return sprintf("Filename must not contain .php");
	}
	
	if (!$full_data) {
		// provide a temporary storage location
		// the path must be absolute since it will be used by move_uploaded_file()
		global $d_root, $d_subpath, $d;
		if (!isset($dest_folder)) {
			// subsite path already in $d_private
			$dest_folder = $GLOBALS['d_temp'];
		}
		if ($protection & _DKUPL_RANDOM_PREFIX)
			$dest_folder .= random_string(6).'_';
	}
	// protect also by custom extension
	if ($protection & _DKUPL_CUSTOM_EXT)
		$phys_name .= '.'._DKUPL_EXT;
	
	if (!$full_data) {
		$full_name = $dest_folder.$phys_name;
		
		if (!$overwrite) {
			if (file_exists($full_name))
				return _UPLOAD_EXISTS;
		}
		
		// always log the file upload, even if it might not get finished
		global $d, $my;
		$d->log(2, sprintf(_FILE_UPLOAD_LOG, $my->LogInfo(), $full_name));
		include_once $d_root.'admin/classes/fs.php';
		$fs = new FS(false);
		// will trigger an error if failed
		$fs->move_uploaded_file($tmp_name, $full_name);
	} else {
		//TODO: log this operation
		
		// use $full_name to return the uploaded file content
		$full_name = @file_get_contents($tmp_name);
		if ($full_name === false)
			return _UPLOAD_CANNOT_READ;
	}

	// destination full filename, original filename, mime type ('' if none)
	return array($full_name, $orig_name, (string)@$_FILES[$elem]['type']);
}

?>
