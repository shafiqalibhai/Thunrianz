<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Who's Online module
#
# @author legolas558
#
# this module has been refactored

global $stats,$my;

$module = $module['module'];

	$path = mod_lang($my->lang, $module);
	include_once $path;

//L: load the statistics if not previously already loaded (by the $d_stats parameter)
if (!isset($stats)) {
	include $d_root.'classes/stats.php';
	$stats = new Stats();
}

$gc = $stats->GuestsOnline();

$uc = $stats->MembersOnline();

if ($gc || $uc) {
	$content = _WHOSONLINE_WE_HAVE;
	if($gc) {
		$content .= $gc;
		if($gc==1) $content.= _WHOSONLINE_GUEST;
		else $content.= _WHOSONLINE_GUESTS;
	}

	if($uc) {
		if($gc) $content.=_WHOSONLINE_AND;
		$content.=$uc;
		if($uc==1)
			$content.=_WHOSONLINE_MEMBER;
		else $content.=_WHOSONLINE_MEMBERS;
	}
	$content.=_WHOSONLINE_ONLINE;

	echo $content;
} else
	echo _WHOSONLINE_NONE;

?>