<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Frontend Scripted UI include file
## @author legolas558
##
#

global $d_root,$d,$com_option, $option, $my;
$com_option = $option;
	
include $d_root.'lang/'.$my->lang.'/admin/admin.php';
include $d_root.'admin/includes/admin_functions.php';
include $d_root.'admin/classes/ui.php';
include $d_root.'admin/classes/toolbar.php';

global $toolbar;
$toolbar = new Toolbar();

?>