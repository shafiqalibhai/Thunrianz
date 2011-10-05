<?php  if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once usr_com_path('common.php');

require_once(com_path("html"));

function get_download_url(&$download_filesize, $protected = true, $prot_changed = false, $created = true) {
	global $d, $d_root, $d_private;
	include $d_root.'includes/upload.php';
	$root = $d->SitePath();
	$upload = in_upload('package_file', $root.$d_private.'downloads/', $protected ? _DKUPL_RANDOM_PREFIX | _DKUPL_CUSTOM_EXT : 0);
	$uploaded = is_array($upload);
	if ($uploaded) {
		$download_url = substr($upload[0], strlen($root));
	} else if ($upload==='') {
		$download_url = in_raw('package_url', $_POST, 'http://');
		if (!is_url($download_url))
			$download_url = '';
		$uploaded = true;
	} else {
		CMSResponse::Back($upload);
		return null;
	}

	if ($download_url==='') {
		$download_url = in_raw('package_dir', $_POST, '');
		if (!strlen($download_url)) {
			CMSResponse::Back('No download path or url specified!');
			return null;
		}
	}

	// remote file size
	if (is_url($download_url))
		$download_filesize = remote_filesize($download_url);
	else {
		// if the file is local and the protected flag changed, then proceed to rename it (with or without prefix)
		if (!$uploaded) {
			if ($prot_changed) {
				include_once $d_root.'admin/classes/fs.php';
				$fs = new FS(false);
				$orig_file = $download_url;
				if ($protected) {
					$p = strrpos($download_url, '/');
					$download_url = substr_replace($download_url, random_string(8).'_', $p+1, 0);
				} else {
					$p=strrpos($download_url, '_')+1;
					$download_url = substr($download_url, 0, strrpos($download_url, '/')+1).substr($download_url, $p);
				}
				$fs->rename($root.$orig_file, $root.$download_url);
			} else if ($created && $protected) {
				$p=strrpos($download_url, '_')+1;
				$download_url = substr($download_url, 0, strrpos($download_url, '/')+1).substr($download_url, $p);
				$fs->rename($root.$orig_file, $root.$download_url);
			}
		}

		if (!is_file($root.$download_url))
			$download_filesize = 0;
		else
			$download_filesize = filesize($root.$download_url);
	}

	return $download_url;
}

switch($option) {
case "items":{

switch($task) {
case 'massop':
	$catid = in_num('catid', $_REQUEST);
	if (isset($catid))
		$catid = '&catid='.$catid;
	else $catid = '';
	$easydb->MassOp('downloads','admin.php?com_option=downloads&option=items'.$catid);
	break;
case "delete":
	include_once $d_root.'admin/classes/fs.php';
	$fs = new FS(true);
	$cid = in_arr('cid', __NUM, $_REQUEST);
	foreach($cid as $id) {
		$row = $conn->SelectRow('#__downloads', 'url',  ' WHERE id='.$id);
		// remove only non-URL download files
		if (!is_url($row['url']) && (strpos($row['url'], $d_private)===0))
			$fs->remove($d_root.$row['url']);
	}
case "orderup":
case "orderdown":
case "orderchange":
case "reorder":
case "publish":
case "unpublish":
	$catid = in_num('catid', $_REQUEST);
	if (isset($catid)) {
		$cond = 'catid='.$catid;
		$purl = '&catid='.$catid;
	} else $purl = $cond = '';
	$cid = in('cid', __ARR|__NUM, $_REQUEST);
	$easydb->data_table($cid, $task, "downloads","admin.php?com_option=downloads&option=items".$purl, $cond,true);
	break;

case "itemdelete":
	$download_id = in_num('download_id', $_POST);
	$conn->Execute('DELETE FROM #__downloads WHERE id='.$download_id);
	$table_page = in_num('table_page', $_REQUEST, 1);
	CMSResponse::Redir('admin.php?com_option=downloads&option=items'.
					( ($table_page != 1) ? '&table_page='.$table_page : ''));
	break;

case "create":
	$download_catid = in_num('download_catid', $_POST);
	$download_title = in_sql('download_title', $_POST, null, 255);
	$download_author = in_sql('download_author', $_POST, null, 100);
	// for non-admin users downloads are always protected
	$download_protected = in_num('download_protected', $_POST, 1);
	$download_antileech = in_num('download_antileech', $_POST);

	if (!$my->is_admin())
		$download_protected = 1;

	$download_url = get_download_url($download_filesize, $download_protected, false, true);
	if (!isset($download_url))
		break;

	$download_image_url = in_sql('download_image_url', $_POST);
	$download_website = in_sql('download_website', $_POST);
	$download_description = in_sql('download_description', $_POST);
	$download_published = in_num('download_published', $_POST);
	if ($download_published == 1)
		change_val('categories', $download_catid, 'count', 1);
//	$download_filesize = in_sql('download_filesize', $_POST);
	$download_hits = in_num('download_hits', $_POST, 0);

	$order=$easydb->neworder("downloads");
	$download_filesize = return_bytes($download_filesize);
	$download_url = sql_encode($download_url);

	$download_flags = mk_download_flags(array('protected' => $download_protected,
									'antileech' => $download_antileech));

	$conn->Insert('#__downloads', '(catid,title,author,url,image_url, website,description,filesize,add_date,mod_date,down_date,hits,ordering,published,flags,userid)', "$download_catid,'$download_title','$download_author','$download_url', '$download_image_url', '$download_website','$download_description',$download_filesize,$time,$time, 0,$download_hits,$order,$download_published,$download_flags,".$my->id);
	// should not redirect to specific category if we were not browsing a category
	// ^ or not?
//	$catid = in_num('catid', $_GET);
	$table_page = in_num('table_page', $_REQUEST, 1);
	CMSResponse::Redir('admin.php?com_option=downloads&option=items'.
					'&catid='.$download_catid.( ($table_page != 1) ? '&table_page='.$table_page : ''));
	break;

case "save":
	$download_id = in_num('download_id', $_POST);
	$download_catid = in_num('download_catid', $_POST);
	$download_ocatid = in_num('download_ocatid', $_POST);
	$download_title = in_sql('download_title', $_POST, null, 255);
	$download_author = in_sql('download_author', $_POST, null, 100);
	$download_url = in_sql('download_url', $_POST);
	$download_image_url = in_sql('download_image_url', $_POST);
	$download_website = in_sql('download_website', $_POST);
	$download_description = in_sql('download_description', $_POST);
	$download_published = in_num('download_published', $_POST);
	$download_antileech = in_num('download_antileech', $_POST);
	// downloads are always protected for non-admin users
	$download_protected = in_num('download_protected', $_POST, 1);
	$download_o_protected = in_num('download_o_protected', $_POST);
//	$download_filesize = in_sql('download_filesize', $_POST);
	$download_hits = in_num('download_hits', $_POST, 0);

	if (!$my->is_admin())
		$download_protected = 1;

	$download_url = get_download_url($download_filesize, $download_protected,
								$download_o_protected!=$download_protected);
	if (!isset($download_url))
		break;

	$download_filesize = return_bytes($download_filesize);

	$download_flags = mk_download_flags(array('protected' => $download_protected,
									'antileech' => $download_antileech));

	$download_url = sql_encode($download_url);
	$conn->Update('#__downloads', "catid = $download_catid , title = '$download_title' , author='$download_author', url = '$download_url' , image_url = '$download_image_url', website='$download_website',description='$download_description',  filesize=$download_filesize, mod_date=$time, hits=$download_hits , published = $download_published, flags = $download_flags", " WHERE id = $download_id");
	// fix the totals in case of changed category
	$easydb->check_category('downloads',$download_id,$download_catid,$download_ocatid);

	$catid = in_num('catid', $_GET);
	$table_page = in_num('table_page', $_REQUEST, 1);
	CMSResponse::Redir('admin.php?com_option=downloads&option=items'.
					(isset($catid) ? '&catid='.$catid : '').( ($table_page != 1) ? '&table_page='.$table_page : ''));
	break;
case "edit" :
	$id = in('cid',__ARR0|__NUM,$_REQUEST);
	$catid = in_num('catid', $_GET);
	if (isset($id))
		edit_items($id, $catid);
	break;
case "new":
	edit_items(null, in_num('catid', $_REQUEST));
	break;
default:
	$id = in_num('catid', $_REQUEST);
	items_table($id);
	break;
}

}break;

/* the categories function handling part */
case "categories" :
switch($task) {
	case 'massop':
		$easydb->MassOp('categories','admin.php?com_option=downloads&option=categories', 'section=\'com_downloads\'');
	break;
case "orderup":
case "orderdown":
case "orderchange":
case "reorder":
//case "publish":
//case "unpublish":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		$easydb->data_table($cid, $task, "categories","admin.php?com_option=downloads&option=categories","section='com_downloads'");
		break;
case "delete":
	$cid = in_arr('cid', __NUM, $_REQUEST);
	foreach($cid as $id) {
		$conn->Delete('#__downloads', ' WHERE catid='.$id);
	}
	$easydb->delete_np("categories", $cid, "section='com_downloads'");
	CMSResponse::Redir("admin.php?com_option=downloads&option=categories");
	break;
case "create":
	$category_description = in_sql('category_description', $_POST);
	$category_name = in_sql('category_name', $_POST);
	$section_image = in_sql('section_image', $_POST);
	$section_image_position = in_sql('section_image_position', $_POST);
	$category_access = in_num('category_access', $_POST);
	$category_editgroup = in_num('category_editgroup', $_POST);

	$order=$easydb->neworder("categories","section='com_downloads'");
	$conn->Insert('#__categories', '(name,image,image_position,section,description,ordering,access,editgroup)',
		"'$category_name','$section_image','$section_image_position','com_downloads','$category_description',$order,$category_access,$category_editgroup");
	CMSResponse::Redir("admin.php?com_option=downloads&option=categories");
	break;
case "save":
	$category_description = in_sql('category_description', $_POST);
	$category_name = in_sql('category_name', $_POST);
	$section_image = in_sql('section_image', $_POST);
	$section_image_position = in_sql('section_image_position', $_POST);
	$category_access = in_num('category_access', $_POST);
	$category_editgroup = in_num('category_editgroup', $_POST);
	$category_id = in_num('category_id', $_POST);
	$conn->Update('#__categories', "name = '$category_name' ,image = '$section_image' ,image_position = '$section_image_position' , description = '".$category_description."' ,access = $category_access, editgroup = $category_editgroup", " WHERE id = $category_id");
	CMSResponse::Redir("admin.php?com_option=downloads&option=categories");
	break;
case "edit":
	$sec_id = in_num('sec_id');
	$id = in('cid', __ARR0|__NUM, $_REQUEST);
	edit_categories($sec_id, $id);
	break;
case "new":
	$sec_id = in_num('sec_id');
	//$cid = in('cid', __ARR|__NUM, $_REQUEST);
	edit_categories($sec_id);
	break;
default:
	$id = in_num('catid');
	categories_table($id);
	break;
} break;
}

?>
