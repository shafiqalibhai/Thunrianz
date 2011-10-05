<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$_DRABOTS->registerFunction( 'onSearch', 'botLinksSearch' );

function botLinksSearch( $ret_array ,$text,$search_op, &$common_words, &$stricken_words) {
	global $conn,$access_sql;
	
	$rsa = $conn->SelectArray('#__categories', 'id,name,description', " WHERE section='com_weblinks' $access_sql ORDER BY ordering");
 
	if (!isset($rsa[0])) return;
	
	foreach($rsa as $row) {
		// search in the category is performed extensively because we need to search also into the subelements of the active categories
		if(search_ext($row['name'].$row['description'],$text,$search_op, $common_words, $stricken_words)) {
			$st_arr = array();
		        $st_arr['link']="index.php?option=weblinks&amp;catid=".$row['id'];
		        $st_arr['title']=$row['name'];
		        $st_arr['desc']=$row['description'];
	        $ret_array[]=$st_arr;
		}
		$wrsa = $conn->SelectArray('#__weblinks', 'id,title,description', ' WHERE published=1 AND catid='.
			$row['id'].' AND ('.
			search_query(array('title','description'), $text, $search_op, $common_words, $stricken_words).
			') ORDER BY ordering');
		foreach($wrsa as $wrow) {
			$st_arr['link']="index.php?option=weblinks&amp;task=visit&amp;id=".$wrow['id'];
			$st_arr['title']=$wrow['title'];
			$st_arr['desc']=$wrow['description'];
			$ret_array[] = $st_arr;
		}
	}
}

?>