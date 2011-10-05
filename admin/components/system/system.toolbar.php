<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/System';

switch ($option) {
	case 'insttool':
		if (!$my->is_admin())
			break;
		if ($task=='ask_confirm')
			$toolbar->add_custom(_SYSTEM_PROCEED, 'confirm');
		else if ($task != 'confirm')
			$toolbar->add_custom(_SYSTEM_PROCEED, 'ask_confirm');
		$toolbar->add('back');
	break;
	case 'updates':
		$toolbar->add_custom(_UPDATE, "update");
		$toolbar->add_split();
		$toolbar->add("install");
	break;
	case 'packages':
		$toolbar->add("back");
		break;
	case 'log':
		global $_DRABOTS;
		$_DRABOTS->loadCoreBotGroup('logger');
		$r = $_DRABOTS->trigger('OnAdminMenuLogCanClear', array(), -1);
		if (isset($r) && ($r==true))
			$toolbar->add_custom(_SYSTEM_CLEAR, 'clear');
}

?>