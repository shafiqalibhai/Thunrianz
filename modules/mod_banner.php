<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Banner module
#
# main module file
#

// JC - CSS Conversion complete

global $conn,$d_website;

if(isset($module)) {
	global $$module['module'];
	$$module['module']=true;
} else return;

require_once $d_root.'modules/mod_banner.common.php';

$jsrotate = $params->get('jsrotate', 0) ;
$bannerid = $params->get('bannerid', '') ;
showbanner($bannerid, $jsrotate, $module);
?>