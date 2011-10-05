<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function banner_table() {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=banner");
	$gui->add("com_header",_BANNERS_HEAD);
	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_BANNERS_NAME,'val'=>'name','len'=>'60%','ilink'=>'admin.php?com_option=banner&task=edit&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_ID,'val'=>'id','len'=>'10%','align'=>'center'),
						  array('title'=>_PUBLISHED,'val'=>'published','len'=>'10%','align'=>'center'),
						  array('title'=>_BANNERS_IMPRESS,'val'=>'imphits','len'=>'10%','align'=>'center'),
						  array('title'=>_HITS,'val'=>'hits','len'=>'10%','align'=>'center')
						 ); 
	$table_data=$conn->GetArray("SELECT id,name,imphits,hits,published FROM #__banners");					   
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();
}

function edit_banner($id) {
	global $conn,$d_root;
	if (isset($id)) {
		$rsar=$conn->GetRow("SELECT id,name,imageurl,clickurl,blanktarget,bannercode FROM #__banners WHERE id = ".$id);	  
		$c_head = _BANNERS_EDIT;
	} else {
		$rsar=array("id"=>"","name"=>"","imageurl"=>"media/banners/","bannercode"=>"","clickurl"=>"http://",
		'blanktarget' => 1);
		$c_head = _BANNERS_NEW;
	}
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=banner");
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head);
	$gui->add("hidden","banner_id","",$rsar['id']);
	$v = new ScriptedUI_Validation(); $v->not_empty = true;
	$gui->add("textfield","banner_name",_BANNERS_NAME,$rsar['name'], $v);
	$folder = 'media/banners/';
	global $banner_extensions;
	$banner_arr=select_array($folder,_SELECTIMAGE,$rsar['imageurl'],'file', $banner_extensions);
	$gui->add("list_image","banner_imageurl",_BANNERS_IMAGE,$banner_arr, null, $folder);
	$gui->add('file', 'banner_image_upload');
	$gui->add("textfield","banner_clickurl",_BANNERS_CLICK_URL,$rsar['clickurl']);
	$gui->add("boolean","banner_blanktarget",_BANNERS_BLANK_TARGET,$rsar['blanktarget']);
	$gui->add("textarea","banner_bannercode",_BANNERS_CODE,$rsar['bannercode']);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

?>