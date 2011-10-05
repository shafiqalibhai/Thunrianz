<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## FAQ admin news
# @author legolas558
#
#

$_DRABOTS->registerFunction( 'OnCollectNews', 'faq_admin_news' );

function faq_admin_news() {
	global $conn;
	$rsf = $conn->SelectArray('#__faq', 'id,question,created', ' WHERE published=2');
	$arr = array();
	foreach($rsf as $row) {
		$arr[] = array( 'date' => $row['created'],
			'title' => $row['question'],
			'type' => 'faq',
			'url' => 'admin.php?com_option=faq&amp;option=questions&amp;task=edit&amp;cid[]='.$row['id'],
			'alt' => _START_FAQ);
	}
	return $arr;
}

?>