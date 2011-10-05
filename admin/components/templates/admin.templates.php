<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once com_path("html");

switch($task) {
	case "publish":
		if (null !== ($newtemplate = in('cid', __ARR0 | __PATH, $_POST))) {
			global $d_template;
			$d->setTemplate(strtolower($newtemplate));
			$d->cfg->setVar('d_template',$d_template);
			$d->cfg->Save();
			CMSResponse::Redir('admin.php?com_option=templates');
		}
	break;
	case 'save':
		if (('' === ($template = in_path('template', $_POST, ''))) ||
			('' === ($template_html_data = in_raw('template_html_data', $_POST, ''))) ||
			('' === ($template_css_data = in_raw('template_css_data', $_POST, ''))) )
			CMSResponse::Redir('admin.php?com_option=templates', _FORM_NC);
		$html_file=$d_root."templates/".$template."/index.php";
		$css_file=$d_root."templates/".$template."/template.style.css";
		include $d_root.'admin/classes/fs.php';
		$fs = new FS();
		$fs->put_contents($html_file,$template_html_data);
		$fs->put_contents($css_file,$template_css_data);
		CMSResponse::Redir('admin.php?com_option=templates');
	break;
	case "install":
		include $d_root.'admin/classes/install.php';
		$install = new Install("template");
		CMSResponse::Redir('admin.php?com_option=templates', $install->go());
	break;
case "delete":
	include $d_root.'admin/classes/uninstall.php';
	if ('' !== ($template = in('cid', __ARR0 | __PATH, $_POST, ''))) {
		$uninstall=new UnInstall("template");
		$uninstall->name(strtolower($template));
		CMSResponse::Redir('admin.php?com_option=templates', $uninstall->go());
	}
	break;
case "info" :
	if ('' !== ($template = in('cid', __ARR0 | __PATH, $_GET, '')))
		info_template($template);
	break;
case "new" :
	global $d_root;
	include $d_root.'admin/classes/install.php';
	$web_path = in_raw('web_path', $_REQUEST, '');
	if ($web_path!='')
		$web_path = remote_update($web_path);
	else
		$web_path = 'http://';
		
	Install::install_interface("admin.php?com_option=templates&option=install",_TEMPLATES_INSTALL, $web_path);

	break;
case "edit" :
	if ('' !== ($template = in('cid', __ARR0 | __PATH, $_POST, '')))
		edit_template($template);
	break;
default:
	templates_table();
	break;
}

?>