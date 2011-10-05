<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Admin language component
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# toolbar
#

$d__help_context = 'Administration/System/Languages_manager';

switch($task) {
	case 'new':
		$toolbar->add('install');
		$toolbar->add('cancel');
	break;

	case 'edit':
	case 'commit':
		$toolbar->add_custom(_SAVE_AND_BACK, 'commit', 'if (language_submit()) ui_lcms_st(\'commit\')');
		$toolbar->add_split();
		$toolbar->add_custom(_TB_SAVE, 'save', 'if (language_submit()) ui_lcms_st(\'save\')');
		$toolbar->add('cancel');
	break;
	case 'clone':
		$toolbar->add('save');
		$toolbar->add('cancel');
	break;

	case 'info':
	case 'verify':
	case 'repair':
	case 'normalize':
		$toolbar->add_custom(_TB_BACK, 'back',
			'javascript:location.href=\'admin.php?com_option=language\'');
	break;
	case 'create':
		$toolbar->add_custom(_TB_CREATE, 'clone');
		$toolbar->add('cancel');
	break;
	default:
		$toolbar->add('publish');
		$toolbar->add('unpublish');
		$toolbar->add_split();
		$toolbar->add('edit');
		$toolbar->add_custom(_LANGUAGE_REPAIR, 'repair', "javascript:do_task('repair', false)");
		$toolbar->add_custom(_LANGUAGE_NORMALIZE, 'normalize', "javascript:if (confirm('".js_enc(_LANGUAGE_NORMALIZE_ALERT)."')) do_task('normalize', true)");
		$toolbar->add_custom(_LANGUAGE_VERIFY, '', "javascript:do_task('verify', false)");
		$toolbar->add_split();
		$toolbar->add('create');
		$toolbar->add_custom(_INSTALL,'new');
		$toolbar->add_split();
		$toolbar->add('delete');
	break;
}

?>