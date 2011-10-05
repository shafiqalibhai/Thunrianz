<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
//L: because Enhanced Gallery is not part of the core package, we assert the folders to exist
if(!is_dir($absolute_path.'media/gallery/'))
	mkdir($absolute_path.'media/gallery/');
if(!is_dir($absolute_path.'media/gallery/thumbs/'))
	mkdir($absolute_path.'media/gallery/thumbs/');

echo '<h3>Enhanced Gallery installed</h3>';

?>