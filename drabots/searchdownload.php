<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Download search drabot
# @author legolas558
#@license GNU/GPL
#
#

$_DRABOTS->registerFunction( 'onSearch', 'botDownloadSearch' );

function botDownloadSearch(&$ret_array, $text, $search_op, &$common_words, &$stricken_words) {
	global $conn, $access_sql;
  
	$rsa = $conn->SelectArray('#__categories', 'id,name,description', " WHERE section='com_downloads' $access_sql ORDER BY ordering");
 
	if (!isset($rsa[0])) return;
	
	foreach($rsa as $row) {
		// search in the category is performed extensively because we need to search also into the subelements of the active categories
		if(search_ext($row['name'].$row['description'],$text,$search_op, $common_words, $stricken_words)) {
			$st_arr = array();
		        $st_arr['link']="index.php?option=downloads&amp;catid=".$row['id'];
		        $st_arr['title']=$row['name'];
		        $st_arr['desc']=$row['description'];
	        $ret_array[]=$st_arr;
		}
		
		// search into the elements is performed via SQL
		$frsa = $conn->SelectArray('#__downloads', 'id,catid,title,description,author', ' WHERE '.
						'published=1 AND catid='.$row['id'].' AND ('.
						search_query( array('title', 'description', 'author'), $text,$search_op, $common_words, $stricken_words).
						') ORDER BY ordering');
		foreach($frsa as $frow) {
			$st_arr = array();
				$st_arr['link']="index.php?option=downloads&amp;task=info&amp;id=".$frow['id'];
				$st_arr['title']=$frow['title'];
				$st_arr['desc']=$frow['description'];
			$ret_array[] = $st_arr;
		}
	}
	
}


?>