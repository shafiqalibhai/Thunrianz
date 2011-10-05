<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## component.php
# @author legolas558
#
# finds the default active component or that specified by the active menu (Itemid) and launches it
#

$option = in_path('option', $_GET, null, 36);

// find default menu item
if (!isset($option)) {
	$row = $conn->SelectRow('#__menu', '*', " WHERE menutype='mainmenu' ORDER BY ordering ASC");
	if (!$row) {
		CMSResponse::NotFound();
		return;
	}
	$arr=explode('?',$row['link'],2);
	$p=strpos($row['link'],'?');
	if($p!==false) {
		parse_str(substr($row['link'], $p+1), $_GET);
		foreach($_GET as $k => $v) {
			if (!isset($_POST[$k]))
				$_REQUEST[$k] = $v;
		}
	}
	// below line was inside previous block
	$option = in_path('option', $_GET, null, 36);
	
	$Itemid = $row['id'];
//	if (!($option = in_path('option', $_GET)))			CMSResponse::unauthorized();
}

//TODO: cache system should be plugged here

// default component container	
if ($d_type=='html')
	echo "\n".'<div class="dk_component dkcom_'.xhtml_safe($option).'">'."\n";

if (isset($option)) {

	$main = $d_root.'components/'.$option.'/'.$option.'.php';
	if (is_file($main) ) {
		
		// load the related CSS file if exists
		$css_file = 'components/'.$option.'/'.$option.'.style.css';
		if (file_exists($d_root.$css_file))
			$d->add_css($css_file); 
		// load the custom CSS file in template (override) if exists	
		if (file_exists($d_root.'templates/'.$d_template.'/components/'.$option.'.style.css'))
			$d->add_css('templates/'.$d_template.'/components/'.$option.'.style.css');

		$params = $d->GetComponentParams($option);

		$path = com_lang($my->lang);
		if (file_exists($path)) {
			// removed 'once'
			include $path;
		}
		
		require $main;
	} else {
		// Unauthorized because you cannot access a component which does not exist
		// inconsistent in case of menu instances removal vs old links
		CMSResponse::Unauthorized();
		return;
	}
}

if ($d_type=='html')
	echo "\n</div>\n";
?>