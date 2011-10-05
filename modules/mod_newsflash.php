<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

global $conn,$access_sql;

// get the category id
$catid=$params->get('catid');

// select published content items in that category
$rsa = $conn->SelectColumn('#__content', 'id', ' WHERE catid='.$catid.' AND published=1 '.$access_sql);
// randomly select a content item
$rnum=count($rsa);
if ($rnum>1) {
	$randflash=mt_rand(0,$rnum-1);
} else if ($rnum==1)
	$randflash = 0;
else if (!$rnum) // premature exit if no content item available
	return;

include_once com_path('common', 'content');

global $first_content;
$first_content = true;

showcontent($rsa[$randflash], 'inline', false);

$first_content = false;

?>