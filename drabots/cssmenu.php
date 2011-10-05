<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Admin CSS menu

$_DRABOTS->registerFunction( 'OnAdminMenuContent', 'cssmenu_content' );

$_DRABOTS->registerFunction( 'OnAdminMenuHead', 'cssmenu_head' );

$_DRABOTS->registerFunction( 'OnAdminMenuAlternate', 'cssmenu_alternate' );

function cssmenu_head($stand_alone) {
	global $d_subpath;
	// stand-alone is ignored here
	//TODO: customize the menu.css file
	//L:NOTE: I suppose that a static menu.css is OK for our menu, is it?
	return array('<style type="text/css">@import url("'.$d_subpath.'admin/includes/css/menu.css");</style>', array());
}

function cssmenu_content($stand_alone) {
	if ($stand_alone) {
		// the CSS menu cannot be hosted on a separate file
		CMSResponse::Unavailable();
		return;
	}
	
	echo cssmenu_alternate();
}

function cssmenu_alternate() {

	global $d_root;
	
	// this is our custom menu renderer class
	include_once $d_root.'admin/classes/cssmenu.php';

	$main_menu = new CSSMenu('CSSMenuNode');
	
	AdminMenu::FillMenu($main_menu);

	return '<!-- CSS menu starts here -->'.
			$main_menu->generate().
		'<!-- CSS menu ends here -->';
}

?>