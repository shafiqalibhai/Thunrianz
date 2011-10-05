<?php
## Templateless content
# @see index.php
#
# content-only main output
# This is output source 2

//L: inclusion allowed for inner scripts
//L: where?? RFC!
if (!defined('_VALID')) {

	require 'core.php';
	include $d_root.'includes/header.php';
	
	// website is offline, show online for admin and managers only
	if(!$d_online and $my->gid < 4) {
		// force hard logout
		$my->RemoveLoginInfo();
		CMSResponse::ServerError();
	}
}

// wheter want or not compression (if feasible)
$no_comp = in_num('no_comp', $_GET, false);

// send initial headers
CMSResponse::Start(!$no_comp);

$no_html = in_num('no_html', $_GET, false);

if ($no_html) {
	// include the content without any vanilla HTML
	$d_type = 'raw';
	include $d_root.'includes/component.php';
        ob_end_flush();
        if (ob_get_level()) ob_flush();
	//PHP4
	@session_write_close();
	exit();
}

ob_start();
// include the component without any vanilla HTML
include $d_root.'includes/component.php';
$d->CatchComponent();
session_write_close();

	// output the XHTML 1.0 Transitional header
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<?php	$d->ShowHead();	?>
	</head>
	<body><?php $d->DumpComponent(); ?></body>
</html>