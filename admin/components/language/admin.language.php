<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Admin language component
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# features management
#

$d__help_context = 'Administration/System/Languages_manager';

function _echo_cb($s) { echo $s."\n"; }

global $lang_subpath;
$lang_subpath='lang/';

$d->add_raw_js("
function do_task(task, ident) {
	var rob = null;
	var selected = '';
	i=1;
	do {
		rob=document.getElementById('rb'+i);
		if (!rob) break;
		if (rob.checked) {
			selected = rob.value;
			break;
		}
		i++;
	} while (true);
	if (selected=='') {
		alert('".js_enc(_IFC_LIST_ERR)."');
		return;
	} else if (!ident && (selected=='en')) {
		alert('".js_enc(_LANGUAGE_INVALID_SELECTION)."');
		return;
	}
	ui_lcms_st(task);
}");

require_once com_path("html");

//L: used to renew the main languages files index
//write_language_descriptor('en', 'English', '0.2', 'legolas558', '', 'language.xml');
//die;

switch($task) {
	case "save":
	case 'commit':
		$lang = in_raw('lang', $_POST);
		if (!isset($lang))
			break;
		$files = parse_lang_xml($lang, $lang_name);
		if (!$files)
			break;
		$dtab = in_num('dtab_combo', $_POST, 1);
		d_setcookie('dtab', $dtab);

		dklang_language_update($lang, $files);
		if ($task=='commit')
			CMSResponse::Redir('admin.php?com_option=language&task=edit&cid[]='.$lang);
		else CMSResponse::Redir('admin.php?com_option=language');
		break;
	case 'repair':
		$pathway->add(_LANGUAGE_REPAIR);
		$lang = in('cid', __ARR0|__PATH, $_POST, 2, 'en');
		if ($lang!='en') {
			repair_language($lang, '_acc_cb');
			atomic_form();
		}
	break;
	case 'verify':
		$pathway->add(_LANGUAGE_VERIFY);
		$lang = in('cid', __ARR0|__PATH, $_POST, 2);
		if (!isset($lang))
			break;
		
		atomic_lang_op($lang, 'verify_cb', '_acc_cb');
		atomic_form();
		break;
	case 'normalize':
		$pathway->add(_LANGUAGE_NORMALIZE);
		$lang = in('cid', __ARR0|__PATH, $_POST, 2);
		if (!isset($lang))
			break;
		
		atomic_lang_op($lang, 'normalize_cb', '_acc_cb');
		atomic_form();
		break;
	case "install":
		include $d_root.'admin/classes/install.php';

		$install=new Install("language");
		CMSResponse::Redir('admin.php?com_option=language', $install->go());
		break;
	case "delete":
		include $d_root.'admin/classes/uninstall.php';
		$uninstall=new UnInstall("language");
		$lang = in('cid', __ARR0|__PATH, $_POST, 2);
		$lang = strtolower($lang);
		if ($lang == 'en') {
			CMSResponse::Redir('admin.php?com_option=language', _LANGUAGE_CANNOT_REMOVE_BASE);
		}
		$uninstall->name($lang);
		CMSResponse::Redir('admin.php?com_option=language', $uninstall->go());
		break;
	case "info":
		if (null === ($lang = in('cid', __ARR0|__PATH, $_GET)))
			break;
		info_language($lang);
		break;
	case "new":
		include $d_root.'admin/classes/install.php';
		$web_path = in_raw('web_path', $_REQUEST, '');
		if ($web_path!='')
			$web_path = remote_update($web_path);
		else
			$web_path = 'http://';

		Install::install_interface("admin.php?com_option=language",_LANG_INSTALL,$web_path);
		break;
	case 'edit':
		$lang = in('cid', __PATH | __ARR0, $_REQUEST);
		if (isset($lang))
			edit_language($lang);
		break;
	case 'create':
		$lang = in('cid', __PATH | __ARR0, $_POST, 'en');
		new_language($lang);
		break;
	case 'clone':
		if (null === ($lang_base = in_path('lang_base', $_POST)) ||
			null === ($lang_cc = in_path('lang_cc', $_POST)) ||
			null === ($lang_name = in('lang_name', __NOHTML, $_POST)) ||
			null === ($lang_author = in('lang_author', __NOHTML, $_POST))
		) CMSResponse::Back(_FORM_NC);

		include_once $d_root.'admin/classes/fs.php';
		$fs = new FS();

		if (file_exists($d_root.'lang/'.$lang_cc.'/'))
			CMSResponse::Back(sprintf(_LANGUAGE_ALREADY_EXISTS, raw_strtoupper($lang_cc)));
		$fs->mkdir($d_root.'lang/'.$lang_cc.'/');
		$lang_version = in('lang_version', __SQL | __NOHTML, $_POST, '', 20);
		$lang_email = in('lang_email', __SQL | __NOHTML, $_POST, '');
		
		txcopy($fs, $d_root.'lang/'.$lang_base.'/', $d_root.'lang/'.$lang_cc.'/' );
		$fs->copy($d_root.'lang/'.$lang_base.'/common.php', $d_root.'lang/'.$lang_cc.'/common.php');
//		$fs->copy($d_root.'lang/'.$lang_base.'/user.php', $d_root.'lang/'.$lang_cc.'/user.php');

		$base_files = parse_lang_xml('en', $lang_base);
		if (!$base_files) {
			$echo_cb(sprintf(_LANGUAGE_CANNOT_FIND, $lang_base));
			return -1;
		}
		write_language_descriptor($lang_cc, $lang_name, $lang_version, $lang_author, $lang_email, $base_files);
		
		//edit_language($lang_cc);
		CMSResponse::Redir('admin.php?com_option=language');
	break;
	case 'unpublish':
		// get all the language ids, properly formatted
		$cid = array_map('strtolower', in('cid', __ARR|__PATH, $_REQUEST, 2));
		$mod = false;
		// check that each language id is not already disabled
		foreach($cid as $lang) {
			if (strpos($d_dlangs, $lang) === false) {
				$d_dlangs .= ','.$lang;
				$mod = true;
			}
		}
		if ($mod) {
			$d->cfg->SetVar('d_dlangs', $d_dlangs);
			$d->cfg->Save();
		}
		CMSResponse::Back();
	break;
	case 'publish':
		// get all the language ids, properly formatted
		$cid = array_map('strtolower', in('cid', __ARR|__PATH, $_REQUEST, 2));
		$ol = strlen($d_dlangs);
		$disabled = $d_dlangs;
		// check that each language id is not already disabled
		foreach($cid as $lang) {
			$disabled = str_replace(','.$lang, '', $disabled);
		}
		if (strlen($disabled) != $ol) {
			$d->cfg->SetVar('d_dlangs', $disabled);
			$d->cfg->Save();
		}
		CMSResponse::Back();

	default:
		language_table(); break;
}

?>