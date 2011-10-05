<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Downloads admin news
# @author legolas558
#
#

$_DRABOTS->registerFunction( 'OnCollectNews', 'downloads_admin_news' );

function downloads_admin_news() {
	global $conn;
	$rsd = $conn->SelectArray('#__downloads', 'id,add_date,title', ' WHERE published=2 ORDER BY add_date DESC');
	$arr = array();
	foreach($rsd as $row) {
		$arr[] = array( 'date' => $row['add_date'],
			'title' => $row['title'],
			'type' => 'download',
			'url' => 'admin.php?com_option=downloads&amp;option=items&amp;task=edit&amp;cid[]='.$row['id'],
			'alt' => _START_DOWNLOADS
		);
	}
	return $arr;
}

?>