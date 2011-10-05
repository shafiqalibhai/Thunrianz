<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Component/FAQ';

switch($task) {
	case 'new':
		$toolbar->add('create');
		$toolbar->add('cancel');
	break;
	case 'edit':
		$toolbar->add('save');
		$toolbar->add('cancel');
	break;
	default : 
		if ($option=='questions') {
			$toolbar->add('publish');
			$toolbar->add('unpublish');
			$toolbar->add_split();
		}
		$toolbar->add('new');
		$toolbar->add('edit');
		$toolbar->add('reorder');
		$toolbar->add('delete');
	break;
}
?>