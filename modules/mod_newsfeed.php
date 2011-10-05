<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

global $d_root,$d_private,$time;

$newslink = trim($params->get('newslink', ''));
$show_count = $params->get('show_count', 5);
$refresh = $params->get('refresh', 3600);

if (!strlen($newslink))
	return;

$cache_file=$d_root.$d_private.'cache/newsfeed_'.md5($newslink).'.htm';

if((int)@filesize($cache_file)>0) {
  $newsdata=@unserialize(file_get_contents($cache_file));
  // in case of corrupted cached feed
  if ($newsdata !== false) {
	  $news_arr=$newsdata['readfile'];
	  $write=false;
	  if( ($time-$newsdata['timestamp']) > $refresh )
		$write=true;
	} else
		$write = true;
 } else $write = true;
	
if ($write) {
	$rd = get_url($newslink);
	if ((string)$rd!='') {
	
	$newsdata['timestamp']= $time;

	include_once $d_root.'classes/anyxml/anyxml.php';

	$xml = new AnyXML();
	
	$xml->fromString($rd);
	
	$rdf =& $xml->getElementByPath('rdf:RDF'); // RSS v1.0
	if(!isset($rdf))
		$rdf =& $xml->getElementByPath('channel'); // RSS v0.91 && v2.0

	$children =& $rdf->getAllChildren();

	$news_num=0;
	$news_arr=false;
	foreach($children as $child) {
		if ($child->getName() == 'item') {
	        $news_arr[$news_num]['nlink']=$child->getElementByPath('link');
	        $news_arr[$news_num]['nlink']=$news_arr[$news_num]['nlink']->getValue();
	        $news_arr[$news_num]['ntitle']=$child->getElementByPath('title');
	        $news_arr[$news_num]['ntitle']=$news_arr[$news_num]['ntitle']->getValue();
	        $news_arr[$news_num]['ndescription']=$child->getElementByPath('description');
	        $news_arr[$news_num]['ndescription']=$news_arr[$news_num]['ndescription']->getValue();
	        $news_num++;
		}
    } 
    $newsdata['readfile']=$news_arr ;
	file_put_contents($cache_file, serialize($newsdata));
} else {
	// load language resources to display error text
	$module = $module['module'];
	$path = mod_lang($my->lang, $module);
	include_once $path;
	echo '<p>'._NEWSFEED_FETCH_ERROR.'</p>';
}
}

	$c=min(count($news_arr), $show_count);
	for($i=0 ; $i<$c;$i++) {
	    echo '<a href ="'.$news_arr[$i]['nlink'].'" target="_blank">'.$news_arr[$i]['ntitle'].'</a><br />'.
		'<span>'.$news_arr[$i]['ndescription'].'</span><br />';
	}

?>
