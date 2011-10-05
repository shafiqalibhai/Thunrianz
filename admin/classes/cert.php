<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Certification utility class
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# creates an unique md5 hash for the requested files
#

class	Certification {
	
	function Hash($filename) {
		if (!is_readable($filename))
			return null;
		return md5(file_get_contents($filename)).':'.filesize($filename);
	}
	
	function SplitHash($hash) {
		if (!preg_match('/^([a-z0-9]{32,32}):(\\d+)$/', $hash, $m))
			return null;
		array_shift($m);
		return $m;
	}
	
}

?>