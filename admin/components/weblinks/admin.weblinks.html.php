<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

/* items interface */
function items_table($catid = null) {
	global $conn,$sec_id;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=weblinks&option=items");
	$gui->add("com_header",_USERS_WEBLINKS_HEAD);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_TITLE,'val'=>'title','len'=>'70%','ilink'=>'admin.php?com_option=weblinks&option=items&task=edit&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_PUBLISHED,'val'=>'published','len'=>'10%','align'=>'center')
						 ); 
	//$gui->order=-1;
	if (isset($catid))
		$catsql = ' WHERE catid='.$catid;
	else {
		$catsql = '';
		$table_head[] = array('title'=>_CAT,'val'=>'catid','len'=>'10%','align'=>'center');
	}
	$table_head[] = array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center');

	$table_data=$conn->SelectArray('#__weblinks', 'id,catid,title,published,ordering,hits', $catsql.$gui->Ordering());
	$cat_arr=category_array('com_weblinks',"-1");
	$replace = array( "catid"=> $cat_arr);
	$table_data = gui_array_replace($table_data,$replace);
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();					
}

function edit_items($id = null) {
	global $conn;

	if (isset($id)) {
		$rsar=$conn->SelectRow('#__weblinks', 'id,catid,title,url,description,hits', " WHERE id = ".$id);
		$c_head = _USERS_WEBLINKS_EDIT_HEAD;
	} else{
		$rsar=array("id"=>"","catid"=>"","title"=>"","url"=>"http://","description"=>"","hits"=>"0");
		$c_head = _USERS_WEBLINKS_NEW_HEAD;
	}

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=weblinks&option=items");
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","Content",$c_head);
	$gui->add("hidden","weblink_id","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","weblink_title",_TITLE,$rsar['title'],$v);
	$gui->add("textfield","weblink_url",_URL,$rsar['url'],$v);
	$cat_drop=category_array('com_weblinks',$rsar['catid']);
	$gui->add("select","weblink_catid",_SELECTCAT,$cat_drop,$v);
	$gui->add("hidden","weblink_ocatid","",$rsar['catid']);
	$gui->add("textarea","weblink_description",_DESC,$rsar['description']);
	$v = new ScriptedUI_Validation();
	$v->digits = true;
	$v->not_empty = true;
	$gui->add("textfield","weblink_hits",_HITS,$rsar['hits'], $v);
	if (!isset($id))
		$gui->add('insert_where');

	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function categories_table() {
	global $conn,$sec_id;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=weblinks&option=categories");
	$gui->add("com_header",_USERS_WEBLINKS_EDIT_CATEGORY);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_CAT,'val'=>'name','len'=>'60%','ilink'=>'admin.php?com_option=weblinks&option=categories&task=edit&cid[]=ivar1','ivar1'=>'id',
						  'explore' => 'admin.php?com_option=weblinks&option=items&id=ivar1') ,
						  array('title'=>_RECORDS , 'val'=>'count' , 'len'=>'10%','align'=>'center') , 
						  array('title'=>_ACCESS,'val'=>'access','len'=>'10%','align'=>'center') ,
						  array('title'=>_EDITGROUP,'val'=>'editgroup','len'=>'10%','align'=>'center') ,
						  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center') 
						 ); 
	global $edit_sql;
	$table_data=$conn->SelectArray('#__categories', 'id,name,section,ordering,access,editgroup,count', " WHERE section='com_weblinks' $edit_sql".$gui->Ordering());
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
//	$gui->add("massops");
	$gui->add("end_form");
	$gui->generate();					
}

function edit_categories($id = null) {
	global $conn,$sec_id,$d_root;

	if (isset($id)) {
		$rsar=$conn->SelectRow('#__categories', 'id,name,image,image_position,description,access,editgroup'," WHERE id = ".$id);
		$c_head = _CONTENT_CAT_EDIT_HEAD;
	}else{
		$rsar=array("id"=>"","name"=>"","description"=>"","access"=>"0","image"=>"","image_position"=>"left", 'editgroup' => 3);
		$c_head = _CONTENT_CAT_NEW_HEAD;
	}
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=weblinks&option=categories");
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head);
	$gui->add("hidden","category_id","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","category_name",_NAME,$rsar['name'], $v);
	$img_arr=select_array("media/icons/",_SELECTIMAGE,$rsar['image'],'file', $GLOBALS['d_pic_extensions']);
	$gui->add("list_image","category_image",_SELECTIMAGE,$img_arr,null,"media/icons/");
	$pos=pos_array($rsar['image_position']);
	$gui->add("select","category_image_position",_IMAGEPOS,$pos);
	$gui->add("access","category_access",_ACCESS,$rsar['access']);
	$gui->add("access","category_editgroup",_EDITGROUP,$rsar['editgroup']);
	$gui->add("textarea","category_description",_DESC,$rsar['description'],null, ' size="5"');
	if (!isset($id))
		$gui->add('insert_where');
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}
?>