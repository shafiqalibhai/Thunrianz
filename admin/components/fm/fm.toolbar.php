<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Component/File_Manager';

function is_image($str) {
	return in_array(file_ext($str), $GLOBALS['d_pic_extensions']);
}

$sel_dir=true;

// get the path of the directory or file to be viewed
$view = in_raw('view', $_REQUEST, '');

if(isset($view)){
	if(is_file($view)){
		$sel_dir=false;
		$task='edit';
	}
}

switch($task) {
	case "upload_gui":
		$toolbar->add("upload");
		$toolbar->add("cancel");
	break;
	case "new":
	case "edit":
		if (strlen($view) && is_image($view)) {
			// no buttons
		} else
			$toolbar->add('save');
		$toolbar->add('cancel');
	break;
	case "directory":
		$toolbar->add_custom("Create","create");
		$toolbar->add('cancel');
	break;
	case "chmod":
		$toolbar->add('save');
		$toolbar->add('cancel');
	break;
	case "rename":
		$toolbar->add_custom(_FM_RENAME,"rename_save");
		$toolbar->add('cancel');
	break;
	default :
		$toolbar->add("new");
		$toolbar->add_custom(_FM_CREATE_DIR,"directory");
		$toolbar->add("upload_gui");
		$toolbar->add_split();
		$toolbar->add_custom_list(_FM_RENAME,"rename");
		$toolbar->add_custom_list("Chmod","chmod");
		$toolbar->add_split();
		$toolbar->add("delete");
	break;
}
?>