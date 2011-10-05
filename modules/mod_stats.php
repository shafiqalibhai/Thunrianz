<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Simple statistics module
# @author Alex Dirk
#
# main module file
#

global $conn,$stats, $d_root, $my;

$module = $module['module'];

	$path = mod_lang($my->lang, $module);
	include_once $path;


if (!isset($stats)) {
	include $d_root.'classes/stats.php';
	$stats = new Stats();
}

$content ="";
$content .="<br /><strong>"._TIME_STAT.": </strong> " .lc_strftime('%H:%M');

$rsc=$conn->Count("SELECT COUNT(id) FROM #__users WHERE published=1");
$content .="<br /><strong>"._MEMBERS_STAT.":</strong> ".$rsc;

$rsc=$conn->Count("SELECT COUNT(id) FROM #__weblinks WHERE published=1");
$content .="<br /><strong>"._LINKS_STAT.":</strong> ".$rsc;

$content .="<br /><strong>"._HITS_STAT.":</strong> ".$stats->TodayVisits();

$content .="<br /><strong>"._STATS_VISITORS.":</strong> ".($stats->TotalVisits()+$stats->TodayVisits())."<br /><br />";
?><?php
echo $content;
?>