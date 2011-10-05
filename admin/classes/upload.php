<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

define('MAX_UPLOAD_FILES', 5);

global $d_root;
include $d_root.'includes/upload.php';

class Upload {

function Upload(){}

// $dir must be relative to $d_root
function upload_files($dir,$ext=null, $direct_upload_cb = null) {
	$done = array();
	// if the directory is not writable only get one upload file
	if (!is__writable($dir)) {
		if (isset($direct_upload_cb)) {
			//DEBUG
//			trigger_error('Cannot process uploaded data, directory not writable and no direct callback specified');
			$upload = in_upload('upload_file1', $dir, 0, $ext, true, null, true);
			if (!is_array($upload)) {
				if ($upload==='')
					return false;
				CMSResponse::Back($upload);
				return null;
			}
			return call_user_func_array($direct_upload_cb, $upload);
		}
	}
	for($i=1;$i<=MAX_UPLOAD_FILES;$i++) {
		$file_name= 'upload_file'.$i;
		$upload = in_upload($file_name, $dir, 0, $ext);
		if (!is_array($upload)) {
			if ($upload==='')
				continue;
			CMSResponse::Back($upload);
			return;
		}
		$done[] = $upload[1];
	}
	return $done;
}

function upload_interface($form,$dir, $allowed_ext = null) {
	if (!strlen($dir)) {
		trigger_error("Empty directory specified to upload_interface");
	}

	global $d,$d_root;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","",$form);
	$gui->add("spacer");
	$gui->add("com_header",_UPLOAD_FILES);

	$gui->add("tab_head");
	$gui->add("tab_simple","",_UPLOAD_SELECT,"");
	$gui->add("text",'',sprintf(_UPLOAD_FILES_EXP, fix_root_path($dir)));
	if (isset($allowed_ext))
		$gui->add("text",'', sprintf(_UPLOAD_FILES_EXT, raw_strtoupper(implode(', ', $allowed_ext))));
	$gui->add("spacer");
	if (!is__writable($dir))
		$max = 1;
	else $max = MAX_UPLOAD_FILES;
	for($i=1;$i<=$max;++$i) {
		$gui->add("file",'upload_file'.$i,_FILE_UPLOAD.' '.$i);
	}

	if ($max==1) {
		$gui->add('spacer');
		$gui->add('text', '', 'Your file upload will be directly processed because there are no write permissions on '.fix_root_path($dir));
	}
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

}
?>