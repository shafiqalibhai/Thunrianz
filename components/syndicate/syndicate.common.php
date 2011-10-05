<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function feed_init($params) {
	global $d_root, $d_title, $d_website, $d_descm, $pathway;

	require $d_root.'/classes/feed/feed_base.php';

	// the factory returns a valid class for the currently requested feed type
	$feed = feed_base::factory(in_raw('feed_type', $_GET),
		$params->get('default_feed_type', 'rss_2_0'),
		array(
			'atom_1_0' => $params->get('atom_1_0_enabled', 1),
			'rss_1_0' => $params->get('rss_1_0_enabled', 1),
			'rss_2_0' => $params->get('rss_2_0_enabled', 1)
		));

	$feed->setAuthor( $params->get('atom_channel_author', 'Lanius CMS syndication') );

	$feed->setChannelTitle($params->get('atom_channel_title', 'Lanius CMS atom feed'));

	//L: dummy email address generated through get_domain()  - true is for the domain extension (needed)
	//L: a valid alternative would be noreply@example.com when the administrator does not want an email address in the ATOM feed
	//L: and that value is currently used as default in admin/components/syndicate/syndicate.xml
	//L: NOTE: it is currently possible to specify an empty '' email address in the component parameters. Is that OK with the ATOM RFC?
	$mail = $params->get('atom_channel_mail');
	if (!isset($mail))
		$mail = 'noreply@'.get_domain(true);
	$feed->SetMail($mail);

	//L: used to set the current URL in the pathway class
	$pathway->add('');

	header('Content-type: '.$feed->mime);
	
	return $feed;
}

?>