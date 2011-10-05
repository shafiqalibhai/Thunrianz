<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$task = in_raw('task', $_REQUEST, 'view');
$menutype = in_raw('menutype', $_REQUEST, false);

require_once(com_path("html"));

// menu categories handling
if (!$menutype) {
    switch($task) {
        case "delete":
		$cid = in_arr('cid', __NUM, $_REQUEST);
		foreach($cid as $id) {
			$row = $conn->SelectRow('#__categories', 'name', ' WHERE id='.$id);
			$conn->Delete('#__menu', ' WHERE menutype=\''.sql_encode($row['name'])."'");
		}
		$easydb->delete_np("categories", $cid,"section='com_menu'");
		CMSResponse::Redir("admin.php?com_option=menu");
        	break;
        case "create" :
		$menu_title = in_sql('menu_title', $_POST, '', 255);
		$menu_access = in_num('menu_access', $_POST);
		if (!strlen($menu_title) || !isset($menu_access))
			CMSResponse::Redir("admin.php?com_option=menu", _FORM_NC);
		$conn->Insert('#__categories', '(name,section,access)', "'".$menu_title."', 'com_menu',$menu_access");
		CMSResponse::Redir("admin.php?com_option=menu");
		break;
        case "new":
		create_menu();
		break;
	case 'massop':
		$easydb->MassOp('categories','admin.php?com_option=menu', 'section=\'com_menu\'');
	break;
        default:
		menu_table();
    }
} else {
    //this is common to save and new 
/*	$module_params='';
	$vars = in_prefix('param_', __RAW, $_POST);
	if (isset($vars)) {
		foreach ($vars as $var => $val)
			$module_params.=$var.'='.$val."\n";
	}*/
	//SECURITY HERE PLEASE
	$module_params = in_prefix('param_', __RAW, $_POST);
	if (isset($module_params))
			$module_params = sql_encode(serialize($module_params));
	else
		$module_params = serialize(array());

	switch($task) {
		case 'massop':
			$easydb->MassOp('menu', 'admin.php?com_option=menu&menutype='.$menutype);
			break;
		case "delete":
			$cid = in_arr('cid', __NUM, $_REQUEST);
			$easydb->delete_np("menu", $cid, "menutype='$menutype'");
			CMSResponse::Redir("admin.php?com_option=menu&menutype=$menutype");
			break;
        case "orderup":
        case "orderdown":
        case "reorder":
        case "publish":
        case "unpublish":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
       		$easydb->data_table($cid, $task, "menu","admin.php?com_option=menu&menutype=$menutype","menutype='$menutype'");
       		break;
        case "save":
		//FIXME: XHTML encoding of menu name field
		$link_name = in_sql('link_name', $_POST, '', 100);
		$link_url = in_sql('link_url', $_POST, '');
		$omt = in_sql('link_omenutype', $_POST);
		$link_menutype = in_sql('link_menutype', $_POST);
		$link_type = in_raw('link_type', $_POST);
		$olink_parent = in_num('olink_parent', $_POST);
		$link_parent = in_num('link_parent', $_POST, 0);
		$link_browsernav = in_num('link_browsernav', $_POST, 0);
		$link_access = in_num('link_access', $_POST);
		$link_id = in_num('link_id', $_POST);
		$menu_id = in_num('menu_id', $_POST);
		
		//TODO: validate $link_name and $link_url!
		$link_sublevel = in_num('link_sublevel', $_POST);
		$slsql = ',sublevel='.$link_sublevel;
	
//		$module_params = sql_encode($module_params);
		// cancel the parent menu item if the category is changing
		if ($omt !== $link_menutype)
			$link_parent = 0;
		$mt = 'menutype=\''.$link_menutype."',";
		if($link_type == "cc" || $link_type == "cs" || $link_type == "ci" || $link_type == "cb") {
		    $m_name= '';
		    $link ='';
		    $component_id = '';
		    get_link($link_type,$menu_id,$link,$m_name,$component_id);
		    $conn->Execute("UPDATE #__menu SET $mt name='".$m_name."',link='$link',parent=$link_parent,componentid=$component_id,access=$link_access,params='$module_params' $slsql WHERE  id=$link_id");
		} else if ($link_type == 'separator') {
		    $conn->Update('#__menu',  "$mt name='$link_name',parent=$link_parent,access=$link_access $slsql", " WHERE  id=$link_id");
		} else if ($link_type == 'url') {
		    $conn->Execute("UPDATE #__menu ".
		    		"\nSET $mt name='$link_name',link='$link_url',parent=$link_parent,browsernav=$link_browsernav,access=$link_access $slsql WHERE  id=$link_id");
		} else if ($link_type == 'cl') {
		    $conn->Execute("UPDATE #__menu ".
		    		"\nSET $mt name='$link_name',link='$link_url',parent=$link_parent,access=$link_access $slsql WHERE id=$link_id");
		} else if ($link_type == 'wrapper') {
		    $conn->Execute("UPDATE #__menu SET $mt name='$link_name',link='$link_url',parent=$link_parent,access=$link_access,params='$module_params' $slsql WHERE  id=$link_id");
		} else if ($link_type == 'component' ) {
		    $conn->Execute("UPDATE #__menu ".
		    		"\nSET $mt name='$link_name',parent=$link_parent,access=$link_access,params='$module_params' $slsql WHERE  id=$link_id");
		}
		
		if($link_parent!=$olink_parent) {
		// set the sublevel flag for parent
		if($link_parent!=0) {
		        $conn->Update('#__menu', 'sublevel=1', " WHERE id=$link_parent");
		}
		
		// set the sublevel to 0 for previous parent if it has no other submenus
		if($olink_parent!=0) {
		        $row=$conn->SelectRow('#__menu', 'id', " WHERE parent=$olink_parent");
			// reset the sublevel
		        if (empty($row))
				$conn->Update('#__menu', 'sublevel=0', " WHERE id=$olink_parent");
		    }
		}
		
		CMSResponse::Redir("admin.php?com_option=menu&menutype=$menutype");		
		break; 
        case "create": {
		$order=$easydb->neworder("menu","menutype='$menutype'");
		$publish=0;
		$link_name = in_sql('link_name', $_POST, '', 100);
		$link_url = in_sql('link_url', $_POST, '');
		$omt = in_sql('link_omenutype', $_POST);
		$link_menutype = in_sql('link_menutype', $_POST);
		$link_type = in_raw('link_type', $_POST);
		$olink_parent = in_num('olink_parent', $_POST,0);
		$link_parent = in_num('link_parent', $_POST,0);
		$link_access = in_num('link_access', $_POST);
		$link_browsernav = in_num('link_browsernav', $_POST);
		$link_id = in_num('link_id', $_POST);
		if (!isset($link_type)) {
			CMSResponse::Back(_FORM_NC);
			break;
		}
		if($link_type == "cc" || $link_type == "cs" || $link_type == "ci" || $link_type == "cb") {
				$menu_id = in_num('menu_id', $_POST);
				if (!isset($menu_id)) {
					CMSResponse::Back(_FORM_NC);
					break;
				}

		    $m_name= '';
		    $link ='';
		    $component_id = '';
		    get_link($link_type,$menu_id,$link,$m_name,$component_id);
		    if ($link_type=='ci') $module_params=serialize(array());
			$conn->Insert('#__menu',
	  				'(menutype,name,link,link_type,parent,componentid,ordering,access,params)',
					"'".$link_menutype."','".$m_name."','".$link."','".
					$link_type."',$link_parent,$component_id,$order,$link_access,'$module_params'");
		} else if ($link_type == 'separator') {
				// browsernav = 3, this is a separator
				$conn->Insert('#__menu',
	  				'(menutype,name,link_type,parent,ordering,browsernav,access)',
					"'".$link_menutype."','".$link_name."','".
					$link_type."',$link_parent,$order,3,$link_access");
		} else if ($link_type == 'url') {
				$conn->Insert('#__menu',
	  				'(menutype,name,link,link_type,parent,ordering,browsernav,access)',
					"'".$link_menutype."','".$link_name."','".
					$link_url."', '".$link_type."',$link_parent,$order,$link_browsernav,$link_access");
		} else if (($link_type == 'wrapper') || ($link_type == 'cl')) {
				$conn->Insert('#__menu',
	  				'(menutype,name,link,link_type,parent,ordering,access,params)',
					"'".$link_menutype."','".$link_name."','".
					$link_url."','".$link_type.
					"',$link_parent,$order,$link_access,'$module_params'");
		} else if ($link_type == 'component' ) {
			$link_componentid = in_num('link_componentid', $_POST);
			if (!isset($link_componentid))
					CMSResponse::Back(_FORM_NC);
			$rsa=$conn->SelectRow('#__components', 'id,link,option_link', ' WHERE id='.$link_componentid);
			$link_url = sql_encode("index.php?".$rsa['link']);
			$conn->Insert('#__menu', '(menutype,name,link,link_type,parent,componentid,ordering,access,params)',
		    	"'$link_menutype','$link_name','$link_url','$link_type',$link_parent,$link_componentid,$order,$link_access,'$module_params'");
			$iid = $conn->Insert_ID();
		}
		if($link_parent!=0)
		    $conn->Execute("UPDATE #__menu SET sublevel=1 WHERE id=$link_parent");
		// redirect to edit page in case of component
		if ($link_type == 'component') {
			// check if component as any XML parameter first
			$com_name = substr($rsa['option_link'], 4);
			if (ScriptedUI::computexmlparams($d_root.'components/'.$com_name.'/'.$com_name.
					'.xml')) {
				CMSResponse::Redir('admin.php?com_option=menu&menutype='.$menutype.'&task=edit&cid[]='.$iid.'&item_type=component');
				break;
			}
		}
		CMSResponse::Redir("admin.php?com_option=menu&menutype=$menutype");
		break;
        }
        case "edit":
		$id = in('cid', __ARR0 | __NUM, $_REQUEST);
		if (isset($id))
			edit_menu($id);
		break;
	case "new" :
		$item_type = in_raw('item_type', $_REQUEST);
		// null is ok here
		edit_menu(null, $item_type);
		break;
	case 'view':
        default:
		menutype_table();
	break;
} 
    
}

/* //DEAD CODE?
function add_child($parent) {
    global $conn;
    $row=$conn->GetRow("SELECT id,sublevel FROM #__menu WHERE id=$parent");
    if($row['sublevel']==0){
	trigger_error('NOT YET IMPLEMENTED');
    }
}

function remove_child($parent){
	trigger_error('NOT YET IMPLEMENTED');
}
*/

function get_link($link_type,$menu_id,&$link,&$m_name,&$component_id) {
    global $conn;
    $rs=$conn->SelectRow('#__components', 'id', " WHERE option_link= 'com_content'");
    $component_id = $rs['id'];

    if($link_type=="cc") { 
        $rs=$conn->SelectRow('#__categories', 'id,name', " WHERE id= $menu_id");
        $m_name=$rs['name'];
        $link="index.php?option=content&task=category&id=".$menu_id;
    } else if($link_type=="cb") { 
        $rs=$conn->SelectRow('#__categories', 'id,name', " WHERE id= $menu_id");
        $m_name=$rs['name'];
        $link="index.php?option=content&task=showblog&id=".$menu_id;
    } else if($link_type=="cs") {
        $rs=$conn->GetRow("SELECT id,title FROM #__sections WHERE id= $menu_id");
        $m_name=$rs['title'];
        $link="index.php?option=content&task=section&id=".$menu_id;
    } else if($link_type=="ci") {
        $rs=$conn->GetRow("SELECT id,title_alias FROM #__content WHERE id= $menu_id");
        $m_name=$rs['title_alias'];
        $link="index.php?option=content&pcontent=1&task=view&id=".$menu_id;
    }
}

?>