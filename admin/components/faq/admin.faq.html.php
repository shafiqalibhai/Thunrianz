<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

// items 
function items_table($catid = null) {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=faq&option=questions".(isset($catid) ? '&catid='.$catid : ''));
	$gui->add("com_header",_FAQ_QUESTIONS_MANAGE);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_FAQ_A_QUESTION,'val'=>'question','len'=>'60%','ilink'=>'admin.php?com_option=faq&option=questions&task=edit&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_OWNER,'val'=>'userid','len'=>'10%','align'=>'center'),
						  array('title'=>_PUBLISHED,'val'=>'published','len'=>'10%','align'=>'center'),
						  array('title'=>_CAT,'val'=>'catid','len'=>'20%','align'=>'center'),
					  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center') 
						 ); 
//	$gui->order=-1;
	$table_data=$conn->SelectArray('#__faq', 'id,catid,question,published,ordering,userid',
			(isset($catid) ? ' WHERE catid='.$catid : '').$gui->Ordering());
	$cat_arr=category_array('com_faq',"-1");
	$replace = array("catid"=>$cat_arr);
	$table_data = gui_array_replace($table_data,$replace, array('userid' => 'username_by_id'));
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();					
}

function categories_table() {
	global $conn,$sec_id;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=faq&option=categories");
	$gui->add("com_header",_CONTENT_CAT_HEAD);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_CAT,'val'=>'name','len'=>'60%',
							'ilink'=>'admin.php?com_option=faq&option=categories&task=edit&cid[]=ivar1','ivar1'=>'id',
	    'explore' => 'admin.php?com_option=faq&option=questions&catid=ivar1'), 
						  array('title'=>_RECORDS , 'val'=>'count' , 'len'=>'10%','align'=>'center') , 
						  array('title'=>_ACCESS,'val'=>'access','len'=>'10%','align'=>'center') ,
						  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center') 
					); 
	$table_data = $conn->SelectArray('#__categories', 'id,name,section,ordering,access,count',
		" WHERE section='com_faq'".$gui->Ordering());
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();					
}

function edit_categories($id = null) {
	global $conn,$sec_id,$d_root;

	if (isset($id)) {
		$rsar=$conn->SelectRow('#__categories', 'id,name,image,image_position,description,access', " WHERE id=".$id);			   
		$c_head = _CONTENT_CAT_EDIT_HEAD;
	}else{
		$rsar=array("id"=>"","name"=>"","description"=>"","access"=>"0","image"=>"","image_position"=>"left");
		$c_head = _CONTENT_CAT_NEW_HEAD;
	}
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=faq&option=categories");
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head);
	$gui->add("hidden","category_id","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","category_name",_NAME,$rsar['name'], $v);
	$img_arr=select_array("media/icons/",_SELECTIMAGE,$rsar['image'], 'file', $GLOBALS['d_pic_extensions']);
	$gui->add("list_image","category_image",_SELECTIMAGE,$img_arr,null,"media/icons/");
	$pos=pos_array($rsar['image_position']);
	$gui->add("select","category_image_position",_IMAGEPOS,$pos);
	$gui->add("access","category_access",_ACCESS,$rsar['access']);
	$gui->add("textarea","category_description",_DESC,$rsar['description']);
	if (!isset($id))
		$gui->add('insert_where');

	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function edit_items($id) {
	global $conn;

	if (isset($id)) {
		$rsar=$conn->SelectRow('#__faq', '*', " WHERE id=".$id);
		$c_head = _FAQ_EDIT_HEAD;
	} else {
		$rsar=array("id"=>"","catid"=>0,"question"=>"","answer"=>"", "published" => 0);
		$c_head = _FAQ_NEW_HEAD;
	}

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=faq&option=questions");
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head);
	$gui->add("hidden","faq_id","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","faq_question",_FAQ_A_QUESTION,$rsar['question'],$v);
	global $_DRABOTS;
	$_DRABOTS->loadCoreBotGroup('editor');
	$_DRABOTS->trigger('OnContentEdit', array(&$rsar['answer']));
	$gui->add("htmlarea","faq_answer",_FAQ_A_ANSWER,$rsar['answer'],$v);
	$cat_drop=category_array('com_faq',$rsar['catid']);
	$gui->add("boolean","faq_published",_PUBLISHED, $rsar['published']==1);
	$gui->add("select","faq_catid",_SELECTCAT,$cat_drop,$v);
	if (!isset($id))
		$gui->add('insert_where');
	else
		$gui->add("hidden","faq_ocatid","",$rsar['catid']);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

?>