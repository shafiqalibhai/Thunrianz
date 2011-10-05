<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function categories_table($sec_id) {
	global $conn, $easydb;
	
	if ($sec_id!=0) {
		$srow = $conn->GetRow('SELECT name FROM #__categories WHERE id='.$sec_id);
		$sect = ' ('.current($srow).')';
	} else $sect = '';

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=forum&option=categories");
	$gui->add("com_header",_FORUM_MANAGE.$sect);
	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_NAME,'val'=>'name','len'=>'60%','ilink'=>'admin.php?com_option=forum&option=categories&task=edit&cid[]=ivar1','ivar1'=>'id') ,
//						  array('title'=>_SECTION, 'val'=>'parent_id' , 'len'=>'10%','align'=>'center') , 
						  array('title'=>_RECORDS, 'val'=>'topic_count' , 'len'=>'10%','align'=>'center') , 
						  array('title'=>_ACCESS,'val'=>'access','len'=>'10%','align'=>'center') ,
						  array('title'=>_EDITGROUP,'val'=>'editgroup','len'=>'10%','align'=>'center') ,
						  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center') 
						 ); 
						 
	$table_data=$conn->SelectArray('#__forum_categories', 'id,name,ordering,access,editgroup,topic_count'," WHERE parent_id=$sec_id".$gui->Ordering());
//	$table_data = gui_array_replace($table_data, array('parent_id' => $easydb->sections('forum')));
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("hidden", 'sec_id', '', $sec_id);
	$gui->add("end_form");
	$gui->generate();					
}

function sections_table() {
	global $conn, $easydb;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=forum&option=sections");
	$gui->add("com_header",_FORUM_MANAGE_SECTIONS);
	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_TITLE,'val'=>'name','len'=>'60%','ilink'=>'admin.php?com_option=forum&option=sections&task=edit&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_RECORDS, 'val'=>'count' , 'len'=>'10%','align'=>'center') , 
						  array('title'=>_ACCESS,'val'=>'access','len'=>'10%','align'=>'center') ,
//						  array('title'=>_EDITGROUP,'val'=>'editgroup','len'=>'10%','align'=>'center') ,
						  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center') 
						 ); 
						 
	$table_data=$conn->SelectArray('#__categories', 'id,name,count,access,ordering', " WHERE section='com_forum'".$gui->Ordering());
//	$table_data = gui_array_replace($table_data, array('parent_id' => $easydb->sections('forum')));
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();					
}


function edit_categories($cid, $sec_id = null) {
	global $conn;

	if (isset($cid)) {
		$rsar=$conn->GetRow("SELECT * FROM #__forum_categories WHERE id = ".$cid);
		$c_head = _FORUM_MANAGE_EDIT;
	} else {
		$rsar=array("id"=>"","name"=>"","description"=>"","moderators"=>"","locked"=>"0","access"=>"0",
		"editgroup"=>"1", 'parent_id' => $sec_id);
		$c_head = _FORUM_MANAGE_NEW;
	}
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=forum&option=categories");
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head);
	$gui->add("hidden","category_id","",$rsar['id']);
	$v = new ScriptedUI_Validation(); $v->not_empty = true;
	$gui->add("textfield","category_name",_NAME,$rsar['name'], $v);

	global $easydb;
	$sections = $easydb->sections('forum', $rsar['parent_id']);
	$gui->add("hidden","category_old_section",_SECTION,$rsar['parent_id']);
	$gui->add("select","category_section",_SECTION,$sections);
	$v = new ScriptedUI_Validation(false);
	$v->max = 255;
	$gui->add("textfield","category_new_section",_NEW_SECTION,'', $v);

	$gui->add("access","category_access",_ACCESS,$rsar['access']);

	$gui->add("access","category_editgroup",_EDITGROUP,$rsar['editgroup']);
	$gui->add("boolean","category_locked",_FORUM_LOCKED,$rsar['locked']);
	$gui->add("textarea","category_description",_DESC,$rsar['description']);
	$gui->add('spacer');
	$gui->add("textfield","category_moderators",_FORUM_MODERATORS,$rsar['moderators']);
	$gui->add("text","","",_FORUM_MODERATORS_EXP);
	if (!isset($cid))
		$gui->add('insert_where');

	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function edit_section($id) {
	global $conn;

	if(isset($id)) {
		$rsar=$conn->SelectRow('#__categories', '*', " WHERE id=".$id);					   
		$c_head = _FORUM_MANAGE_SECTION_EDIT;
	} else {
		$rsar=array("id"=>-1,'name'=>'',"description"=>"","access"=>"0","editgroup"=>"3");
		$c_head = _FORUM_MANAGE_SECTION_NEW;
	}
	
	//TODO: offer customization of other category fields
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=forum&option=sections");
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head);
	$gui->add("hidden","section_id","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$v->max = 255;
	$gui->add("textfield","category_new_section",_NEW_SECTION,'', $v);

	$gui->add("textfield","section_name",_TITLE,$rsar['name'], $v);

	$al=access_array($rsar['access']);
	$gui->add("select","section_access",_ACCESS,$al);

	$gui->add("access","section_editgroup",_EDITGROUP,$rsar['editgroup']);
	$gui->add("textarea","section_description",_DESC,$rsar['description']);
	if (!isset($id))
		$gui->add('insert_where');
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

?>