<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path('common'));

$pathway->add('Lanius CMS v'.cms_version());

$d->add_meta(_MANIFEST_DESC);

about_page();

?>