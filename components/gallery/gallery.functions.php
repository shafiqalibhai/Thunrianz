<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

global $d;

$d->add_raw_js("\n".'function getSelectedValue( srcList ) {
	i = srcList.selectedIndex;
	if (i != null && i > -1) {
		return srcList.options[i].value;
	} else {
		return null;
	}
}'."\n");

define('_GALLERY_DEFAULT', 'media/gallery/');
define('_GALLERY_DEFAULT_THUMBS', 'media/gallery/thumbs/');

// clears a name retrieved from a URL
function clear_name($s) {
	$p=strrpos($s,'?');
	if ($p!==false)
		$s=substr($s,0,$p);
	$p=strrpos($s,'/');
	if ($p!==false)
		$s=substr($s,$p+1);
	return unix_name($s);
}

if (strtolower('ABCDEFGHIJKLMNOPQRSTUVWXYZ')!=='abcdefghijklmnopqrstuvwxyz') {

	// used for an ASCII strtoupper()
	function _raw_strtolower_cb($m) { return chr(ord($m[0])+32); }
	function raw_strtolower($s) {
		if (!strlen($s)) return '';
		return preg_replace_callback('/[a-z]/', '_raw_strtolower_cb', $s);
	}
} else {
	function raw_strtolower($s) {return strtolower($s);}
}


?>