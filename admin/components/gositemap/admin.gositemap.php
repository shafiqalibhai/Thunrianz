<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

switch ($task) {
	case 'create':
		require com_path('xml');

		$pathway->add(_SITEMAP_CREATED);

		$sitemapfile = $d_root.$d->SubsitePath().'sitemap.xml';

		$count = 0;

		include $d_root.'admin/classes/fs.php';

		$fs = new FS();

		if ($fs->put_contents($sitemapfile, create_google_sitemap($count))) {

			echo '<h2>'._SITEMAP_CREATED.'</h2><p>';
			echo $count;
			echo ' '._SITEMAP_COUNT_INDEXED.'</p><p>'._SITEMAP_FROM.' <a target="_blank" href="sitemap.xml">sitemap.xml</a></p>';
		}
	break;
	default:
		require com_path('html');
		create_sitemap();
	break;
}

?>