<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Service component
# @author legolas558
#
# allows core webservices

$service = in_raw('service', $_GET);

switch ($service) {
	default:
		CMSResponse::BadRequest();
	break;
	case 'captcha':
		$prefix = in_path('pfx', $_GET, '');
		if (!$params->get('captcha', 1)) {
			CMSResponse::Unavailable();
			break;
		}
		$_DRABOTS->loadBotGroup('captcha');
		$r = $_DRABOTS->trigger('OnCaptchaGenerate', array($prefix), 1);
		if (empty($r))
			CMSResponse::Unavailable();
	break;
	case 'xmlrpc':
		if (!$params->get('xmlrpc', 0)) {
			CMSResponse::Unavailable();
			break;
		}
		require $d_root.'classes/xmlrpc/functions.php';
		require $d_root.'classes/xmlrpc/wrappers.php';
		require $d_root.'classes/xmlrpc/server.php';
		//TODO: remoteblog should use this code?
	break;
	case 'admin_menu':
		// always disallow access to menu when not logged in
		if ($my->gid<3) {
			CMSResponse::Unauthorized('', false);
			break;
		}
		include $d_root.'admin/classes/adminmenu.php';
		CMSResponse::NoCache();
		AdminMenu::Head(true);
		AdminMenu::Content(true);
	break;
/*	case 'basejs':
		// base javascript used by all websites
		// currently used by WYSIWYG editors to localize website root
		header('Content-Type: text/javascript');
		header('Cache-Control: public');
		echo 'var _d_subsite_path = "'.$d->SubsitePath().'";';
	break;	*/
}

?>