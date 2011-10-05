<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## common admin includes
# @author legolas558
# all needed inclusions for admin user
# should be optimized not to directly include the user @see includes/header.php
# but only the needed includes

//L: include the user level files
include $d_root.'includes/header.php';

// this should stay here because contains references to shared language resources
include $d_root.'admin/classes/adminmenu.php';

// exit if user is not at least Publisher
if ($my->gid<3) {
	include($d_root.'admin/auth.php');

	@session_write_close();
	exit();
}

// website is offline, show online for admin and managers only
if (!$d_online and $my->gid < 4) {
	// force hard logout
	$my->Logout();
	CMSResponse::ServerError();
}

?>