<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

global $banner_extensions;

$banner_extensions = $d_pic_extensions;
$banner_extensions[] = 'swf';
$banner_extensions[] = 'flv';

switch($task) {
	case "publish":
	case "unpublish":
	case "delete":
			$cid = in('cid', __ARR | __NUM, $_POST);
			$easydb->data_table($cid, $task, "banners","admin.php?com_option=banner");
			break;
	case "create":
		if (!content_custom_banner_handle('banner_image_upload', 'banner_imageurl'))
			break;
		if ( (null === ($banner_bannercode = in_sql('banner_bannercode', $_POST))) ||
			 (null === ($banner_clickurl = in_sql('banner_clickurl', $_POST))) ||
			 (null === ($banner_name = in_sql('banner_name', $_POST))) ||
			 (null === ($banner_blanktarget = in_num('banner_blanktarget', $_POST))) ||
			 (null === ($banner_imageurl = in_sql('banner_imageurl', $_POST))) )
			CMSResponse::Redir("admin.php?com_option=banner", _FORM_NC);
			if (trim($banner_clickurl) == 'http://')	$banner_clickurl = '';
		$conn->Insert('#__banners', '(name,imageurl,clickurl,blanktarget,bannercode)', "'$banner_name','$banner_imageurl','$banner_clickurl',$banner_blanktarget,'$banner_bannercode'");	
		CMSResponse::Redir("admin.php?com_option=banner");
	break;
	case "save":
		if (!content_custom_banner_handle('banner_image_upload', 'banner_imageurl'))
			break;
		if ( (0 > ($banner_id = in_num('banner_id', $_POST, -1))) ||
			(null === ($banner_clickurl = in_sql('banner_clickurl', $_POST))) ||
			(null === ($banner_blanktarget = in_num('banner_blanktarget', $_POST))) ||
			 ('' === ($banner_imageurl = in_sql('banner_imageurl', $_POST, '')))
			)
			CMSResponse::Redir("admin.php?com_option=banner", _FORM_NC);
		
		if (trim($banner_clickurl) == 'http://')	$banner_clickurl = '';
		$banner_bannercode = in_sql('banner_bannercode', $_POST, '');
		$banner_name = in_sql('banner_name', $_POST, '');
		if (!strlen($banner_name))
			$banner_name = $banner_imageurl;

		$conn->Update('#__banners',  "name = '$banner_name' , imageurl = '$banner_imageurl' , clickurl = '$banner_clickurl', blanktarget = $banner_blanktarget, bannercode = '$banner_bannercode'", " WHERE id = $banner_id");
		CMSResponse::Redir('admin.php?com_option=banner');
	break;
	case "edit" :
		if (null !== ($id = in('cid', __ARR0 | __NUM, $_REQUEST)))
			edit_banner($id);
		break;
	case "new" :
		edit_banner(null); break;
	default: banner_table(); break;
}

function content_custom_banner_handle($upload_field, $dest_field) {
	global $d_root, $banner_extensions, $d;
	// get the uploaded icon, if any
	include $d_root.'includes/upload.php';
	$upload = in_upload($upload_field, $d->SitePath().'media/banners/', 0,
	$banner_extensions, false);
		if (is_array($upload)) {
			$upload = $upload[0];
			$p = strrpos($upload, '/');
			$upload = substr($upload, $p+1);
			// now set the uploaded image as the selected one
			$_POST[$dest_field] = $upload;
		} else {
			if (strlen($upload)) {
				CMSResponse::Back($upload);
				return false;
			}
		}
	return true;
}

?>