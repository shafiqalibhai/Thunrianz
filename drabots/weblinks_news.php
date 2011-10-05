<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Weblinks admin news
# @author legolas558
#
#

$_DRABOTS->registerFunction( 'OnCollectNews', 'weblinks_admin_news' );

function weblinks_admin_news() {
	global $conn;
	$rsw = $conn->SelectArray('#__weblinks', 'id,date,title', ' WHERE published=2 ORDER BY date DESC');
	$arr = array();
	foreach($rsw as $row) {
		$arr[] = array('date' => $row['date'],
				'title' => $row['title'],
				'type' => 'weblinks',
				'url' => 'admin.php?com_option=weblinks&amp;option=items&amp;task=edit&amp;cid[]='.
					$row['id'],
				'alt' => _USERS_WEBLINKS_NEW_HEAD
		);
	}
	return $arr;
}

?>
