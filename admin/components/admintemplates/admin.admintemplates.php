<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

//L: include also the client side language resources
$path = com_lang($my->lang, 'templates');
include $path;

require com_path('html');

switch($task) {
	case "publish":
		if (null !== ($cid = in('cid', __ARR | __PATH, $_POST))) {
			$d->cfg->setVar('d_atemplate',strtolower($cid[0]));
			$d->cfg->Save();
			CMSResponse::Redir('admin.php?com_option=admintemplates');
		}
	break;
	case 'save':
		if (('' === ($atemplate = in_path('atemplate', $_POST, ''))) ||
			('' === ($template_html_data = in_raw('template_html_data', $_POST, ''))) ||
			('' === ($template_css_data = in_raw('template_css_data', $_POST, ''))) ||
			('' === ($template_css_data_ui = in_raw('template_css_data_ui', $_POST, ''))) )
			CMSResponse::Redir('admin.php?com_option=admintemplates', _FORM_NC);
		$html_file=$d_root."admin/templates/".$atemplate."/index.php";
		$css_file1=$d_root."admin/templates/".$atemplate."/css/template.style.css";
		$css_file2=$d_root."admin/templates/".$atemplate."/css/ui.style.css";
		include $d_root.'admin/classes/fs.php';
		$fs = new FS();
		$fs->put_contents($html_file,$template_html_data);
		$fs->put_contents($css_file1,$template_css_data);
		$fs->put_contents($css_file2,$template_css_data_ui);
		CMSResponse::Redir('admin.php?com_option=admintemplates');
	break;
	case "install":
		include $d_root.'admin/classes/install.php';
		$install=new Install("template");
		CMSResponse::Redir('admin.php?com_option=admintemplates', $install->go());
	break;
case "delete":
		include $d_root.'admin/classes/uninstall.php';
		if ('' !== ($atemplate = in('cid', __ARR0 | __PATH, $_POST, ''))) {
			$uninstall=new UnInstall("template");
			$uninstall->name(strtolower($atemplate));
			CMSResponse::Redir('admin.php?com_option=admintemplates', $uninstall->go());
		}
	break;
case "info" :
	if ('' !== ($atemplate = in('cid', __ARR0 | __PATH, $_GET, '')))
		info_template($atemplate);
	break;
case "new" :
	global $d_root;
	include $d_root.'admin/classes/install.php';
	$web_path = in_raw('web_path', $_REQUEST, '');
	if ($web_path!='')
		$web_path = remote_update($web_path);
	else
		$web_path = 'http://';
	Install::install_interface("admin.php?com_option=admintemplates",_TEMPLATES_INSTALL,$web_path);

	break;
case "edit" :
	if ('' !== ($atemplate = in('cid', __ARR0 | __PATH, $_POST, '')))
		edit_template($atemplate);
	break;
default: templates_table(); break;

}

?>