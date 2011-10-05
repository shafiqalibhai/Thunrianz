<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
include(com_path("html"));

$task = in_raw('task', $_REQUEST, 'view');

$pathway->add_head(_GALLERY_TITLE);

switch ( $task ) {
	case "show":
		if (null !== ($id = in_num('id', $_GET)))
			show_pic($id);
	break;
	case "new":
		if (!$my->can_submit()) {
			CMSResponse::Unauthorized('', false);
			break;
		}
		submit_gallery(in_num('catid', $_GET));
		break;
	case 'newgallery':
		$gallery_catid = in_num('gallery_catid', $_POST);
		if (!isset($gallery_catid))
			break;
		
		if (!can_submit_into_category($gallery_catid))
			break;

		if (null === ($gallery_title = in('gallery_title', __SQL|__NOHTML, $_POST)))
			CMSResponse::Redir('index.php?option=gallery&task=new&catid='.$gallery_catid, _FORM_NC);

		$extra = $conn->SelectRow('#__gallery_category', 'thumbs_path,gallery_path', ' WHERE id='.$gallery_catid);
		if (!count($extra)) {
			CMSResponse::Unauthorized('', false);
			break;
		}

		if ($my->can_publish() && can_publish_into_category($gallery_catid)) {
			$gallery_published = in_checkbox('gallery_published', $_POST,0);
		} else
			$gallery_published = 2;
	
		$gallery_url = in_raw('gallery_url', $_POST, '');
		if (!is_url($gallery_url))	$gallery_url = '';
		//L: upload in specified gallery category folder without random prefix using only the pic extensions and without overwriting
		include_once $d_root.'includes/upload.php';
		$root = $d->SitePath().$extra['gallery_path'];
		$gallery_file = in_upload('gallery_file', $root, 0, $d_pic_extensions, false);
		
		$thumb_file = null;
		if (is_array($gallery_file)) {
			$gallery_url = substr($gallery_file[0], strlen($root));
			if ($params->get('send_thumbnail', true)) {
				include $d_root.'classes/thumbnailer/thumbnailer.php';
				$thumb_file = $d->SitePath().$extra['thumbs_path'].$gallery_url;
				if (!Thumbnailer::resize_image($gallery_file[0],
					$thumb_file, _GALLERY_THUMBNAIL_SIZE))
					$thumb_file = null;
				// is MIME validated?
				$thumb_mime = $gallery_file[2];
			}
		} else {
			if ($gallery_file === '') {
				if (!is_url($gallery_url))
					CMSResponse::Back('Please submit a valid url or an upload file');
				else {
					if ($params->get('send_thumbnail', true)) {
							include $d_root.'admin/classes/fs.php';
							$fs = new FS();
							include $d_root.'classes/thumbnailer/thumbnailer.php';
					if (Thumbnailer::make_thumbnail_from_url($fs, $gallery_url, $extra['thumbs_path'])) {
							$thumb_file = clear_name($gallery_url);
							$ext = file_ext($thumb_file);
							global $mime;
							include_once $d_root.'includes/download.php';
							if (!isset($mime[$ext]))
								$thumb_mime = $mime['exe'];
							else $thumb_mime = $mime[$ext];
							$thumb_file = $d_root.$extra['thumbs_path'].$thumb_file;
						}
					}
				}
			} else
				CMSResponse::Back($gallery_file);
		}
		
		$gallery_description = in('gallery_description', __NOHTML, $_POST);
		
		//TODO: offer customization for hits?
		$gallery_hits = 0;
		
		include $d_root.'admin/classes/easydb.php';
		$easydb = new EasyDB();
		$order=$easydb->neworder("gallery");
		//TODO: do not encode prior to notification sending!
		$conn->Insert('#__gallery', '(catid,title,description,url,date,hits,published,ordering,userid)',
			"$gallery_catid,'$gallery_title','".sql_encode($gallery_description)."','$gallery_url',$time,$gallery_hits, $gallery_published, $order,".$my->id);
		if ($gallery_published == 1)
			change_val('categories', $gallery_catid, 'count', 1);

		if($d_event) {
			if (is_url($gallery_url))
				$full_url = $gallery_url;
			else
				$full_url = $d_website.$extra['gallery_path'].$gallery_url;
			include_once $d_root.'classes/gelomail.php';
			$m = new GeloMail();
			if (isset($thumb_file))
				$m->attach = array($thumb_file, basename($thumb_file), $thumb_mime);
			$m->I18NSendNotify(
				array('_GALLERY_ADDED_SUBJECT', $d_title),
				array('_GALLERY_ADDED_MAIL', 
				$d_website, $my->username, $my->id,
				$gallery_title, $full_url, $gallery_description),
				'gallery');
		}
		
		CMSResponse::Redir("index.php?option=gallery&task=success&catid=".$gallery_catid);
	break;
	case "view":
		$catid = in_num('catid', $_REQUEST);
		if (isset($catid))
			view_gallery($catid);
		else
			view_category();
	break;
	case 'success':
		$catid = in_num('catid', $_GET);
		if (isset($catid))
			confirm_submission($catid);
	break;

}
?>