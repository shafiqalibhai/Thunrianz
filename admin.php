<?php
## Admin backend
#
# main admin backend script (web)
# This is output source 0

define( "_VALID_ADMIN", 1 );

require 'core.php';

include $d_root.'admin/includes/header.php';

include $d_root.'admin/classes/ui.php';
include $d_root.'admin/classes/toolbar.php';
include $d_root.'admin/classes/easydb.php';
include $d_root.'classes/anyxml/anyxml.php';
include $d_root.'admin/classes/pathway.php';

/* WARNING: TODO:
 * this variable localization was moved here to free the front end.
 * This should be removed before any final release, but poses less
 * of a security risk here than in the front end.
 * The admin area still depends heavily on this code, and this should be addressed.
 */
if (false) {
	$_PG = array_keys($GLOBALS);
	if ($d__bad_env) {
		foreach($_GET as $var => $val) {
			if (in_array($var, $_PG))
				continue;
			if (!is_array($val))
				$$var = stripslashes($val);
			else
				$$var = $val;
		}
		foreach($_POST as $var => $val) {
			if (in_array($var, $_PG))
				continue;
			if (!is_array($val))
				$$var = stripslashes($val);
			else
				$$var = $val;
		}
	} else {
		extract($_GET, EXTR_SKIP);
		extract($_POST, EXTR_SKIP);
	}
}

$toolbar = new Toolbar();

$option = in_raw('option');
$com_option = in('com_option', __PATH, $_GET, 'start');

// support non-javascript toolbars
$task = $toolbar->GetTask();

$pathway = new AdminPathway();
$easydb = new EasyDB();

// whether want or not compression (if feasible)
$no_comp = in_num('no_comp', $_GET, false);

// prepare the response
CMSResponse::Start(!$no_comp);

$row = $conn->SelectRow('#__components', 'admin_access', ' WHERE option_link=\'com_'.$com_option.'\'');
if (!count($row)) {
	CMSResponse::NotFound();
	return;
}

// check access level for the admin component
if ($row['admin_access'] > $my->gid) {
	CMSResponse::Unauthorized();
	return;
}

$cdir=$d_root."admin/components/$com_option/";

// include the proper language resources
$path = com_lang($my->lang);
if (file_exists($path)) {
	require $path;
}

//$pathway = "<strong>$d_title</strong> / $com_option";
//if($task!=="") $pathway.=" / $task";

// include toolbar buttons initializer if present
$path = $cdir.$com_option.'.toolbar.php';
if (file_exists($path)) {
	include $path;
}

// put the component output into a buffer
if (!defined('_NO_TEMPLATE'))
	ob_start();

// execute the actual component code
require $cdir.'admin.'.$com_option.'.php';

if (defined('_NO_TEMPLATE'))
	return;
// toolbar will be generated inside component's form
$d->CatchComponent();

$pathway = $pathway->Generate();

if (defined('_RAW_TEMPLATE')) {

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title><?php echo $d->title.' ';
			if ($d_subpath!=='') echo 'Subsite ';
			echo 'Administration'; ?></title>
<?php echo $d->ShowMainHead(); ?>
</head>
<body><?php
	// no template
	$toolbar->generate();
	$d->DumpComponent();
?></body>
</html><?php } else {
	// admin template must always exist!
	include $d_root.'admin/templates/'.$d_atemplate.'/index.php';
}

//PHP4
@session_write_close();
?>