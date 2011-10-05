<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$item_types=array(array("name"=>_MENU_TYPE_COMPONENT,"value"=>"component"),
					  array("name"=>_MENU_TYPE_LINK,"value"=>"url"),
					  array("name"=>_MENU_TYPE_ITEM,"value"=>"ci"),
					  array("name"=>_MENU_TYPE_SECTION,"value"=>"cs"),
					  array("name"=>_MENU_TYPE_CATEGORY,"value"=>"cc"),
					  array("name"=>_MENU_TYPE_BLOG,"value"=>"cb"),
					  array("name"=>_MENU_TYPE_CLINK,"value"=>"cl"),
					  array("name"=>_MENU_TYPE_SEPARATOR,"value"=>"separator"),
					  array("name"=>_MENU_TYPE_WRAPPER,"value"=>"wrapper")
					);

//L: re-indexes an array using $key
function ref_array($array,$key) {
	if( !is_array($array) )return false;
	if (!count($array)) return array();
	foreach($array as $row)$ret[ $row[$key] ]= $row;
	return $ret;
}

function menu_table() {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=menu");
	$gui->add('spacer');
	$gui->add("com_header",_MENU_HEAD);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>_NAME,'val'=>'name','len'=>'80%','ilink'=>'admin.php?com_option=menu&menutype=ivar1','ivar1'=>'name') ,
//						  array('title' => _COUNT, 'val' => 'count', 'len' => '8%', 'align' => 'center') ,
//						  array('title' => _ACCESS, 'val' => 'access', 'len' => '18%', 'align' => 'center') ,
						 );
	$table_data=$conn->SelectArray('#__categories', 'id,access,name', " WHERE section = 'com_menu'");
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();
}

function create_menu() {
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=menu");
	$gui->add("com_header",_MENU_CREATE_HEAD);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_MENU_CREATE_HEAD,"");
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","menu_title",_NAME,'',$v);
	$gui->add("access","menu_access",_ACCESS, 0);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

global $_comp_cache;
$_comp_cache = array();
function _get_component_by_id($id) {
	if (!isset($_comp_cache[$id])) {
		global $conn;
		$row = $conn->SelectRow('#__components', 'name', ' WHERE id='.$id);
		if (count($row)==0) {
//3			trigger_error('Cannot find component with id #'.$id);
			$_comp_cache[$id] = null;
		} else
			$_comp_cache[$id] = $row['name'];
	}
	return $_comp_cache[$id];
}

function menutype_table() {
	global $conn,$menutype,$item_types;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=menu&menutype=$menutype");
	$gui->add("com_header",sprintf(_MENU_MANAGE, $menutype));

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>_NAME,'val'=>'name','len'=>'60%','ilink'=>'admin.php?com_option=menu&menutype='.$menutype.'&task=edit&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_TYPE , 'val'=>'link_type' , 'len'=>'10%','align'=>'center') ,
						  array('title'=>_ACCESS,'val'=>'access','len'=>'10%','align'=>'center') ,
						  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center')
						 );
	$table_data=$conn->SelectArray('#__menu', 'id,name,link_type,parent,sublevel,ordering,access,componentid', " WHERE menutype = '$menutype' ".$gui->Ordering());

/*
	// create a conditional list to show warnings for deletion of component instances
	$in_js = '';
	$ijs=0;
	if (count($table_data=ref_array($table_data, 'id'))) {
		foreach($table_data as $row) {
			if($row['parent']!=0) {
				if (!isset($table_data[$row['parent']]))
					trigger_error('Could not find parent '.$row['parent'].' for menu id '.$row['id']);
				$table_data[ $row['id'] ]['name'] = $table_data[ $row['parent'] ]['name'] ." - &gt; ".$table_data[ $row['id'] ]['name'];
			}
			if ($row['link_type']=='component') {
				$comp_id = _get_component_by_id($row['componentid']);
				$in_js.="\ncase '".(++$ijs)."':\nreturn '\\n\\n".js_enc(sprintf(_MENU_INSTANCE_NOTICE, $comp_id))."';break;";
			}
		}
	}
	global $d;
	$d->add_raw_js("
	function postitem() {
		for (i=1;i<=".count($table_data).";i++) {
			var obj=document.getElementById('cb'+i);
			if (obj.checked) {
				var hl=document.getElementById('a'+i);
				document.location = hl.href;
				return;
			}
		}
		alert('".js_enc(_IFC_LIST_ERR)."');
	}");
	
	$d->add_raw_js('function _instance_notice(id) {
		switch (id) {
		'.$in_js.'
		default:
			alert(id);
		}		
		return \'\';
		}');
*/
	$replace = array( "link_type"=>$item_types);
//	array_walk($table_data, '_check_instance_exists');
	$table_data = gui_array_replace(array_values($table_data),$replace);
	
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
//	$gui->add('massops');
	$gui->add("end_form");
	$gui->generate();
}

function _check_instance_exists(&$val) {
	if ($val['link_type'] != 'component')
		return $val;
	$com_name = _get_component_by_id($val['componentid']);
	if (!isset($com_name)) {
		global $d_atemplate;
		$val['name'] = '<span style="color: red"><img src="admin/templates/'.$d_atemplate.'/images/warning.png" border="0" alt="[Warning]" />&nbsp;'.$val['name'].'</span>';
	}
}

function _add_move_box(&$gui, $menutype) {
	$menutype_arr=get_menutype_arr($menutype);
	$gui->add("hidden","link_omenutype",'',$menutype);
	$gui->add("select","link_menutype",_MENU_PARENT_MENU,$menutype_arr);
}

function edit_menu($id = null, $sel_item_type = null) {
	global $menutype,$conn, $item_types;

	// initialize the interface
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=menu&menutype=$menutype");

	// first time: show list of possible menu types
	if (!isset($id) && !isset($sel_item_type)) {
		// in case of new menu items
		$gui->add("com_header",_MENU_NEW_HEAD);
		$gui->add("tab_head");
		$gui->add("tab_simple","",_MENU_NEW_HEAD,"");
		$gui->add("hidden","task","","new");
		$item_type=select($item_types, 'component');
		$gui->add("list","item_type",_MENU_NEW_TYPE,$item_type,null,' size="10"');
		$button_arr = array(array('name'=>_NAV_NEXT , 'onclick'=>'javascript:document.adminform.submit()'));
		$gui->add("buttons","","",$button_arr);
		$gui->add("tab_end");
		$gui->add("tab_tail");
		$gui->add("end_form");
		$gui->generate();
		return;
	}
	
	if (isset($id) && !isset($sel_item_type)) {
        	$rsa=$conn->SelectRow('#__menu', 'link_type', " WHERE id=".$id);
        	$item_type=$rsa['link_type'];
	} else
		$item_type = $sel_item_type;
	
	$gui->add('spacer');

	switch($item_type) {
		case "component":
			component_gui($item_type, $gui, $id);
		break;
		case "cs" :
		case "ci" :
		case "cb" :
		case "cc" :
			gui($item_type, $gui, $id);
			break;
		case "url" :
		case "wrapper":
		case "cl":
		case "separator" :
			url_gui($item_type, $gui, $id);
		break;
        }
	$gui->add("end_form");
	$gui->generate();
}

// by legolas558
function get_menutype_arr($selection = null) {
	global $conn;
	$menus=$conn->SelectColumn('#__categories', 'name', " WHERE section='com_menu'");
	$a = array();
	foreach($menus as $menu) {
		$a_i = array('name' => $menu, 'value' => $menu);
		if ($menu===$selection)
			$a_i['selected'] = true;
		$a[] = $a_i;
	}
	return $a;
}

function _terminate_gui(&$gui, &$rsar, $parents) {
	$parents=select($parents,$rsar['parent']);
	$gui->add("list","link_parent",_PARENT,$parents,null,' size="6"');
	$gui->add("hidden","olink_parent","",$rsar['parent']);
	// show an option for popmenu flag
	if (!$rsar['sublevel'])
		$gui->add('hidden', 'link_sublevel', '', "0");
	else {
		$sublevel_list = array(
			array('name' => _YES, 'value' => 2),
			array('name' => _NO, 'value' => 1)
		);
		$sublevel_list = select($sublevel_list, $rsar['sublevel']);
		$gui->add('list', 'link_sublevel', _MENU_LINK_POPMENU, $sublevel_list);
	}
	$gui->add("access","link_access",_ACCESS,$rsar['access']);
}

function component_gui($item_type, &$gui, $id) {
	global $conn,$menutype, $d_root;

	if (isset($id)) {
		$rsar=$conn->GetRow("SELECT id,name,parent,componentid,access,params,sublevel FROM #__menu WHERE id=".$id);
			$c_head=_MENU_COMPONENT_EDIT_HEAD;
	} else {
		$rsar=array("id"=>"0","name"=>"","componentid"=>0,"access"=>"0","parent"=>"0","params"=>"",'sublevel' => 0);
		$c_head=_MENU_COMPONENT_NEW_HEAD;
	}

	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head,"");
    
	_add_move_box($gui, $menutype);

	$gui->add("hidden","link_type","",$item_type);
	$gui->add("hidden","link_id","",$rsar['id']);

	$v = new ScriptedUI_Validation();
	$uv = new ScriptedUI_Validation();$uv->not_empty = true;$uv->max = 100;
	$gui->add("textfield","link_name",_NAME, xhtml_safe($rsar['name']),$uv);

	if (isset($id)) {
		$rsa = $conn->SelectArray('#__components', 'id,name', ' WHERE id='.$rsar['componentid']);
		$gui->add("text","",_MENU_TYPE_COMPONENT,$rsa[0]['name']);
		$gui->add("hidden","link_componentid","",$rsar['componentid']);	
	} else {
		global $my;
		$rsa = $conn->SelectArray('#__components', 'id,name,link', " WHERE parent=0 AND link<>''");//admin_access<=".$my->gid." ");
		$com_array[]=array("name"=>_SELECT,"value"=>"");
		foreach($rsa as $row)$com_array[]=array("name"=>$row['name'],"value"=>$row['id']);
		$com_array=select($com_array,$rsar['componentid']);
		$gui->add("list","link_componentid",_MENU_TYPE_COMPONENT,$com_array,$v, ' size="6"');
	}

	$parent[]=array("name"=>_MENU_TOP,"value"=>"0");
	$rsa=$conn->SelectArray('#__menu', 'id,name,parent', " WHERE access<9 AND parent=0 AND menutype='$menutype'");

        foreach($rsa as $row) {
		if ($row['id'] != $id)
			$parent[]=array("name"=>$row['name'],"value"=>$row['id']);
	}
	
	_terminate_gui($gui, $rsar, $parent);

	// params xml part
	if (isset($id))
		_component_params($gui,$rsar['componentid'], $rsar['params']);
    $gui->add("tab_end");
    $gui->add("tab_tail");
}

function _component_params(&$gui, $id, $params) {
	global $conn, $d_root;
	if ($id==0)	// this is a bad hack
		$com = 'content';
	else {
		$crow= $conn->SelectRow('#__components', 'option_link', " WHERE id=".$id);
		$com = substr($crow['option_link'], 4);
	}
	$c_xml = $d_root.'components/'.$com.'/'.$com.'.xml';
	if (!file_exists($c_xml))
		$c_xml = $d_root.'admin/components/'.$com.'/'.$com.'.xml';
		// include the relative language file, if present
		global $my;
		$lang_inc = $d_root.'lang/'.$my->lang.'/admin/components/'.$com.'.php';
		if (file_exists($lang_inc)) {
			include $lang_inc;
		}
        $gui->addxmlparams($c_xml,$params,false,_COMPONENTS_PARAMS,'components/component');
}


function gui($item_type, &$gui, $id) {
	global $conn,$menutype;
	
	if (isset($id))
		$rsar=$conn->SelectRow('#__menu', 'id,name,parent,componentid,access,params,sublevel',
							" WHERE id=".$id);
	else
		$rsar=array("id"=>"","name"=>"","parent"=>"0","componentid"=>0,"access"=>"0", 'params' => '', 'sublevel' => 0);

	if($item_type=='cc' || $item_type=='cb')
		$t_head = _MENU_TYPE_CATEGORY;
	else if($item_type=='ci')
		$t_head = _MENU_TYPE_ITEM;
	else
		$t_head = _MENU_TYPE_SECTION;

	$gui->add("com_header",_MENU_NEW_HEAD." &gt; $t_head");
	$gui->add("tab_head");
	$gui->add("tab_simple","",_MENU_NEW_HEAD,"");
	_add_move_box($gui, $menutype);
	$gui->add("hidden","link_type","",$item_type);
	$gui->add("hidden","link_id","",$rsar['id']);
	$select_list=array();
	if($item_type=="ci") $content_defined=false;
	global $access_acl;
	$rsa=$conn->SelectArray('#__sections', 'id,title', ' WHERE '.$access_acl);

	foreach($rsa as $row) {
		if ($item_type=="cc" || $item_type=="cb") {
			$rsia=$conn->SelectArray('#__categories', 'id,name', " WHERE section = '".$row['id']."'");
			foreach($rsia as $rowi) {
				$select_list[]=array("name"=>$row['title'].'/'.$rowi['name'],"value"=>$rowi['id']);
			}
		// gather content items which could be linked
		} else if($item_type=="ci") {
			$rsia=$conn->SelectArray('#__categories', 'id,name', ' WHERE count<>0 AND section = \''.$row['id']."'");
			foreach($rsia as $rowi) {
				$rsi1a=$conn->SelectArray('#__content', 'id,title_alias', ' WHERE catid=\''.$rowi['id']."'");
				if (isset($rsi1a[0])) {
					$content_defined=true;
					foreach($rsi1a as $rowi1)
						$select_list[]=array("name"=>$row['title'].'/'.$rowi['name'].'/'.$rowi1['title_alias'],"value"=>$rowi1['id']);
				}
			}
		} else  //if($item_type=="cs")
			$select_list[]=array("name"=>$row['title'],"value"=>$row['id']);
	}

	if (($item_type=='ci') && !$content_defined) {
		$gui->add("tab_simple","",_MENU_CONTENT_ITEM_ERROR);
		$gui->add("tab_end");
		$gui->add("tab_tail");
		return;
	}

	//L: hack to select the category
	if (isset($id) && (($item_type=='cc' || $item_type == 'cs' || $item_type=='ci' || $item_type=='cb'))) {
		$tmp = $conn->SelectRow('#__menu', 'link', ' WHERE id='.$id);
		if (!preg_match('/id=(\\d+)/', current($tmp), $m))
			trigger_error("Cannot find selected item");
		$select_list = select($select_list, (int)$m[1]);
	} else
		$select_list = select($select_list,$rsar['componentid']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("list", "menu_id", $t_head, $select_list, $v, ' size="6"');

	$parent[]=array("name"=>_MENU_TOP,"value"=>"0");
	$rsa=$conn->SelectArray('#__menu', 'id,name,parent', " WHERE parent=0 AND menutype='$menutype'");
	foreach($rsa as $row) {
		$parent[] = array("name"=>$row['name'],"value"=>$row['id']);
	}
	_terminate_gui($gui, $rsar, $parent);

	//L: why not 'ci' here?
	if ($item_type=='cc' || $item_type=='cs' || $item_type=='cb') {
		_component_params($gui, $rsar['componentid'], $rsar['params']);
	}
	$gui->add("tab_end");
	$gui->add("tab_tail");
}

function url_gui($item_type, &$gui, $id = null) {
	global $conn,$menutype;

	global $conn,$d_root;
	$d_name_mask="";
	global $item_types;
	$t_head = null;
	foreach($item_types as $it) {
		if ($it['value'] == $item_type) {
			$t_head = $it['name'];
			if ($item_type == 'separator')
				$d_name_mask="- - - - - -";
			break;
		}
	}
	if (!isset($t_head)) {
		trigger_error($item_type.' is not recognized');
	}
	if (isset($id))
		$rsar=$conn->SelectRow('#__menu', '*', ' WHERE id='.$id);
	else
		$rsar=array("id"=>"","name"=>$d_name_mask,"link"=>"","parent"=>"0","componentid"=>"","browsernav"=>"0","access"=>"0", 'params' => '', 'sublevel' => 0);
	
	$gui->add("com_header", isset($id) ? _MENU_EDIT_HEAD : _MENU_NEW_HEAD.' &gt; '.$t_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","", isset($id) ? _MENU_EDIT_HEAD : _MENU_NEW_HEAD);
	_add_move_box($gui, $menutype);
	$gui->add("hidden","link_type","",$item_type);
	$gui->add("hidden","link_id","",$rsar['id']);
	$select_list='';
	$v = new ScriptedUI_Validation();$v->not_empty = true;
	$uv = new ScriptedUI_Validation();$uv->not_empty = true;$uv->max = 100;
	if ($item_type=="url"){
		$gui->add("textfield","link_name",_NAME,xhtml_safe($rsar['name']), $uv);
		$gui->add("textfield","link_url",_URL,$rsar['link'], $v);
		$nav_list=array(
			array("name"=>_MENU_LINK_OCUR,"value"=>"0"),
			array("name"=>_MENU_LINK_ONEW,"value"=>"1"),
			array("name"=>_MENU_LINK_OPOPUP,"value"=>"2")
		);
		$nav_list=select($nav_list,$rsar['browsernav']);
		$gui->add("list","link_browsernav",$t_head,$nav_list,null,' size="3"');
	}
	else if($item_type=="wrapper"){
		$gui->add("textfield","link_name",_NAME,xhtml_safe($rsar['name']), $uv);
		// set the link field (in db) for the wrapper
		if (!isset($id))
			$rsar['link'] = 'index.php?option=wrapper';
		$gui->add("hidden","link_url",'',$rsar['link'], $v);
	} else if($item_type=="cl"){
		$gui->add("textfield","link_name",_NAME,xhtml_safe($rsar['name']), $uv);

		// select the menu categories
		$rsa1=$conn->SelectArray('#__categories', 'id,name,count', " WHERE section = 'com_menu'");
		$clone_of=false;
		// enumerate menu items for each category
		foreach($rsa1 as $row1) {
			$rsa2=$conn->SelectArray('#__menu', 'id,menutype,name,link_type', " WHERE menutype = '".$row1['name']."' AND link_type<>'cl'");
			foreach($rsa2 as $row2) {
				$clone_of[]=array("name"=>$row1['name']." -&gt; ".$row2['name'],"value"=>$row2['id']);
			}
		}
		$clone_of=select($clone_of,$rsar['link']);
		$gui->add("list","link_url",_MENU_CLINK_SELECT,$clone_of,null, ' size="6"');
	} else{
		$gui->add("textfield","link_name",_MENU_SPACERTEXT,xhtml_safe($rsar['name']), $uv);
	}

	$parent[]=array("name"=>_MENU_TOP,"value"=>"0");
	$rsa=$conn->SelectArray('#__menu', 'id,name,parent', " WHERE access<9 AND parent=0 AND menutype='$menutype'");

	foreach($rsa as $row) {
		if ($row['id'] != $id)
			$parent[]=array("name"=>$row['name'],"value"=>$row['id']);
	}
	
	_terminate_gui($gui, $rsar, $parent);
	
	$gui->add("tab_end");

	// always add the parameters when adding new wrapper menu item
	if ($item_type=="wrapper") {
		global $my;
		$c_xml= $d_root.'components/wrapper/wrapper.xml';
		include com_lang($my->lang, 'wrapper');
		$gui->addxmlparams($c_xml,$rsar['params'],false,_COMPONENTS_PARAMS,'components/component');
	}

	$gui->add("tab_tail");
}

?>