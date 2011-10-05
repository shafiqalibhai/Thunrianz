<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$mod_type=$module['module'];
global $$mod_type, $access_sql;

if(!isset($$mod_type)) {

	$$mod_type=true;
	function getmenulink($menu,$menutype='') {
		global $d_type,$conn,$d_seo;
		
		// this field is set before others because of link cloning
		$name=$menu['name'];
		
		switch ($menu['link_type']) {
			case 'separator':
			case 'url':
/*				break;
			case 'url':
			if (eregi( "index.php\?", $menu['link'] )) {
				if (!eregi( "Itemid=", $menu['link'] )) {
					$menu['link'] .= "&amp;Itemid=".$menu['id'];
				}	
			} */
			break;
			case 'cl':
				global $access_sql;
				// the link field is used to store the cloned menu item id
				$name = $menu['name'];
				$menu=$conn->SelectRow('#__menu', '*', ' WHERE id='.$menu['link'].' '.$access_sql);
				// the cloned menu link has not enough rights
				if (empty($menu))
					return;
				// clone recursively
				if ($menu['link_type'] == 'cl')
					return getmenulink($menu, 'cl');
/*				if ($crow) {
					$menu['link']=getmenulink($crow, $menutype);
					return $menu['link'];
				} */
			break;
			case 'wrapper':
				$menu['link'] = 'index.php?option=wrapper&Itemid='.$menu['id'];
			break;
			case 'ci':
				$menu['link'] .= '&Itemid='.$menu['id'];
				if ($d_seo)
					$menu['link'] .= '&'.substr(content_sef($menu['name']), 5);
				break;
			default:
				$menu['link'] .= '&Itemid='.$menu['id'];
			break;
		}

		if($d_type=="html") {
			$menuclass = 'menu_mainlevel';
			if ($menu['parent'] > 0)
				$menuclass = 'menu_sublevel';
		}

		if (strlen($menuclass))
			$class = "class=\"$menuclass\"";

		$link=$menu['link'];
		$active="";
		global $Itemid;
		if (($Itemid==$menu['id']) && ($menutype!='topmenu')) $active =  "id=\"active_menu\"";
		$xlink = xhtml_safe($link);
		switch ($menu['browsernav']) {
			case 1:
			// open in new window
			$txt = "<a href=\"$xlink\" target=\"_blank\" $class $active title=\"$name\">$name</a>";
			break;

			case 2:
			// open in popup
			global $d;
			// 780, 550
			$txt = "<a href=\"#\" onclick=\"javascript:".$d->popup_js("'".$xlink."'", 600, 400)."return false;\" $class $active title=\"$name\">$name</a>".$d->alternate_url($link, $name);
			break;

			case 3:
			// no link, separator
			$txt = "<span $class $active title=\"$name\">$name</span>";
			break;

			default:
			// normal link
			//TODO: xhtml_safe() application!
			$txt = "<a href=\"$xlink\" $class $active title=\"$name\">$name</a>";
			break;
		}
		return $txt;
	}

	function showmenu($menutype='mainmenu',$type='vertical')	{
	global $conn,$d_root, $d_subpath,$Itemid,$access_sql,$d_type;

	$indent_pic = template_pic('indent1.png');
		
	if($d_type=="html")	{
		if($type=='vertical')	{
		$indents = array(
			// block prefix / item prefix / item suffix / block suffix
			array( '', '<div>', '</div>', '' ),
			array( '', '<div>'. $indent_pic , '</div>', '' )	);
		} else if($type=='horizontal') {
		$indents = array(
			array( '', '<div>', '</div>', '' ),
			array( '', '<div>', '</div>', '' ),	//	array( "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\"><tr align=\"left\">", "<td>" , "</td>", "</tr></table>" )
			);
		} else if($type=='flat_list') {
			$indents = array(
				array( "\n<ul class=\"menu_mainlevel\">", "\n<li>" , "</li>", "\n</ul>" ),
				array( "\n<ul class=\"menu_mainlevel\">", "\n<li>" , "</li>", "\n</ul>" )	// copied from above
				);
		}
	}

	$rsa = $conn->SelectArray('#__menu', '*', " WHERE menutype='$menutype' AND parent=0 $access_sql ORDER BY ordering ");

	// menu start
	echo $indents[0][0];
	foreach($rsa as $row) {
		echo $indents[0][1].getmenulink($row,$menutype);

		if ($row['sublevel']>0) {
			// apriori display evaluation: show when sublevel is of type 1 (always) OR when this parent item is selected
			$show = ($row['sublevel'] == 1) || ($row['id'] == $Itemid);
			$rs1a=$conn->SelectArray('#__menu', '*',
							' WHERE parent='.$row['id']." $access_sql ORDER BY ordering");
			if (!$show) {
				// when sublevel = 2 and the parent menu not selected check if any child is selected
				foreach ($rs1a as $mrow) {
					if ($mrow['id'] == $Itemid) {
						$show = true;
						break;
					}
				}
			}
			if($show) {
				foreach($rs1a as $row1) {
					echo $indents[1][1].getmenulink($row1,$menutype).$indents[1][2];
				}
			}
		}
		echo $indents[0][2];
	}

	echo $indents[0][3];
	// menu
	}
}

$menutype = $params->get('menutype', 'mainmenu');
$menu_style = $params->get('menu_style', 'vertical');

showmenu($menutype,$menu_style);
?>