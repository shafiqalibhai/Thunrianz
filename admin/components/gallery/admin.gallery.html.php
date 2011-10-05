<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Lanius CMS Enhanced Gallery
# @author legolas558
# @version 1.2
# Released under GNU/GPL License
# This component is part of Lanius CMS core
#
# gallery component HTML output rendering

function items_table($id = null) {
	global $conn;
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=gallery&option=items".( isset($id) ? '&catid='.$id : ''));
	
	$gui->add('spacer');
	$gui->add("com_header",_GALLERY_MANAGE);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_TITLE,'val'=>'title','len'=>'60%','ilink'=>'admin.php?com_option=gallery'.(isset($id) ? '&catid='.$id : '').
						'&option=items&task=edit&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_OWNER,'val'=>'userid','len'=>'10%','align'=>'center'),
						  array('title'=>_PUBLISHED,'val'=>'published','len'=>'10%','align'=>'center'),
		);
		if (!isset($id))
			$table_head[] = array('title'=>_CAT,'val'=>'catid','len'=>'20%','align'=>'center');
	$table_head[] =  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center');
	
	if (isset($id))
		$plus=' WHERE catid='.$id;
	else $plus='';
	$table_data=$conn->SelectArray('#__gallery', 'id,catid,title,published,ordering,hits,userid', " $plus ".$gui->Ordering());
	$cat_arr=category_array('com_gallery',"-1");
	$replace = array( "catid"=> $cat_arr);
	$table_data = gui_array_replace($table_data,$replace, array('userid' => 'username_by_id'));
//	$gui->order=-1;
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
	$gui->add("end_form");
	$gui->generate();					
}

## Creates the photo submission form
function new_photo() {
	global $d_pic_extensions;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=gallery&option=items");
	$gui->add("com_header",_GALLERY_NEW_PHOTO);

	$s = _SUPPORTED_FORMATS.': '.raw_strtoupper(implode('/',$d_pic_extensions));
	$gui->dtabs_interface(_UPL_FILE|_UPL_URL, array($s, _REMOTE_URL), $d_pic_extensions);
	$vn = new ScriptedUI_Validation(false);
	$vn->digits = true;
	$gui->add('textfield', 'photo_max_sz', _GALLERY_AUTO_RESIZE, '0', $vn, 'style="width: 50px;"');
	
	$gui->add('spacer');

	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$cat_arr = category_array('com_gallery');	
	$gui->add("select","photo_catid",_SELECTCAT,$cat_arr,$v);
	$gui->add("textfield","photo_title",_TITLE);
	$gui->add("textarea","photo_description",_DESC);
	$vn->required = true;
	$gui->add("textfield","photo_hits",_HITS,'0',$vn);
	$gui->add('insert_where');

	$gui->add("end_form");
	$gui->generate();
}

function edit_items($id = null, $catid = null) {
	global $conn;

	$rsar=$conn->GetRow("SELECT id,catid,title,url,description,hits FROM #__gallery WHERE id=".$id);
	$extra=$conn->GetRow('SELECT * FROM #__gallery_category WHERE id='.$rsar['catid']);
	if (!count($extra))
		CMSResponse::Back(_GALLERY_NO_CATEGORIES);

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=gallery&option=items".
				(isset($catid) ? '&catid='.$catid : ''));
	$gui->add('spacer');
	$gui->add("com_header",_GALLERY_EDIT_PHOTO);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_GALLERY_EDIT_PHOTO);
	$gui->add("hidden","photo_id","",$rsar['id']);
	if (isset($id)) {
		$gui->add("hidden","photo_ocatid","",$rsar['catid']);
	}
	$has_thumb = false;
	global $d_root;
	if (!is_url($rsar['url'])) {
		$photo_url = $extra['thumbs_path'].$rsar['url'];
		$has_thumb = is_file($d_root.$photo_url);
		if (!$has_thumb) {
			$photo_url = $extra['gallery_path'].rawurlencode($rsar['url']);
			$img_msg = _GALLERY_THUMBNAIL_NOT_AVAILABLE.'<br />';
		} else {
			$img_msg = '';
			$photo_url = $extra['thumbs_path'].rawurlencode($rsar['url']);
		}
	} else {
		$photo_url = $extra['thumbs_path'].clear_name($rsar['url']);
		$has_thumb = is_file($d_root.$photo_url);
		if (!$has_thumb) {
			$photo_url = $rsar['url'];
			$img_msg = _GALLERY_THUMBNAIL_NOT_AVAILABLE.'<br />';
		} else $img_msg = '';
	}
	$gui->add('text', '', $img_msg.'<img src="'.$photo_url.'" border="2" alt="'.$rsar['title'].'" />');
	$gui->add('spacer');
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","photo_title",_TITLE,$rsar['title'],$v);
	$gui->add("textarea","photo_description",_DESC,$rsar['description']);
	$vn = new ScriptedUI_Validation();
	$vn->digits = true;
	$vn->not_empty = true;
	$vn->min_value = 0;
	$gui->add("textfield","photo_hits",_HITS,$rsar['hits'], $vn);
	$gui->add('spacer');
	$gui->add("boolean","photo_make_thumb",_GALLERY_MAKE_THUMBNAIL, !$has_thumb);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

// wizard_interface by legolas558
function wizard_interface() {

	// this function returns a list of folders without associated categories
	// FIXME
	function get_unfolded() {
		global $conn,$access_sql,$d_root;
		$folders = read_dir($d_root._GALLERY_DEFAULT, 'dir');
		if (empty($folders)) return array();
		$i = array_search('thumbs',$folders);
		unset($folders[$i]);
		$nf = array();
		foreach($folders as $folder) {
			$row = $conn->SelectRow('#__gallery_category', 'id',' WHERE gallery_path=\''.sql_encode(_GALLERY_DEFAULT.$folder."/")."'");
			if (!count($row))
				$nf[] = $folder;
		}
		$folders = $nf;
		global $d_root, $d_pic_extensions;
		include $d_root.'admin/classes/fs.php';
		$fs = new FS(true);
		$nf = array();
		foreach($folders as $folder) {
			$files = read_dir($d_root._GALLERY_DEFAULT.$folder.'/', 'file', false, $d_pic_extensions);
			if (!count($files))
				// if the directory contain no files will remove the directory recursively
				$fs->deldir($d_root._GALLERY_DEFAULT.$folder.'/');
			else
				$nf[] = array('value'=>$folder, 'name'=>$folder.' ('.count($files).' files)');
		}
		return $nf;
	}

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=gallery&option=items");
	$gui->add('spacer');
	$gui->add("com_header",_GALLERY_WIZARD);
	$gui->add("tab_head");
	$gui->add("tab_simple","",_GALLERY_WIZARD);
	$cat_arr=category_array('com_gallery');
	$gui->add("text","",_GALLERY_HELP1);
	$gui->add("textfield","batch_catname",_GALLERY_CATNAME);
	$gui->add('spacer');
	if (count($cat_arr)!=0) {
		$gui->add("text","",_GALLERY_HELP2);
		$gui->add("select","batch_catid",_SELECTCAT,select($cat_arr,''));
		$gui->add('spacer');
	}

	$unf_arr = get_unfolded();
	if (count($unf_arr)!=0) {
		$gui->add("text","",_GALLERY_HELP3);
		array_unshift($unf_arr, array("name"=>_GALLERY_SELECT_FOLDER,"value"=>""));
		$gui->add("select","batch_unfolded",_GALLERY_UNFOLDED,select($unf_arr,0));
		$gui->add('spacer');
	}

	//$button_arr = array(array('name'=>_NAV_NEXT , 'onclick'=>'javascript:ui_lcms_st(\'create\');')); 
	$gui->add("hidden","batch_create","","1");  
	//$gui->add('spacer');
	//$gui->add("buttons","","",$button_arr);
	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
	}

function categories_table() {
	global $conn;

	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=gallery&option=categories");
	$gui->add("com_header",_CONTENT_CAT_HEAD);

	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') , 
						  array('title'=>_CAT,'val'=>'name','len'=>'60%','ilink'=>'admin.php?com_option=gallery&option=categories&task=edit&cid[]=ivar1','ivar1'=>'id',
						  'explore' => 'admin.php?com_option=gallery&option=items&id=ivar1') ,
						  array('title'=>_RECORDS , 'val'=>'count' , 'len'=>'10%','align'=>'center') , 
						  array('title'=>_ACCESS,'val'=>'access','len'=>'10%','align'=>'center') ,
						  array('title'=>_EDITGROUP,'val'=>'editgroup','len'=>'10%','align'=>'center') ,
						  array('title'=>_ORDERING,'val'=>'ordering','len'=>'10%','align'=>'center') 
						 ); 
						 
	$table_data=$conn->SelectArray('#__categories',
			'id,name,section,ordering,access,editgroup,count',
			" WHERE section='com_gallery' ".$gui->Ordering());	   
	$gui->add("data_table_arr","maintable",$table_head,$table_data);
//	$gui->add("massops");
	$gui->add("end_form");
	$gui->generate();					
}

// when editing a gallery category
function edit_categories($id) {
	global $conn,$d_root;

	if (isset($id)) {
		$rsar=$conn->SelectRow('#__categories', "id,name,image,image_position,description,access,editgroup", ' WHERE id='.$id);
		$extra = $conn->SelectRow('#__gallery_category', 'gallery_path,thumbs_path', ' WHERE id='.$id);
		$c_head = _CONTENT_CAT_EDIT_HEAD;
	} else {
		$rsar=array("id"=>"","name"=>"","description"=>"","access"=>"0","image"=>"","image_position"=>"left", 'editgroup' => 2);
		$extra = array('thumbs_path' => _GALLERY_DEFAULT_THUMBS, 'gallery_path' => _GALLERY_DEFAULT);
		$c_head = _CONTENT_CAT_NEW_HEAD;
	}
	$gui=new ScriptedUI();
	$gui->add("form","adminform","","admin.php?com_option=gallery&option=categories");
	$gui->add('spacer');
	$gui->add("com_header",$c_head);
	$gui->add("tab_head");
	$gui->add("tab_simple","",$c_head);
	$gui->add("hidden","category_id","",$rsar['id']);
	$v = new ScriptedUI_Validation();
	$v->not_empty = true;
	$gui->add("textfield","category_name",_NAME,$rsar['name'],$v);
	if (!isset($id))
		$gui->add('text', '', _GALLERY_AUTO_CATEGORY_PATHS);
//	$gui->add("textfield","category_gallery_path",_GALLERY_PATH,$extra['gallery_path'],$v);
	$gui->file_browser('category_gallery_path', _GALLERY_PATH, $extra['gallery_path'], null, 'dir', true);
	// the original gallery category path value
	$gui->add("hidden","category_ogallery_path",'',$extra['gallery_path']);
	//$gui->add("textfield","category_thumbs_path",_GALLERY_THUMBS_PATH,$extra['thumbs_path'],$v);
	$gui->file_browser('category_thumbs_path', _GALLERY_THUMBS_PATH, $extra['thumbs_path'], null, 'dir', true);
	// the original gallery category thumbnails path value
	$gui->add("hidden","category_othumbs_path",'',$extra['thumbs_path']);

	if (isset($id)) {
		$gui->add('text', '', _GALLERY_AUTO_CATEGORY_THUMBNAIL);
		//,'','(field_value.indexOf("(")!=-1)||(field_value.indexOf(")")!=-1)||(field_value.indexOf(" ")!=-1)');
		global $d_pic_extensions;
		if (!is_dir($d_root.$extra['thumbs_path'])) {
			$gui->add('text', '', 'Invalid thumbs path');
			$gui->add('hidden', 'category_image', '', $rsar['image']);
		} else {
			$img_arr=select_array($extra['thumbs_path'],_SELECTIMAGE,$rsar['image'],'file',$d_pic_extensions);
			$gui->add("list_image","category_image",_SELECTIMAGE,$img_arr,null,$extra['thumbs_path']);
		}
		$lr_arr=pos_array($rsar['image_position']);
		$gui->add("select","category_image_position",_IMAGEPOS,$lr_arr);
	}
	$gui->add("access","category_access",_ACCESS,$rsar['access']);
	$gui->add("access","category_editgroup",_EDITGROUP,$rsar['editgroup']);
	$gui->add("textarea","category_description",_DESC,$rsar['description'],null,' size="5"');
	if (!isset($id))
		$gui->add('insert_where');

	$gui->add("tab_end");
	$gui->add("tab_tail");
	$gui->add("end_form");
	$gui->generate();
}

function regenerate_thumbnails($ids) {
	global $conn, $d_root;
	include $d_root.'classes/thumbnailer/thumbnailer.php';
	include $d_root.'admin/classes/fs.php';
	$fs = new FS();

	foreach ($ids as $id) {
		$row = $conn->SelectRow('#__gallery', 'catid,url', ' WHERE id='.$id);
		$extra = $conn->SelectRow('#__gallery_category', 'gallery_path,thumbs_path', ' WHERE id='.$row['catid']);
		if (!is_url($row['url'])) {
			if (!Thumbnailer::resize_image($d_root.$extra['gallery_path'].$row['url'], $d_root.$extra['thumbs_path'].$row['url'], _GALLERY_THUMBNAIL_SIZE))
				break;
		} else {
			if (!Thumbnailer::make_thumbnail_from_url($fs, $row['url'], $d_root.$extra['thumbs_path']))
				break;
		}
		
	}
}

?>