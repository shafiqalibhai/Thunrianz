<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Media Streaming module
# @author legolas558
#
# creates the proper EMBED code for any audio/video media file
#

$url = $params->get('url', '');
if (!strlen($url)) {
	echo 'Invalid stream url';
	return;
}

$type = $params->get('type', 'wm');
$hidden = $params->get('hidden', 0);
$width = $params->get('width', 170);
$height = $params->get('height', 150);
$controls = $params->get('controls', 1);
$autostart = $params->get('autostart', 1);

include_once $d_root.'modules/mod_stream.common.php';

place_stream_object($url, $type, $hidden, $width, $height, $controls, $autostart);

?>