<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Admin template class
# @author legolas558
#
# offers facilities for code embedding in admin backend templates

$_DRABOTS->registerFunction( 'OnAdminMenuContent', 'jsmenu_content' );

$_DRABOTS->registerFunction( 'OnAdminMenuHead', 'jsmenu_head' );

	function	jsmenu_head($stand_alone) {
		global $my, $d_subpath;
		if (!$stand_alone)
			return array('<script language="javascript" type="text/javascript">
var cmThemeDefaultBase = "'.$d_subpath.'admin/templates/default/images/";
</script>
<script language="javascript" src="'.$d_subpath.'admin/templates/default/js/JSCookMenu.js" type="text/javascript"></script>
<script language="javascript" src="index2.php?option=service&amp;service=admin_menu&amp;no_html=1&amp;lang='.$my->lang.'" type="text/javascript"></script>
<script language="javascript" src="'.$d_subpath.'admin/templates/default/js/ThemeDefault/theme.js" type="text/javascript"></script>
<link rel="stylesheet" href="'.$d_subpath.'admin/templates/default/js/ThemeDefault/theme.css" type="text/css" />', array());
		// stand-alone mode
		return array('', array('Content-Type: text/javascript', 'Cache-Control: public'));
	}
	
	function jsmenu_content($stand_alone) {
		// we return nothing because everything was done in the head in this case
		if ($stand_alone) {
			global $my;
			if (isset($_GET['lang'])) {
				if ($_GET['lang']!=$my->lang) {
					CMSResponse::Redir('index2.php?option=service&service=admin_menu&no_html=1&lang='.$my->lang);
					return;
				}
			}
			// output the inline content
			echo jsmenu_inline();
			// output must terminate here to prevent execution of further drabots
			exit();
		}
		// also prepare the alternate text
		$noscript = '';
		global $_DRABOTS;
		$r = $_DRABOTS->trigger('OnAdminMenuAlternate');
		if (isset($r[0]))
			$noscript = "\n<noscript>\n".$r[0]."\n</noscript>";
		else
		//TODO: internationalize!
			$noscript = "\n<noscript><big>"."Your browser does not have javascript support, please enable it or either ask the administrator to enable a non-javascript menu"."</big></noscript>";
		return array('<div id="myMenuID" style="margin-left: 15px;"></div>'."\n".
		'<script language="javascript" type="text/javascript">
			cmDraw ("myMenuID", myMenu, "hbr", cmThemeDefault, "ThemeDefault");
		</script>'.$noscript, array());
	}

function jsmenu_inline() {
	global $my, $d_root;

	// this is our custom menu renderer class
	include_once $d_root.'admin/classes/jsmenu.php';

	$main_menu = new JSMenu('JSMenuNode');
	
	AdminMenu::FillMenu($main_menu);

	return 'var myMenu = '.
		$main_menu->generate().
	// will comment spurious error messages
		"\n// ";
}

?>