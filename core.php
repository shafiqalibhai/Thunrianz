<?php
## Core include file
# @author legolas558
#
# basic include file for all valid Lanius CMS scripts

function mt_float()
{
   $a = explode(' ', microtime());
   return ((float)$a[0] + (float)$a[1]);
}

$page_start_time = mt_float();

define('_VALID', 1);

require 'version.php';

if (!file_exists( $d_root.$d_private.'config.php' )) {
	header( "Location: install/index.php" );
	exit();
}

require $d_root.$d_private.'config.php';

if ($d_iniset) {	// attempt to manually set the PHP ini custom settings
	require $d_root.'includes/iniset.php';
}

// detect windows OS on server
if (stristr(@php_uname(), 'windows'))
	$GLOBALS['d__windows'] = 1;
else {
	if (isset($_SERVER['OS'])) {
		if (stristr($_SERVER['OS'], 'windows'))
			$GLOBALS['d__windows'] = 1;
		else
			$GLOBALS['d__windows'] = 0;
	} else
		$GLOBALS['d__windows'] = 0;
}

include $d_root.'includes/errtrace.php';

set_error_handler('cms_error_handler');

error_reporting( -1 ^ 2048 );

include $d_root.'includes/functions.php';

include $d_root.'includes/dracon.php';

include $d_root.'includes/adodb.php';

?>
