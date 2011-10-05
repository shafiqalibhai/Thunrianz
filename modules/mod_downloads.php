<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Downloads module
# @author Vikas Patial
# main module file
#

global $conn,$my;

$type = $params->get( 'type' ,'top') ;
$count = $params->get( 'count' ,'10') ;

switch($type) {
  case "top": 
    $ord = ""; 
    $order_str="ORDER BY hits DESC"; 
    break;
  case "last": 
    $ord = "down_date,"; 
    $order_str="ORDER BY down_date DESC"; 
    break;
  case "rating": 
    $ord = "rating_sum,"; 
    $order_str="ORDER BY rating_sum DESC"; 
    break;
  case "new": 
    $ord = "add_date,"; 
    $order_str="ORDER BY add_date DESC"; 
    break;
}

$rsa = $conn->SelectArrayLimit('#__downloads', 'id,title,'.$ord.'hits', " WHERE published=1 $order_str",$count);

if (isset($rsa[0])) {
$inst = module_instance($module['instance'], 'downloads');
  echo '<ul>';
  foreach($rsa as $row) {
    echo '<li><a href="index.php?option=downloads&amp;task=info&amp;id='.$row['id'].
		$inst.content_sef($row['title']).'">'.$row['title'].' ('.$row['hits'].')</a></li>';
  }
  echo '</ul>';
}
?>