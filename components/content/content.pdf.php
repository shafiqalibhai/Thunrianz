<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$un_name = trim(preg_replace('/[^A-Za-z0-9\\.\\-\\_]+/', ' ', $crow['title']));

$caching = $params->get('export_cache', 1);
include $d_root.'includes/download.php';

if ($caching) {
	$cache_path = $d_root.$d_private.'cache/';

	$hash = md5($crow['modified'].' '.$id);
	$cache_file = 'content_'.$id.'.'.$hash;
	
	if (file_exists($cache_path.$cache_file)) {
		download($cache_path.$cache_file, filesize($cache_path.$cache_file), $un_name.'.pdf');
		exit();
	} else {
		// will remove older cached versions
		include_once $d_root.'includes/safe_glob.php';
		$oldies = safe_glob($cache_path.'content_'.$id.'.*');
		foreach($oldies as $oldie) {
			unlink($oldie);
		}
	}
}

require_once $d_root.'classes/dompdf/dompdf_config.inc.php';

$dompdf = new DOMPDF();

ob_start();

$this_url = xhtml_safe($d_website.'index.php?option=content&task=view&id='.$id.'&Itemid='.$Itemid);

$page_header = '<div style="position: absolute"><sup><a href="'.$this_url.'">'.$this_url.'</a></sup></div>';

$content_page_breaker .= $page_header;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<link rel="stylesheet" href="templates/<?php echo $d_template;?>/css/template_css.css" type="text/css" />
	</head>
	<body>
	<?php
	echo $page_header;
	// outputs the paged content
	global $pop;
	$pop = true; // for the drabots
	$crow = showcontent($id,'inline',false,false,'inline',2);
?></body>
</html>
<?php

$dompdf->load_html(ob_get_clean());

// to prevent errors due to 3rd party bad coding practices
$er = error_reporting(-1 ^ E_NOTICE ^ 2048);
	$dompdf->render();
error_reporting($er);

$dompdf = $dompdf->output();

send_download_headers($un_name.'.pdf', strlen($dompdf), $mime['pdf']);

echo $dompdf;

if ($caching)
	file_put_contents($cache_path.$cache_file, $dompdf);

$dompdf = null;

exit();

?>