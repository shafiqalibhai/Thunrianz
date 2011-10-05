<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$_DRABOTS->registerFunction( 'onSearch', 'botEventSearch' );

function botEventSearch(&$ret_array, $text, $search_op,&$common_words,&$stricken_words) {
	global $conn;
	$rsa = $conn->SelectArray('#__event', 'id,title,description,sdate', ' WHERE published=1 AND ('.
					search_query(array('title','description'), $text, $search_op, $common_words,$stricken_words).
					') ORDER BY sdate DESC');
  
	foreach($rsa as $row) {
		$st_arr = array();
        $st_arr['link']="index.php?option=event&amp;month=".date("m",$row['sdate'])."&amp;year=".date("Y",$row['sdate'])."#".$row['id'];
		$st_arr['title']=$row['title'];
		$st_arr['desc']=$row['description'];
		$ret_array[]=$st_arr;
    }
  
}
?>