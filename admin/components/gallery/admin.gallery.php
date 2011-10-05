<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Lanius CMS Enhanced Gallery
# @author legolas558
# @version 1.2
# Released under GNU/GPL License
# This component is part of Lanius CMS core
#
# main gallery management component code
# this code contains parts of SGallery 1.0

require com_path("html");

require usr_com_path('functions.php');

// delete the files relative to a gallery record
function delete_photo($url, $extra) {
	global $d_root;
	include_once $d_root.'admin/classes/fs.php';
	$fs = new FS(true);
	if (!is_url($url)) {
		if ($fs->unlink($d_root.$extra['gallery_path'].$url) &&
			$fs->unlink($d_root.$extra['thumbs_path'].$url)) return 1;
	} else {
		if ($fs->unlink($d_root.$extra['thumbs_path'].clear_name($url)))
			return 1;
	}
	return 0;
}

// craft the title and name of a gallery category and assert existance of the needed folders
function assert_dir(&$title, &$name) {
	global $gallery_path,$thumbs_path,$d_root;
	if (!strlen($title))
		$title=$name;
	else if (!strlen($name))
		$name=$title;
	$name = unix_name($name);
	
	include_once $d_root.'admin/classes/fs.php';
	$fs = new FS();

	return ($fs->assertdir($gallery_path.$name) &&
			$fs->assertdir($thumbs_path.$name));
}
	
switch($option) {
case 'upload':
	$cid = in_num('cid');
	if (!isset($cid))
		CMSResponse::Back(_FORM_NC);
	$extra = $conn->SelectRow('#__gallery_category', 'gallery_path,thumbs_path', ' WHERE id='.$cid);
	include $d_root.'admin/classes/upload.php';
	if ($task=='upload') {
		global $time;
		$done = Upload::upload_files($d->SitePath().$extra['gallery_path'], $d_pic_extensions);
		if (count($done)!=0) {
			$prev = $conn->SelectRow('#__categories', 'image', ' WHERE id='.$cid);
			if ($prev['image']=='')
				$conn->Update('#__categories', 'image=\''.
								sql_encode($done[0]).'\'', ' WHERE id='.$cid);
			include $d_root.'classes/thumbnailer/thumbnailer.php';
			foreach ($done as $fname) {
				$order=$easydb->neworder("gallery");
				$conn->Insert('#__gallery', '(catid,title,url,description,date,hits,ordering,published,userid)',"$cid,'".sql_encode(unix_name(substr($fname,0,strrpos($fname,'.'))))."','".$fname.
						"','','$time', 0,$order,0,".$my->id);
				Thumbnailer::resize_image($d_root.$extra['gallery_path'].$fname,
								$d_root.$extra['thumbs_path'].$fname, _GALLERY_THUMBNAIL_SIZE);
			}
		}
		CMSResponse::Redir("admin.php?com_option=gallery&option=items&cid=".$cid);
	} else
		Upload::upload_interface("admin.php?com_option=gallery&option=upload&cid=".$cid,$d_root.$extra['gallery_path'], $d_pic_extensions);
break;

case 'config':
	show_config();
	break;
case 'items':{

switch($task) {
	case 'delete':
		$cid = in_arr('cid', __NUM, $_REQUEST);
		foreach ($cid as $img_id) {
			$row=$conn->SelectRow('#__gallery', 'catid,url', ' WHERE id='.$img_id);
			$extra = $conn->SelectRow('#__gallery_category',
						'gallery_path,thumbs_path', ' WHERE id='.$row['catid']);
			delete_photo($row['url'], $extra);
		}
		// fallback wanted, so that the photos count is updated in proper categories
case "orderup":
case "orderdown":
case "orderchange":
case 'reorder':
case "publish":
case "unpublish":
	if (isset($catid)) {
		$cond = 'catid='.$catid;
		$purl = '&id='.$catid;
	} else $purl = $cond = '';
	$cid = in('cid', __ARR|__NUM, $_REQUEST);
	$easydb->data_table($cid, $task, "gallery","admin.php?com_option=gallery&option=items".$purl, $cond,true);

		break;
case "create":
	// flag to activate wizard
	$batch_create = in_num('batch_create', $_POST);
	//batch create
	if (isset($batch_create)) {
		$batch_catid = in_num('batch_catid', $_POST);
		$batch_unfolded = in_raw('batch_unfolded', $_POST);
		$batch_catname = in_raw('batch_catname', $_POST);
		wizard_handler($batch_catid, $batch_catname, $batch_unfolded);
		break;
	}
	//else insert single photo	
	include_once $d_root.'admin/classes/fs.php';
	$fs = new FS();
	
	$photo_title = in('photo_title', __SQL|__NOHTML, $_POST, '');
	$photo_description = in('photo_description', __SQL|__NOHTML, $_POST);
	$photo_hits = in_num('photo_hits', $_POST, 0);
	$photo_catid = in_num('photo_catid', $_POST, 0);

	include $d_root.'classes/thumbnailer/thumbnailer.php';
	$extra = $conn->SelectRow('#__gallery_category', 'gallery_path,thumbs_path',
							' WHERE id='.$photo_catid);
	include $d_root.'includes/upload.php';
	$root = $d->SitePath().$extra['gallery_path'];
	$photo_upload = in_upload('package_file', $root, 0, $d_pic_extensions);
	if (is_array($photo_upload)) {
		Thumbnailer::resize_image($photo_upload[0],
					$d->SitePath().$extra['thumbs_path'].$photo_upload[1],_GALLERY_THUMBNAIL_SIZE);
		$photo_url = substr($photo_upload[0], strlen($root));
	} elseif ($photo_upload==='') {
			$photo_url = in_raw('package_url', $_POST, 'http://');
			if (is_url($photo_url))
				Thumbnailer::make_thumbnail_from_url($fs, $photo_url, $d_root.$extra['thumbs_path']);
			else
				CMSResponse::Back(_FORM_NC);
	} else
		CMSResponse::Back($photo_upload);

	$photo_max_sz = in_num('photo_max_sz', $_POST, 0);
	if ($photo_max_sz && !is_url($photo_url))
		Thumbnailer::resize_image($root.$photo_url, $root.$photo_url, $photo_max_sz);
	
	// attempt to automatically give a filename
	if (!strlen($photo_title)) {
		$photo_title = basename($photo_url);
//		$photo_title = substr($photo_title, 0, -(strlen(file_ext($photo_title))));
	}
	$photo_url = sql_encode($photo_url);

	$order=$easydb->neworder("gallery");
	$conn->Insert('#__gallery', '(catid,title,url,description,date,hits,ordering,userid)',
		"$photo_catid,'$photo_title','$photo_url','".sql_encode($photo_description)."','$time', $photo_hits,$order,".$my->id);

	CMSResponse::Redir("admin.php?com_option=gallery&option=items&id=".$photo_catid);
break;
case "save":
	$photo_title = in('photo_title', __SQL|__NOHTML, $_POST);
	$photo_description = in('photo_description', __SQL|__NOHTML, $_POST);
	$photo_hits = in_num('photo_hits', $_POST,0);
	$photo_id = in_num('photo_id', $_POST);
	
	$conn->Update('#__gallery', "title='$photo_title' , description='$photo_description', date='$time', hits=$photo_hits", " WHERE id=$photo_id");

	if (in_num('photo_make_thumb', $_POST, 0)==1)
		regenerate_thumbnails(array($photo_id));

	$catid = in_num('photo_ocatid', $_POST);
	CMSResponse::Redir('admin.php?com_option=gallery&option=items'.
					(isset($catid) ? '&id='.$catid : ''));

break;


case "wizard" :
	wizard_interface();
	break;

case "edit":
	$id = in('cid', __ARR0|__NUM, $_REQUEST);
	if (isset($id))
		edit_items($id);
	break;
//case "batch" : batch();break;
case "new" :
	new_photo();
	break;
//L: by legolas558
case 'thumbnails':
	$cid = in('cid', __ARR|__NUM, $_POST);
	if (isset($cid)) {
		regenerate_thumbnails($cid);
		CMSResponse::Redir('admin.php?com_option=gallery&option=items');
	}
break;

default:
	items_table(in_num('id', $_REQUEST));
	break;
}

}break;

/* the categories function handling part */
default:
case "categories" :
switch($task)
{

case "orderup":
case "orderdown":
case "orderchange":
case 'reorder':
//case "publish":
//case "unpublish":
	$cid = in('cid', __ARR|__NUM, $_REQUEST);
	$easydb->data_table($cid, $task, "categories","admin.php?com_option=gallery&option=categories","section='com_gallery'");
	break;
	case 'massop':
		$easydb->MassOp('categories','admin.php?com_option=gallery&option=categories', 'section=\'com_gallery\'');
	break;
case 'delete':
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		foreach($cid as $catid) {
			$urls = $conn->SelectColumn('#__gallery', 'url', ' WHERE catid='.$catid);
			$extra = $conn->SelectRow('#__gallery_category', 'gallery_path,thumbs_path', ' WHERE id='.$catid);
			foreach ($urls as $url) {
				delete_photo($url, $extra);
			}
			$conn->Execute("DELETE FROM #__gallery WHERE catid=".$catid);
			$conn->Execute("DELETE FROM #__categories WHERE id=".$catid);
			$conn->Execute("DELETE FROM #__gallery_category WHERE id=".$catid);
			include_once $d_root.'admin/classes/fs.php';
			$fs = new FS(true);

			$fs->deldir($d_root.$extra['gallery_path']);
			$fs->deldir($d_root.$extra['thumbs_path']);
		}
		//L: maybe following line should be removed?
		$easydb->delete_np("categories", $cid, "section='com_gallery'");
		CMSResponse::Redir("admin.php?com_option=gallery&option=categories");
	break;

case "create":
	$category_name = in_sql('category_name', $_POST);
	
	$category_gallery_path = in_raw('category_gallery_path', $_POST);
	if (!valid_path($category_gallery_path))
		CMSResponse::Back(_GALLERY_INVALID_PATH."\n\n::/".$category_gallery_path);
	$category_thumbs_path = in_raw('category_thumbs_path', $_POST);
	if (!valid_path($category_thumbs_path))
		CMSResponse::Back(_GALLERY_INVALID_PATH."\n\n::/".$category_gallery_path);
	
	$category_description = in_sql('category_description', $_POST);
	$category_access = in_num('category_access', $_POST);
	$category_editgroup = in_num('category_editgroup', $_POST);
	
	if ($category_gallery_path == _GALLERY_DEFAULT) {
		if ($category_thumbs_path != _GALLERY_DEFAULT_THUMBS)
			$category_gallery_path .= substr($category_thumbs_path, strrpos($category_thumbs_path, '/')+1);
		$category_gallery_path .= unix_name($category_name)."/";
	}
	
	if ($category_thumbs_path == _GALLERY_DEFAULT_THUMBS)
		$category_thumbs_path .= substr($category_gallery_path, strrpos(substr($category_gallery_path,0,-1), '/')+1);
	if (substr($category_gallery_path,-1)!='/')
		$category_gallery_path.='/';
	if (substr($category_thumbs_path,-1)!='/')
		$category_thumbs_path.='/';

	include_once $d_root.'admin/classes/fs.php';
	$fs = new FS(false);
	
	$fs->assertdir($d_root.$category_gallery_path);
	$fs->assertdir($d_root.$category_thumbs_path);
	
	$order=$easydb->neworder("categories","section='com_gallery'");
	$conn->Execute("INSERT INTO #__categories ".
	        "(name,section,description,ordering,access,editgroup) ".
	        "VALUES ('$category_name','com_gallery','$category_description',$order,$category_access,$category_editgroup)");
	$conn->Insert('#__gallery_category', '(id,gallery_path,thumbs_path)', $conn->Insert_ID().', \''.sql_encode($category_gallery_path).'\', \''.
				sql_encode($category_thumbs_path)."'");

	CMSResponse::Redir("admin.php?com_option=gallery&option=categories");
	break;
case "save":
	$category_name = in_sql('category_name', $_POST);
	
	$category_gallery_path = in_raw('category_gallery_path', $_POST);
	if (!valid_path($category_gallery_path))
		CMSResponse::Back(_GALLERY_INVALID_PATH."\n\n::/".$category_gallery_path);
	$category_ogallery_path = in_raw('category_ogallery_path', $_POST);
	$category_thumbs_path = in_raw('category_thumbs_path', $_POST);
	if (!valid_path($category_thumbs_path))
		CMSResponse::Back(_GALLERY_INVALID_PATH."\n\n::/".$category_gallery_path);
	$category_othumbs_path = in_raw('category_othumbs_path', $_POST);
	$category_image = in('category_image', __SQL|__PATH, $_POST);
	$category_image_position = in_sql('category_image_position', $_POST);
	$category_description = in_sql('category_description', $_POST);
	$category_access = in_num('category_access', $_POST);
	$category_editgroup = in_num('category_editgroup', $_POST);
	$category_id = in_num('category_id', $_POST);
	if (substr($category_gallery_path,-1)!='/')
		$category_gallery_path.='/';
	if (substr($category_thumbs_path,-1)!='/')
		$category_thumbs_path.='/';

	$msg = '';
	if ($category_gallery_path!==$category_ogallery_path) {
		include_once $d_root.'admin/classes/fs.php';
		$fs = new FS(false);
		
		$err=0;
		$emsg = _GALLERY_MOVE_FAILURE.':';
		if (!$fs->rename($d_root.$category_ogallery_path, $d_root.$category_gallery_path)) {
			$err=1;
			$emsg .= "\n::/".$category_ogallery_path;
		}
		if (!$fs->rename($d_root.$category_othumbs_path, $d_root.$category_thumbs_path)) {
			$err=1;
			$emsg .= "\n::/".$category_othumbs_path;
		}
		if ($err) $msg = $emsg;
	}
	
	$conn->Update('#__categories', "name = '$category_name' , image = '$category_image' ,image_position = '$category_image_position' ,description = '".sql_encode($category_description)."' ,access = $category_access, editgroup = $category_editgroup", " WHERE id = $category_id");
	$conn->Update('#__gallery_category', 'gallery_path=\''.sql_encode($category_gallery_path).'\',thumbs_path=\''.sql_encode($category_thumbs_path).'\'', ' WHERE id='.$category_id);

	
	CMSResponse::Redir("admin.php?com_option=gallery&option=categories", $msg);
	break;

case "edit":
	$id = in('cid', __ARR0|__NUM, $_REQUEST);
	if (isset($id))
		edit_categories($id);
	break;
case "new":
	edit_categories(null);
	break;
case "wizard" :
	wizard_interface();
	break;

default:
	categories_table();
	break;

} break;

}

//edit by legolas558
function show_config()
{
global $conn;
$row=$conn->GetRow("SELECT * FROM #__gallery_config");

$gui=new ScriptedUI();
$gui->add("form","adminform","","admin.php?com_option=gallery&option=config");
$gui->add("com_header",_GALLERY_CONFIG);
$gui->add("tab_head");
$gui->add("tab_simple","",_GALLERY_CONFIG);

$gui->add("textfield","gallery_resize",_GALLERY_AUTO_RESIZE,$row['resize']);
$gui->add("boolean","gallery_first",_GALLERY_FIRST,$row['first']);
$gui->add("tab_end");
$gui->add("tab_tail");
$gui->add("end_form");
$gui->generate();
}

function auto_cat($title, $category_image, $nname = null) {
	global $easydb, $conn;
	if (!isset($nname)) {
		$nname = unix_name(raw_strtolower($title));
		global $d_root;
		include_once $d_root.'admin/classes/fs.php';
		$fs = new FS(false);
		while ($fs->dir_exists($d_root._GALLERY_DEFAULT.$nname.'/'))
			{ $nname .= '_'; }
		if (!$fs->assertdir($d_root._GALLERY_DEFAULT.$nname.'/') ||
			!$fs->assertdir($d_root._GALLERY_DEFAULT_THUMBS.$nname.'/')) {
			global $d;
			CMSResponse::Back(_GALLERY_DIRECTORY_ERROR);
			exit();
		}
	}
	$order=$easydb->neworder("categories","section='com_gallery'");
	$conn->Insert('#__categories','(name,image,image_position,section,description,ordering,access)',
				"'".sql_encode($title)."','".sql_encode($category_image)."','left','com_gallery','',$order,0");
	$id = $conn->Insert_ID();
	$conn->Insert('#__gallery_category', '(id,gallery_path,thumbs_path)', "$id, '".sql_encode(_GALLERY_DEFAULT.$nname."/")."', '".
				sql_encode(_GALLERY_DEFAULT_THUMBS.$nname."/")."'");
	return $id;
}

//by Legolas558
// manages wizard page submission
function wizard_handler($batch_catid, $batch_catname, $batch_unfolded) {
	global $conn,$easydb,$time,$d;
	if (!empty($batch_unfolded)) { // restate a folder without category
		global $d_root;
		include $d_root.'classes/thumbnailer/thumbnailer.php';

		$src_path = $d_root._GALLERY_DEFAULT.$batch_unfolded.'/';
		$dirimgarr=read_dir($src_path, 'file', false, $GLOBALS['d_pic_extensions']);
		
		global $d_root;
		include_once $d_root.'admin/classes/fs.php';
		$fs = new FS();
		$fs->assertdir($d_root._GALLERY_DEFAULT_THUMBS.$batch_unfolded);
		
		$batch_catid = auto_cat($batch_unfolded, $dirimgarr[0], $batch_unfolded);
		generate_thumbnails($batch_catid, $src_path, $dirimgarr); // generate non-existing thumbnails
		foreach($dirimgarr as $photo) {
			$photo = sql_encode($photo);
			$order=$easydb->neworder("gallery");			//create thumbnail and add file 
			$conn->Insert('#__gallery', '(catid,title,url,description,date,hits,published,ordering)',
						"$batch_catid,'$photo','$photo','',$time,0,0,$order");
		}
		CMSResponse::Redir("admin.php?com_option=gallery&option=categories&cid[]=".$batch_catid);
	} else if (!empty($batch_catname))
		$batch_catid = auto_cat($batch_catname, '');
	if (!isset($batch_catid))
		CMSResponse::Back(_FORM_NC);
	else
		CMSResponse::Redir('admin.php?com_option=gallery&option=upload&cid='.$batch_catid);
}

	function generate_thumbnails($catid, $src_path, $files) {
		global $conn, $d_root;
		$row = $conn->SelectRow('#__gallery_category', 'thumbs_path', ' WHERE id='.$catid);
		$thumbs_path = $row['thumbs_path'];
		foreach ($files as $file) {
			$candidate = $d_root.$thumbs_path.$file;
			if (!is_file($candidate)) {
				if (!Thumbnailer::resize_image($src_path.$file, $candidate, _GALLERY_THUMBNAIL_SIZE))
					break;
			}
		}
	}



?>