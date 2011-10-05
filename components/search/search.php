<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include(com_path($d_type));

$pathway->add_head(_SEARCH_TITLE);

function search( $text,$search_op, &$common_words, &$stricken_words ) {
	global $conn;
	$ret_array=array();
	$rsa = $conn->SelectArray('#__content', 'id,title,introtext,bodytext,metadesc,metakey', ' WHERE '.
					'(published=1 OR published=4) AND ('.
					search_query(
						array('title','introtext','bodytext','metadesc','metakey'),
						$text, $search_op, $common_words,$stricken_words).
					') ORDER BY ordering');
	foreach($rsa as $row) {
		$st_arr = array();
		$st_arr['link']="index.php?option=content&amp;task=view&amp;id=".$row['id'];
		$st_arr['title']=$row['title'];
		$st_arr['desc']=($row['introtext']=="")?$row['bodytext']:$row['introtext'];
		$ret_array[] = $st_arr;
	}
	global $_DRABOTS;
	$_DRABOTS->loadBotGroup( 'search' );
	$_DRABOTS->trigger( 'onSearch', array( &$ret_array,$text,$search_op, &$common_words, &$stricken_words));
	return $ret_array;
}
  
$arr = array();
$searchphrase = in('sp', __NOHTML, $_GET, 'any');
$searchword = in('q', __NOHTML, $_GET, '');

$common_words = $stricken_words = array();
if (trim($searchword)!=='')
	$arr = search($searchword, $searchphrase, $common_words, $stricken_words);

search_html($arr, $common_words, $stricken_words);
?>