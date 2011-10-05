<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Lanius CMS Enhanced Gallery
# @author legolas558
# @version 1.2
# Released under GNU/GPL License
# This component is part of Lanius CMS core
#
# gallery component toolbar

$d__help_context = 'Component/Gallery';

if ($option=='upload') {
	$toolbar->add_custom(_TB_UPLOAD,"upload");
	$toolbar->add("cancel");
}else if ($option=='config') {
	$toolbar->add('save');
	$toolbar->add('cancel');
} else
	switch($task) {
		case "wizard":
			$toolbar->add_custom(_NAV_NEXT,"create");
			$toolbar->add("cancel");
		break;
		case "new":
			$toolbar->add("create");
			$toolbar->add("cancel");
		break;
		case "edit":
			$toolbar->add("save");
			$toolbar->add("cancel");
		break;
		case 'thumbnails': // no toolbar is shown
		break;
		default :
			if ($option=='items') {
				$toolbar->add("publish");
				$toolbar->add("unpublish");
				$toolbar->add("reorder");
				$toolbar->add_split();
				$toolbar->add_custom_list(_GALLERY_THUMBS, 'thumbnails');
				$toolbar->add_split();
			} else
				$toolbar->add("reorder");
			$toolbar->add_custom(_TB_WIZARD,"wizard");
			$toolbar->add_split();
			$toolbar->add_custom(_ADD,"new");
			$toolbar->add("edit");
			$toolbar->add("delete");
		break;
	}

?>