<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

global $conn, $access_sql;

$catid = $params->get( 'catid', '') ;
$secid = $params->get( 'secid', 0);
$count = $params->get( 'count', 3) ;
$has_description = $params->get( 'desc' ,0);

// apply section ID filtering
if ($secid) {
	$sql_filt = 'AND sectionid='.$secid;
} else {
	if($catid==='')
		$sql_filt='';
	else
		$sql_filt="AND catid=$catid";
}

$rsa = $conn->SelectArrayLimit('#__content', 'id,title,introtext,sectionid,catid,created,ordering,access,hits'," WHERE published=1 $access_sql $sql_filt ORDER BY hits DESC, ordering ASC",$count);
if (!isset($rsa[0]))
	return;
?><ul><?php
	$inst = module_instance($module['instance'], 'content');
	global $d__utf8_unsafe;
	foreach($rsa as $row) {
		if ($has_description)
			$desc = content_summary($row['introtext'], 100);
		?><li><a href="index.php?option=content&amp;task=view&amp;id=<?php echo $row['id'].$inst.content_sef($row['title']); ?>"><?php
		echo $row['title']; ?></a><?php 
		if ($has_description)
			echo "<br />".$desc;  ?></li><?php 
	} ?>
</ul>
