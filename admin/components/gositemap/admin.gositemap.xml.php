<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

//L: by legolas558
function extimated_changefreq($modified) {
	global $time;
	
	$hours = (int)(($time-$modified)/(60*60));
	if ($hours<=12)
		return 'hourly';
	if ($hours<=24)
		return 'daily';
	if ($hours<=7*24)
		return 'weekly';
	if ($hours<=7*24*30)
		return 'monthly';
	return 'yearly';
}

function create_google_sitemap(&$count) {
	global $conn, $d_root, $d_website, $d_title, $access_sql, $d;

  // Create the start block.
  $startBlock = '<?xml version="1.0" encoding="'.$d->Encoding().'"?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">';
  // Create the end block.
  $endBlock = "</urlset>\n";

        $items='';
        $rsa=$conn->GetArray("SELECT id FROM #__content ORDER BY ordering");
		
		$count = 0;
		$c = count($rsa);
		foreach($rsa as $row) {
			$crow=$conn->GetRow("SELECT id,title,modified FROM #__content WHERE id=".$row["id"]." AND published=1 AND access<2");
			if($crow) {
				$items.= "<url>\n";
				$items.= "<loc>".$d_website."index.php?option=content&amp;task=view&amp;id=".$crow['id'].
						content_sef($crow['title'])."</loc>\n";
				//NOTE: do not use lc_strftime() here
				$items.= "<lastmod>".gmstrftime("%Y-%m-%d",$crow['modified'])."</lastmod>\n";
				$items.= "<changefreq>".extimated_changefreq($crow['modified'])."</changefreq>\n";
				$items.= "<priority>".sprintf('%.2F', ($c-$count)/$c)."</priority>\n";
				$items.= "</url>\n";
				$count++;
			}
		}

	return $startBlock.$items.$endBlock;
}

?>