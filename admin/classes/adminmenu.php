<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Admin backend menu class
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# base class for menu generation
#

// they should stand here - shared
include $d_root.'lang/'.$my->lang.'/admin/admin.php';
include $d_root.'admin/classes/menu.php';
include $d_root.'admin/includes/admin_functions.php';

// admin menu functions
// each function returns an array ($content, array($headers...))
// if the function is called in the stand-alone mode then it can quit the response (calling exit();)
// OnAdminMenuHead is always called before OnAdminMenuContent
// OnAdminMenuHead is called in the <head /> tag of the response, when there is an XHTML response
// otherwise it is simply called before OnAdminMenuContent

class	AdminMenu {

	function	Head($stand_alone = false) {
		return AdminMenu::_admin_menu_trigger('OnAdminMenuHead', $stand_alone);
	}
	function Content($stand_alone = false) {
		return AdminMenu::_admin_menu_trigger('OnAdminMenuContent', $stand_alone);
	}

	function	_admin_menu_trigger($fn, $stand_alone = false) {
		global $_DRABOTS;
		$_DRABOTS->loadCoreBotGroup('admin_menu');
		$r = $_DRABOTS->trigger($fn, array($stand_alone), 1);
		$content = null;
		if (isset($r[0])) {
			list($content, $headers) = $r[0];
			// send all headers
			foreach($headers as $header) {
				header($header);
			}
		}
		return $content;
	}
	
function FillMenu(&$main_menu) {
	global $conn, $my, $d_subpath;

// get all the available components
$available = $conn->SelectColumn('#__components', 'option_link', ' WHERE admin_access<='.$my->gid);

//3. SYSTEM menu
if (in__array($available, 'com_system')) {
	$menu =& $main_menu->iadd(_AMENU_SYSTEM,admin_template_pic('sysinfo.png'));

if (in__array($available, 'com_config'))
	$menu->iadd(_AMENU_MYSITE_GCONFIG,admin_template_pic('config.png'),
				'admin.php?com_option=config');

if(in__array($available, 'com_database')){
	$submenu =& $menu->iadd(_DATABASE,admin_template_pic('db.png'));
	$submenu->iadd(_DB_BACKUP_HEAD,admin_template_pic('component.png'),'admin.php?com_option=database');
	if ($my->is_admin())
		$submenu->iadd(_DB_QUERY,admin_template_pic('db.png'),'admin.php?com_option=database&option=query');
}
if (in__array($available, 'com_system'))
	$menu->iadd(_SYSTEM_TAR_BACKUP,admin_template_pic('tar.png'),'admin.php?com_option=backup');


$menu->add_split();

if (in__array($available, 'com_user'))
	$menu->iadd(_AMENU_MYSITE_USERS_MANAGE,admin_template_pic('edit.png'),'admin.php?com_option=user');

if (in__array($available, 'com_modules'))
	$menu->iadd(_MODULES_HEAD,admin_template_pic('edit.png'),'admin.php?com_option=modules&option=manage');
	
if(in__array($available, 'com_drabots')){
	$menu->iadd(_DRABOTS_HEAD,admin_template_pic('edit.png'),'admin.php?com_option=drabots');
}

if ($d_subpath=='' && in__array($available, 'com_subsites')) {
	$submenu =& $menu->iadd(_AMENU_MANAGE_SUBSITES,admin_template_pic('edit.png'),'admin.php?com_option=subsites');
	$rsa = $conn->SelectArray('#__subsites', 'subpath');
	if (count($rsa)) {
//		$submenu->add_split();
		foreach($rsa as $row)
			$submenu->iadd($row['subpath'],admin_template_pic('subsites.png'),$row['subpath'].'admin.php', '', '_blank');
	}
}

if (in__array($available, 'com_massmail')){
	$menu->iadd(_AMENU_MYSITE_USERS_MAIL,admin_template_pic('email.png'),'admin.php?com_option=massmail');
}

if(in__array($available, 'com_templates')){
	$menu->iadd(_AMENU_MYSITE_TMANAGE_SITE,admin_template_pic('template.png'),'admin.php?com_option=templates');
	$menu->iadd(_AMENU_MYSITE_TMANAGE_ADMIN,admin_template_pic('template.png'),'admin.php?com_option=admintemplates');
}

if(in__array($available, 'com_language'))
	$menu->iadd(_AMENU_MYSITE_LMANAGE,admin_template_pic('i18n.png'),			
				 'admin.php?com_option=language');
				 
				 
$menu->iadd(_AMENU_MYSITE_PREVIEW,admin_template_pic('cpreview_new.png'),'index.php','','_blank');

/*
$submenu =& $menu->iadd(_AMENU_MYSITE_PREVIEW,admin_template_pic('preview.png'));

$submenu->iadd(_AMENU_MYSITE_PREVIEW_CUR,admin_template_pic('cpreview.png'),'index.php');
*/

}

// 4. MENU
if(in__array($available, 'com_menu')){
	$menu =& $main_menu->iadd(_MENU,admin_template_pic('menu.png'));
	$menu->iadd(_AMENU_MENU_MANGE,admin_template_pic('sections.png'),'admin.php?com_option=menu');
	$menu->add_split();

	$rsa=$conn->SelectArray('#__categories', 'id,name', " WHERE section='com_menu'");
	foreach($rsa as $row) {
		$menu->iadd($row['name'],admin_template_pic('menu.png'),
					'admin.php?com_option=menu&menutype='.$row['name']);
	}
}

if(in__array($available, 'com_content')){
	$menu =& $main_menu->iadd(_AMENU_CONTENTS,admin_template_pic('categories.png'));
	$menu->iadd(_FRONTPAGE_HEAD,admin_template_pic('home.png'),'admin.php?com_option=frontpage');
	// disallow section editing to publisher
	if ($my->gid >= 4)
		$menu->iadd(_AMENU_CONTENT_SECTIONS,admin_template_pic('sections.png'),'admin.php?com_option=content');
	$menu->add_split();

	global $access_acl;
	// display sections
	$rsa=$conn->SelectArray('#__sections', 'id,title,name,ordering,count', ' WHERE '.$access_acl.' ORDER BY ordering');
	// loop through all content sections
	foreach($rsa as $row) {
		$submenu =& $menu->iadd($row['title'],admin_template_pic('sections.png'),
								'admin.php?com_option=content&option=categories&sec_id='.$row['id']);
		if ($row['count']>0)
			$submenu->iadd('All '._AMENU_CONTENT_ITEMS.' in '.$row['title'],admin_template_pic('items.png'),
						'admin.php?com_option=content&option=items&sec_id='.$row['id']);
		if($row['count']>0) {
			// select archived content items
			$rwc = $conn->SelectCount('#__content' ,'id', ' WHERE sectionid='.$row['id']." AND published=4");
			if ($rwc)
				$submenu->iadd($row['title'].' '._AMENU_CONTENT_ARCHIVE,admin_template_pic('edit.png'),
							'admin.php?com_option=content&option=archive&sec_id='.$row['id']);
		}
		$submenu->add_split();
		// show the categories javascript menu item - filter out inaccessible and ineditable categories
		global $edit_sql, $access_sql;
		$crsa = $conn->SelectArray('#__categories', 'id,name', ' WHERE section=\''.$row['id']."' $edit_sql");
		foreach ($crsa as $crow) {
			$submenu->iadd($crow['name'], admin_template_pic('content_cat.png'),
						'admin.php?com_option=content&option=items&sec_id='.$row['id'].'&cid[]='.$crow['id']);
		}
	}
}

// get all components
$rsa = components_by_parent(0);

if (count($rsa)) {
	$menu =& $main_menu->iadd(_AMENU_COMPONENTS,admin_template_pic('component.png'));

	foreach($rsa as $row) {
//		if (($row['option_link']==="")
//			&& ($row['admin_menu_link']==='')
//			)
//			continue;
		$childs = components_by_parent($row['id']);
		if (isset($childs[0]))
			$url = '';
		else {
			if ($row['admin_menu_link']==='')
				continue;
			$url = "admin.php?".$row['admin_menu_link'];
		}
		$submenu =& $menu->iadd($row['name'],admin_template_pic('component.png'),$url,
						 $row['admin_menu_alt']);
		foreach($childs as $child) {
			$url = $child['admin_menu_link']!=='' ? "admin.php?".$child['admin_menu_link'] : '';
			$submenu->iadd($child['name'],admin_template_pic('component.png'),$url,$child['admin_menu_alt']);
		}
	}
}

//2. PACKAGES
if (in__array($available, 'com_system')) {
$menu =& $main_menu->iadd("Packages",admin_template_pic('install.png'));

if (in__array($available, 'com_system') && $my->is_admin() && ($d_subpath===''))
	$menu->iadd(_AMENU_INSTALL_TOOL,admin_template_pic('egg.png'),
			'admin.php?com_option=system&option=insttool');

if (in__array($available, 'com_system'))
	$menu->iadd("All Packages",admin_template_pic('kernel.png'),'admin.php?com_option=system&option=packages');

if (in__array($available, 'com_patch'))
	$menu->iadd(_PATCHES_INSTALL,admin_template_pic('kernel.png'),
			'admin.php?com_option=patch&task=new');

// only administrators can install components
if ($my->is_admin() && in__array($available, 'com_components'))
	$menu->iadd(_COMPONENTS,admin_template_pic('kernel.png'),'admin.php?com_option=components');

if(in__array($available, 'com_modules')){
	if ($my->is_admin())
		$menu->iadd(_MODULES,admin_template_pic('kernel.png'),'admin.php?com_option=modules&option=install');
}

if (in__array($available, 'com_system') && ($d_subpath===''))
		$menu->iadd(_AMENU_UPDATES, admin_template_pic('updates.png'),
				'admin.php?com_option=system&option=updates');
}

// 1. INFO MENU
$submenu =& $main_menu->iadd(_AMENU_INFORMATIONS, admin_template_pic('info.png'));

$submenu->iadd(_AMENU_HELP_LICENSE,admin_template_pic('gpl.png'),create_context_help_url('GPL'), '', '_blank');
$submenu->iadd(_AMENU_HELP_DOCUMENTATION,admin_template_pic('docs.png'),create_context_help_url(), '', '_blank');
$submenu->iadd(_AMENU_HELP_ABOUT,admin_template_pic('help.png'),'admin.php?com_option=about');

if (in__array($available, 'com_system')) {
	$submenu->add_split();
	// file logging - always visible
	global $_DRABOTS;
	$_DRABOTS->loadCoreBotGroup('logger');
	$r = $_DRABOTS->trigger('OnAdminMenuLogItem', array(), -1);

	if (isset($r) && ($r==true))
		$submenu->iadd(_SYSTEM_LOG, admin_template_pic('log.png'), 'admin.php?com_option=system&option=log');

	$submenu->iadd(_SYSTEM_PHP_INFO,admin_template_pic('phpinfo.png'),'admin.php?com_option=system&option=info');

	$submenu->iadd(_SYSTEM_VERSION_INFO,admin_template_pic('version.png'),'admin.php?com_option=system&option=version');
}

//6. STAND-ALONE BUTTONS
//$menu =& $main_menu->iadd(_AMENU_HELP,admin_template_pic('help.png'));

$main_menu->iadd(_AMENU_MYSITE_FRONTEND,admin_template_pic('preview.png'),'index.php');

$main_menu->iadd(_AMENU_MYADMIN_HOME,admin_template_pic('home.png'),'admin.php');

}

}

function in__array(&$arr, $needle) {
	return in_array($needle, $arr);
}

function components_by_parent($parent_id) {
	global $my, $conn;
	return $conn->SelectArray('#__components', 'id,name,admin_menu_link,admin_menu_alt,option_link'," WHERE iscore=0 AND option_link<>'' AND parent=$parent_id AND admin_access<=".$my->gid." ORDER BY name");
}

?>