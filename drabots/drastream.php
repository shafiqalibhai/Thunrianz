<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Stream drabot
# @author legolas558
#
#

$_DRABOTS->registerFunction( 'onPrepareContent', 'botStreams' );

function _drastream_replacer($m) {
	global $_DRABOTS;
	$params = $_DRABOTS->GetBotParameters('content', 'drastream');

	$url = $params->get('url', '');
	if (!strlen($url))
		return 'Invalid stream url';

	$type = $params->get('type', 'wm');
	$hidden = $params->get('hidden', 0);
	$width = $params->get('width', 170);
	$height = $params->get('height', 150);
	$controls = $params->get('controls', 1);
	$autostart = $params->get('autostart', 1);

	include_once $GLOBALS['d_root'].'modules/mod_stream.common.php';
	
	ob_start();
	place_stream_object($url, $type, $hidden, $width, $height, $controls, $autostart);
	return '<span class="dkbot_stream">'.ob_get_clean().'</span>';
}

function botStreams( &$row ) {
	$row['introtext'] = preg_replace_callback( '/{drastream\s+(.*?)}/i', '_drastream_replacer', $row['introtext']);
	$row['bodytext'] = preg_replace_callback( '/{drastream\s+(.*?)}/i', '_drastream_replacer', $row['bodytext']);
}

?>