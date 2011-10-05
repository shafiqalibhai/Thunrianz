<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Latest news module
#
# main module file
#

require_once $d_root.'modules/mod_latest_news.common.php';

global $conn, $access_sql;

$catid = $params->get( 'catid' ,'') ;
$secid = $params->get( 'secid', 0);
$count = $params->get( 'count', 3) ;
$col_count = $params->get( 'col_count', 1) ;
$desc = $params->get( 'desc', 0) ;

// apply section ID filtering
if ($secid) {
	$sql_filt = 'AND sectionid='.$secid;
} else {
	if($catid==='')
		$sql_filt='';
	else
		$sql_filt="AND catid=$catid";
}

$rsa = $conn->SelectArrayLimit('#__content',  'id,title,introtext,sectionid,catid,created,ordering,access'," WHERE published = 1 $access_sql $sql_filt ORDER BY ordering",$count*$col_count);
if (!isset($rsa[0]))
	return;

$inst = module_instance($module['instance'], 'content');
if ($col_count>1) {
	$total = count($rsa);
	// estimate the number of necessary columns
	$total_col = intval($total / $count) + ($total % $count ? 1 : 0);
	if ($total_col==1)
		_news_list($rsa, $desc, $inst);
	else {
	?><table cellspacing="0" cellpadding="0" width="100%">
	<tr><?php
	for($i=0;$i<$total_col;++$i) {
		echo '<td valign="top">';
		_news_list(array_slice($rsa, $i*$count, $count), $desc, $inst);
		echo '</td>';
	}
	?></tr></table><?php
	}
} else
	_news_list($rsa, $desc, $inst);

?>
