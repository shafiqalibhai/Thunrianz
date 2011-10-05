<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Debug module
# @author legolas558
# shows page generation time
#

// JC - CSS Conversion complete

//L: for debug purposes
global $page_start_time,$conn;
echo '<p>Page generated in <strong>'.sprintf("%2.6f s",mt_float()-$page_start_time).'</strong> - '.
count(get_defined_constants()).' defined constants - '.count($GLOBALS).' global variables</p>';

// show the executed query and the used time
$query_info = $conn->QueryInfo();
$c = count($query_info[0]);
if ($c) {
	echo '<div style="text-align: left;">';
	echo '<h4>SQL debug information</h4>';
	echo '<p>Executed '.$c.' queries in '.array_sum($query_info[1]).' s</p><hr /><ol>';
	reset($query_info[1]);
	foreach($query_info[0] as $sql) {
		echo '<li><tt>'.xhtml_safe($sql).'</tt><blockquote>'.current($query_info[1]).'</blockquote></li>';
		next($query_info[1]);
	}
	echo '</ol></div>';
}

?>