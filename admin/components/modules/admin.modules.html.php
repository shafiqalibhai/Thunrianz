<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function modules_table() {
	global $conn;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=modules&option=install");
	$gui->add("com_header",_MODULES_INSTALL_HEAD);

	$table_head = array ( array('title'=>'checkbox' , 'val'=>'module' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_NAME,'val'=>'title','len'=>'40%'),
						  array('title'=>_MODULE_ID , 'val'=>'module' , 'len'=>'40%') , 
						  array('title'=>_TYPE , 'val'=>'iscore' , 'len'=>'20%') , 
						 ); 
	$replace = array(  "iscore"=>array(array("value"=>"0","name"=>_CORE),
								   array("value"=>"1","name"=>_GENERAL)
									   )
						);
	$table_data=$conn->SelectArray('#__modules', 'id,title,module,iscore', " WHERE iscore=1");
	$table_data = gui_array_replace($table_data,$replace);
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();					
}

function modules_manage_table() {
	global $conn,$access_level;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=modules&option=manage");
	$gui->add("com_header",_MODULES_HEAD);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'2%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_NAME,'val'=>'title','len'=>'60%','ilink'=>'admin.php?com_option=modules&option=manage&task=edit&cid[]=ivar1','ivar1'=>'id'),
						  array('title'=>_MODULE_ID , 'val'=>'module' , 'len'=>'20%') , 
						  array('title'=>_MODULES_POSITION , 'val'=>'position' , 'len'=>'10%','align'=>'center') , 
						  array('title'=>_ACCESS, 'val'=>'access' , 'len'=>'10%','align'=>'center') , 
						  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center') 
						 ); 
	$replace = array(  "access"=>$access_level	);
		
	$table_data=$conn->SelectArray('#__modules', 'id,title,module,ordering,position,access', $gui->Ordering());
	$gui->add("data_table_arr","maintable",$table_head,$table_data);

	$gui->add('hidden', 'option','', 'manage');
//	$gui->add('massops');

	$gui->add("end_form");
	$gui->generate();					
}

function module_edit($module_id) {
	global $conn,$easydb,$d_root,$d;

	$rsar=$conn->SelectRow('#__modules', 'id,title,message,ordering,position,module,access,showtitle,showon,params,instance', ' WHERE id = '.$module_id);					   
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=modules&option=manage");
	$gui->add("com_header",_MODULES_EDIT_HEAD." &gt; ".$rsar['title']);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_MODULES_EDIT_HEAD);
	$gui->add("hidden","module_id","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","module_title",_TITLE,$rsar['title'], $v);
	$gui->add("boolean","module_showtitle",_MODULES_EDIT_STITLE,$rsar['showtitle']);

	$module_pos = array_assoc($d->ModulePositions());
	
	$module_pos=select($module_pos,$rsar['position']);
	$gui->add("select","module_position",_MODULES_POSITION,$module_pos);
	$gui->add("access","module_access",_ACCESS,$rsar['access']);

	// first parameter contains the values, second parameter contains the component
	if (strlen($rsar['showon']) && $rsar['showon']!='_0_')
		$value = explode('_', $rsar['showon']);
	else
		$value = array();
	$value = array(
		$value,
		null);
	$gui->add("instancem","module_showon[]",_MODULES_SHOW_ON,$value,null, ' size="6"');

	$gui->add('spacer');
	$iel =& $gui->add('hidden', 'menu_instance', '', 0);

	//parsing the params .
	$gui->add("tab_end");

	global $my;
	$module = $rsar['module'];
	$path = mod_lang($my->lang, $module);
	if (file_exists($path)) {
		include $path;
	}

	$xmlDoc = $gui->addxmlparams($d_root."modules/".$rsar['module'].".xml",$rsar['params'], true, _MODULES_PARAMS, 'modules/module');
	$group = null;
	if (isset($xmlDoc)) {
		$group = $xmlDoc->getElementByPath('group');
		if (isset($group))
			$group = $group->getValue();
		$xmlDoc = null;
	}
	if (isset($group)) {
		$iel['tag'] = 'instance';
		$iel['desc'] = _TARGET_INSTANCE;
		$iel['value'] = array(array($rsar['instance']), $group);
	}
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function new_mod() {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=modules&option=manage");
	$gui->add("com_header",_MODULES_NEW_HEAD);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_MODULES_NEW_HEAD);
	$gui->add("hidden","task","","new_mod");
	// removed 'iscore=0 OR iscore=1'
	$rsa=$conn->SelectArray('#__modules', 'id,module,title', " ORDER BY module");
	$mod_type = array();
	if (isset($rsa[0])) {
		foreach($rsa as $row) {
			$mod_type[]=array("name"=>$row['title'].' ('.$row['module'].')',"value"=>$row['id']);
		}
	}
	$v=new ScriptedUI_Validation();
	$gui->add("list","mod_id",_MODULES_NEW_INSTANCE,$mod_type, $v,' size="10"');
//	$button_arr = array(array('name'=>_NAV_NEXT , 'onclick'=>'javascript:if (document.adminform.mod_id.value==0) alert(\''.js_encode(_IFC_LIST_ERR).'\'); else document.adminform.submit()'));   
//	$gui->add("buttons","","",$button_arr);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

?>