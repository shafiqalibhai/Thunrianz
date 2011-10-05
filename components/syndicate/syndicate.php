<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include com_path('common');

$feed = feed_init($params);

//L: customizable through component parameters
$feed->setTitle(xhtml_safe($d_title));
$feed->setDescription((empty($d_desc) ? $d_title.' news' : $d_desc));
$feed->setLink(xhtml_safe($d_website));

$left = $params->get('show_count', 15)-1;

$rs = $conn->Select('#__content_frontpage', 'id', ' ORDER BY ordering');
while ($left>0) {
	$row = $rs->GetArray(1);
	if (!$row)
		break;
	$id = $row[0]['id'];
	$crow=$conn->SelectRow('#__content', "
	                           id,
	                           title,
	                           introtext,
	                           metadesc,
	                           sectionid,
	                           catid,
	                           modified,
	                           ordering,
	                           access",
				   " WHERE
	                           id=$id
	                     AND
	                           published=1
	                     $access_sql
	                     ORDER BY created DESC");
	if (!$crow)
		continue;
	$catrow = $conn->SelectRow('#__categories', 'id', ' WHERE id='.$crow['catid'].' '.$access_sql);
	if (!$catrow)
		continue;
        $input = array(
            'title' => $crow['title'],
            'description' => $crow['metadesc'],
            'link' => xhtml_safe($d_website)
                .'index.php?option=content&amp;task=view&amp;id='.$crow['id'].content_sef($crow['title']),
            'pubDate' => $crow['modified']
            );
        $feed->add_item(new $feed->item_class($input));
	--$left;
}

$feed->display();

?>