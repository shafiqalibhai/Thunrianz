<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function _add_post_feed(&$feed, $row) {
	global $d_website;
	$input = array(
			'title' => $row['subject'].' by '.$row['name'],
			'description' => xhtml_safe(substr(remove_bb($row['message']), 0, 255)),
			'link' => xhtml_safe($d_website)
			.'index.php?option=forum&amp;task=viewpost&amp;catid='.$row['catid'].'&amp;post_id='.$row['id'].
			'#p'.$row['id'].content_sef($row['subject']),
			'pubDate' => $row['time']
	);
	$feed->add_item(new $feed->item_class($input));
}

	$cparams = $d->GetComponentParamsRaw('syndicate');
	include com_path('common', 'syndicate');

	$catid = in_num('catid', $_GET);
	
	if (!isset($catid)) {
		$row = $conn->SelectRow('#__menu', 'name', ' WHERE id='.$Itemid.' '.$access_sql);
		if (!$row) {
			CMSResponse::Unauthorized();
			return;
		}
		$title = $row['name'];
		$desc = $title.' recent posts';
		$pf = false;
	} else {
		$row = $conn->SelectRow('#__forum_categories', 'name,description', ' WHERE id='.$catid.' '.$access_sql);
		if (!$row) {
			CMSResponse::Unauthorized();
			return;
		}
		$title = $row['name'];
		$desc = $row['description'];
		$pf = true;
	}
	
	$feed = feed_init($cparams);

	$feed->setTitle($title);
	$feed->setDescription($desc);
	$feed->setLink(xhtml_safe($pathway->Current()));

	$left = $params->get('show_count', 15)-1;
	
	// fetch from a single category
	if ($pf) {
		$rsa = $conn->SelectArrayLimit('#__forum_posts', 'id,subject,name,message,time', ' WHERE catid='.$catid.' ORDER BY time DESC', $left);
		foreach($rsa as $row) {
			$row['catid'] = $catid;
			_add_post_feed($feed, $row);
		}
	} else {
	// fetch from accessible general categories
		$rs = $conn->Select('#__forum_posts', 'id,subject,name,message,time,catid', ' ORDER BY time DESC');
		while ($left>0) {
			$row = $rs->GetArray(1);
			if (!$row)
				break;
			$crow = $conn->SelectRow('#__forum_categories', 'id', ' WHERE id='.$row[0]['catid'].' '.$access_sql);
			if (!$crow)
				continue;
			_add_post_feed($feed, $row[0]);
			--$left;
		}
	}

	$feed->display();

?>