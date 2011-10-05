<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Gallery admin news
# @author legolas558
#
#

$_DRABOTS->registerFunction( 'OnCollectNews', 'gallery_admin_news' );

function gallery_admin_news() {
	global $conn;
	$rsg = $conn->SelectArray('#__gallery', 'id,date,title', ' WHERE published=2 ORDER BY date DESC');
	$arr = array();
	foreach($rsg as $row) {
		$arr[] = array( 'date' => $row['date'],
				'title' => $row['title'],
				'type' => 'photo',
				'url' => 'admin.php?com_option=gallery&amp;option=items&amp;task=edit&amp;cid[]='.$row['id'],
				'alt' => _START_GALLERY
		);
	}
	return $arr;
}

?>