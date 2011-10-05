<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/Content';

switch($task) {
	default : 
		$toolbar->add("publish");
		$toolbar->add("unpublish");
		$toolbar->add_split();
		$toolbar->add("reorder");
		$toolbar->add("delete");
	break;
}
?>