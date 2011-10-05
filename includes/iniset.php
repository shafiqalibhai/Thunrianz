<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Manual INI setter
# @author legolas558
#
# will set each of the php.ini settings manually 

foreach(file($d_root.'php.ini') as $line) {
	if ($line[0]=='#') continue;
	$p = strpos($line, '=');
	$var = trim(substr($line,0,$p));
	$val = trim(substr($line,$p+1));
	if ($val=='on') $val=true;
	elseif($val=='off')$val=false;
	// added error silencing to prevent spurious lines in shell mode
	@ini_set($var, $val);
}

?>
