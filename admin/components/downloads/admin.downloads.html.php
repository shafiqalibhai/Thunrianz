<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

/* items interface */
function items_table($id = null) {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=downloads&option=items".
			(isset($id) ? '&catid='.$id : ''));
	$gui->add('spacer');
	$gui->add("com_header",_DM_MANAGE);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_TITLE,'val'=>'title','len'=>'50%','ilink'=>
						'admin.php?com_option=downloads'.(isset($id) ? '&catid='.$id : '').
						'&option=items&task=edit&cid[]=ivar1','ivar1'=>'id') ,
//						array('title'=>_ID,'val'=>'id','len'=>'10%','align'=>'center'),
						array('title'=>_OWNER,'val'=>'userid','len'=>'10%','align'=>'center'),
						array('title'=>_PUBLISHED,'val'=>'published','len'=>'10%','align'=>'center'),
						array('title'=> _EXISTS,'val'=>'exists','len'=>'10%','align'=>'center')
					);
	if (!isset($id))
		$table_head[] =  array('title'=>_CAT,'val'=>'catid','len'=>'10%','align'=>'center');
	$table_head[] = array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center');

	if (isset($id))
		$a = ' WHERE catid='.$id;
	else $a = '';
	$table_data=$conn->SelectArray('#__downloads', 'id,catid,title,published,ordering,userid,url,hits',
							" $a ".$gui->Ordering());
	$cat_arr=category_array('com_downloads',"-1");
	$replace = array( "catid"=> $cat_arr);
	$table_data = gui_array_replace($table_data,$replace, array(
			'userid' => 'username_by_id'
			),
			array ('exists' => '_exists_normalizer')
			);
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	if (isset($id))
		$gui->add('hidden', 'catid', '', $id);
//	$gui->add("massops");
	$gui->add("end_form");
	$gui->generate();					
}

function _exists_normalizer(&$row) {
	if (is_url($row['url']))
		$row['exists'] = _NA;
	else {
		global $d_root;
		$row['exists'] = is_readable($d_root.$row['url']) ? _YES : '<big style="color:red"><strong>'._NO.'</strong></big>';
	}
}

function edit_items($id = null, $catid = null) {
	global $conn;

	if (isset($id)) {
		$rsar=$conn->SelectRow('#__downloads', '*', ' WHERE id='.$id);
		$c_head = _DM_MANAGE_EDIT_HEAD;
	}else {
		$rsar=array("id"=>"","catid"=> (string)$catid,"title"=>"","author"=>"","url"=>"http://","description"=>"", 'image_url' => '', "website"=>"http://","hits"=>0,'published'=>0, 'flags' => 3);
		$c_head = _DM_MANAGE_NEW_HEAD;
	}
	
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=downloads&option=items".
				(isset($catid) ? '&catid='.$catid : ''));
	$gui->add("com_header",$c_head);
//	$gui->add("tab_head");
//	$gui->add("tab_simple","",$c_head);

	// setup the uploader tab
	if (isset($id)) {
		if (is_url($rsar['url'])) {
			$url = $rsar['url'];
			$dir = '';
			$dtab = 2;
		} else {
			$dir = $rsar['url'];
			$url = '';
			$dtab = 3;
		}
	} else {
		$dtab = 1;
		$dir = $url = '';
	}

	$gui->dtabs_interface(_UPL_ALL, array(_FILE_UPLOAD, _REMOTE_URL, _LOCAL_DIR), null, 'both',
			$url, $dir, $dtab);
	$gui->add('spacer');

	$cat_drop=category_array('com_downloads',$rsar['catid'], 'ORDER BY ordering');
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("select","download_catid",_SELECTCAT,$cat_drop,$v);

	$gui->add("textfield","download_title",_TITLE,$rsar['title'],$v);
	$gui->add("boolean","download_published",_PUBLISHED,$rsar['published']);
	$flags = download_flags($rsar['flags']);
	global $my;
	$gui->add("boolean","download_protected",_DOWNLOADS_PROTECTED,$flags['protected'], null,
			$my->is_admin() ? '' : ' disabled="disabled"');
	$gui->add('spacer');
	$gui->add("boolean","download_antileech",_DOWNLOADS_ANTILEECH,$flags['antileech']);	
	if (isset($id))
		$gui->add("hidden","download_o_protected",'',$flags['protected']);
	$gui->add("textarea","download_description",_DESC,$rsar['description']);
	$gui->add("textfield","download_author",_OWNER,$rsar['author']);
	$gui->add("textfield","download_website",_WEBSITE,$rsar['website']);
	if (isset($id))
		$gui->add("text","",_DM_FILE_SIZE,$rsar['filesize']);
	$gui->file_browser("download_image_url",_DM_IMAGE_URL,$rsar['image_url'],
					null, 'file', false, array($GLOBALS['d_private'].'downloads/'));

	$gui->add("textfield","download_hits",_HITS,$rsar['hits']);
	if (isset($id)) {
		$gui->add("hidden","download_ocatid","",$rsar['catid']);
		$gui->add("hidden","download_id","",$rsar['id']);
	}
	if (!isset($id))
		$gui->add('insert_where');
	
//	$gui->add("tab_end");
//	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}


function categories_table() {
	global $conn;
	
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=downloads&option=categories");
	$gui->add("com_header",_DM_MANAGE_CAT);
	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_CAT,'val'=>'name','len'=>'60%',
								'ilink'=>'admin.php?com_option=downloads&option=categories&task=edit&cid[]=ivar1','ivar1'=>'id',
								'explore' => 'admin.php?com_option=downloads&option=items&catid=ivar1') ,
						  array('title'=>_ACCESS , 'val'=>'access' , 'len'=>'10%','align'=>'center') ,
						  array('title'=>_EDITGROUP , 'val'=>'editgroup' , 'len'=>'10%','align'=>'center') ,
						  array('title'=>_RECORDS , 'val'=>'count' , 'len'=>'10%','align'=>'center') , 
						  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center') 
						 ); 
						 
	$table_data=$conn->SelectArray('#__categories', 'id,name,section,access,editgroup,ordering,count',
						" WHERE section='com_downloads' ".$gui->Ordering());
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
//	$gui->add("massops");
	$gui->add("end_form");
	$gui->generate();
}

function edit_categories($sec_id,$cid = null) {
	global $conn,$d_root;

	if (isset($cid)) {
		$rsar=$conn->SelectRow('#__categories', '*', ' WHERE id='.$cid);
		$c_head = _CONTENT_CAT_EDIT_HEAD;
	} else {
		$rsar=array("id"=>"","parent_id"=>"","name"=>"","description"=>"","image"=>"","image_position"=>"left", 'access' => 0, 'editgroup' => 3);
		$c_head = _CONTENT_CAT_NEW_HEAD;
	}
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=downloads&option=categories");
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head);
	$gui->add("hidden","category_id",'',$rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","category_name",_NAME,$rsar['name'], $v);

	/*
	//will be implemented in later release
	//L: really?
	$cat_drop=category_array('com_downloads',$rsar['parent_id'],$rsar['id']);
	$gui->add("select","category_parent_id",_PARENT,$cat_drop);
	$gui->add("hidden","category_oparent_id","",$rsar['parent_id']);
	*/
	$img_arr=select_array("media/icons/",_SELECTIMAGE,$rsar['image'], 'file', $GLOBALS['d_pic_extensions']);
	$gui->add("list_image","section_image",_SELECTIMAGE,$img_arr,null,"media/icons/");
	$pos=pos_array($rsar['image_position']);
	$gui->add("select","section_image_position",_IMAGEPOS,$pos);
	$gui->add("access","category_access",_ACCESS,$rsar['access']);
	$gui->add("access","category_editgroup",_EDITGROUP,$rsar['editgroup']);
	$gui->add("textarea","category_description",_DESC,$rsar['description']);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}
?>