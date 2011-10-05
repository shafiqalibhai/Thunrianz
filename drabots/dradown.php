<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Download drabot
# @author ???
#
#

$_DRABOTS->registerFunction( 'onPrepareContent', 'botDownloads' );

function _dradown_replacer($m) {
	include_once usr_com_path('common.php', 'downloads');

	ob_start();
	showitem($m[1]);
	//TODO: better CSS class name
	return '<span class="dkbot_down">'.ob_get_clean().'</span>';
}

function botDownloads( &$row ) {
	global $my;
	include_once com_lang($my->lang, 'downloads');

	$row['introtext'] = preg_replace_callback( '/{dradown\s+(.*?)}/i', '_dradown_replacer', $row['introtext']);
	$row['bodytext'] = preg_replace_callback( '/{dradown\s+(.*?)}/i', '_dradown_replacer', $row['bodytext']);
}

?>
