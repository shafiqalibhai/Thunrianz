<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$_DRABOTS->registerFunction( 'onSearch', 'botCommentSearch' );

function botCommentSearch($ret_array, $text, $search_op, &$common_words, &$stricken_words) {
	global $conn;

	$rsa = $conn->SelectArray('#__content_comment', 'id,comment_id,title,name,comment', ' WHERE published=1 AND ('.
		search_query(array('title','name','comment'), $text, $search_op, $common_words, $stricken_words).
		') ORDER BY date DESC');

	foreach($rsa as $row) {
		$st_arr = array();
		$st_arr['link']="index.php?option=content&amp;task=view&amp;id=".$row['comment_id']."#comment";
		$st_arr['title']=$row['title'];
		$st_arr['desc']=$row['comment'];
		$ret_array[]=$st_arr;
	}
}
?>