<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Component/Polls';

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
		$toolbar->add('new');
		$toolbar->add('edit');
		$toolbar->add_split();
		$toolbar->add_custom_list(_POLLS_RESET, 'reset');
		$toolbar->add_split();
		$toolbar->add('reorder');
		$toolbar->add('delete');
}

?>