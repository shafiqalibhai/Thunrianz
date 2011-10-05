<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Get Firefox module
# @author legolas558
# @version 1.2
#

$sf = $params->get('show_filter', 'ie');

// do not show for all browsers
if ($sf != 'all') {
	// show for IE only
	if ($sf == 'ie')
		$can_show = (strpos(CMSRequest::UserAgent(), 'MSIE')!==false);
	else { // show for all non-Gecko
		// always show for Google Chrome (not Gecko)
		if (strpos(CMSRequest::UserAgent(), 'Chrome') !== false)
			$can_show = true;
		else
			$can_show = (strpos(CMSRequest::UserAgent(), 'Gecko')===false);
	}
} else
	$can_show = true;

// can't show, no output
if (!$can_show)
	return;
	
$msg = $params->get('msg', 'Take back the web!');

?><p align="center"><a href="http://www.getfirefox.com/" title="Get Firefox!" target="_blank"><img src="<?php echo $GLOBALS['d_subpath']; ?>modules/images/getff.png" border="0" alt="<?php echo xhtml_safe($msg); ?>" />
<?php

if (strlen($msg))
	echo '<br />'.$msg;

?></a></p>
