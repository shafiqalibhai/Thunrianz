<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

//L: example usage
if (!is__writable($d_root.'public/sitemap.xml'))
	echo 'Cannot write to public/sitemap.xml';

echo '<h3>Lanius CMS Google Sitemap Installed</h3>';
?>