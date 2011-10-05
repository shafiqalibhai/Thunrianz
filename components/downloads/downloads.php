<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require(com_path("html"));

require(com_path('common'));

$task = in_raw('task', $_REQUEST, 'view');

$pathway->add_head(_DOWNLOADS_TITLE);

$_DRABOTS->loadBotGroup('download');

switch ( $task ) {
	case 'new':
		if (!$my->can_submit()) {
			CMSResponse::Unauthorized('', false);
			break;
		}
		submit_download(in_num('catid', $_GET));
	break;
	case 'newdownload':
		if ((null === ($download_catid = in_num('download_catid', $_POST)))
			|| (null === ($download_title = in('download_title', __SQL|__NOHTML, $_POST)))
			)
				CMSResponse::Redir('index.php?option=downloads&task=new&id='.$id, _FORM_NC);
		
		if (!can_submit_into_category($download_catid))
			break;
		
		$download_url = in_raw('download_url', $_POST, '');
		if (!is_url($download_url))	$download_url = '';
		// d_private already contains subsite path
		$root = $d_root.$d_private.'downloads/';
		include_once $d_root.'includes/upload.php';
		$download_file = in_upload('download_file', $root, _DKUPL_RANDOM_PREFIX | _DKUPL_CUSTOM_EXT);
		
		if (is_array($download_file))
			$download_url = $download_file[0];
		else {
			if ($download_file === '') {
				if (!is_url($download_url))
					CMSResponse::Back( _DOWNLOADS_NC);
			} else
				//TODO: give custom category id
				CMSResponse::Redir('index.php?option=downloads&task=new', $download_file);
		}

		// if this is an URL, attempt to retrieve remote file size (Content-Length)
		if (is_url($download_url)) {
			require_once com_path('common');
			$download_filesize = remote_filesize($download_url);
		} else { // normal file on our FS
			$download_filesize = filesize($download_url);
			$download_url = substr($download_url, strlen($d_root));
		}
//		$download_filesize = return_bytes($download_filesize);
	
		$download_description = in('download_description', __SQL|__NOHTML, $_POST);
		$download_website = in('download_website', __SQL|__NOHTML,$_POST);
		$download_author = in('download_author', __SQL|__NOHTML, $_POST);
		
		//TODO: offer customization
		if ($my->can_publish() && can_publish_into_category($download_catid))
			$download_published = in_checkbox('download_published', $_POST, 0);
		else
			$download_published = 2;
		$download_hits = 0;
		$download_image_url = '';
		
		include $d_root.'admin/classes/easydb.php';
		$easydb = new EasyDB();
		$order=$easydb->neworder("downloads");
		$conn->Insert('#__downloads', '(catid,title,author,url,image_url, website,description,filesize,add_date,mod_date,down_date,hits,ordering,published,userid)',
		"$download_catid,'$download_title','$download_author','$download_url', '$download_image_url', '$download_website','$download_description',$download_filesize,$time,$time, 0,$download_hits,$order, $download_published,".$my->id);
		if ($download_published==1)
			change_val('categories', $download_catid, 'count', 1);

		if ($d_event) {
			$username = $my->username;
			if (!strlen($username))
				$username = _ANONYMOUS;
			include_once $d_root.'classes/gelomail.php';
			$m = new GeloMail();
			$m->I18NSendNotify(
				array('_DOWNLOADS_ADDED_SUBJECT', $d_title),
				array('_DOWNLOADS_ADDED_MAIL',
				$d_website,
				$username, $my->id,
				$download_title, $download_author, $download_description, 
				convert_bytes($download_filesize)				
				),
				'downloads');
		}
		CMSResponse::Redir("index.php?option=downloads&task=success&catid=".$download_catid.'&published='.$download_published);
	break;
	case 'download':
		$id = in_num('id');
		if (isset($id)) {
			download_file($id);
			exit();
		}
	break;
	case 'info':
		if (null !== ($id = in_num('id'))) {
			$row = $conn->SelectRow('#__downloads', '*', ' WHERE id='.$id.' AND published=1');
			showitem($row,true);
		}
	break;
	default:
	case 'view':
		$catid = in_num('catid'); //L: needs more checking
		if (isset($catid))
			view_downloads($catid, in_num('list', $_REQUEST, 1), in_num('otype', $_GET, 0), in_num('order', $_GET, 0) );
		else
			view_categories();
	break;
	case 'success':
		$catid = in_num('catid', $_GET);
		$published = in_num('published', $_GET);
		if (isset($catid))
			confirm_submission($catid, $published);
	break;
}

function download_file($id) {
	global $conn,$my,$time,$params,$d;
	$row=$conn->SelectRow('#__downloads', 'catid,url,hits,filesize,flags', " WHERE id=$id AND published=1");
	if (!$row) {
		//NotFound or Unauthorized?
		CMSResponse::Unauthorized();
		return;
	}
	
	global $access_sql;
	$crow = $conn->SelectRow('#__categories', 'id', ' WHERE id='.
			$row['catid'].' '.$access_sql);
	if (!$crow) {
		CMSResponse::Unauthorized();
		return;
	}

	$hits=$row['hits']+1;
	$conn->Update('#__downloads', "down_date=$time,hits=$hits", ' WHERE id='.$id);

//	ob_clean();

	$flags = download_flags($row['flags']);
	
	// perform a SESSION-based antileech check
	if ($flags['antileech']) {
		global $d_uid;
		if (!isset($_SESSION[$d_uid.'-downloads']) ||
			!in_array($id, $_SESSION[$d_uid.'-downloads'])) {
			CMSResponse::Redir('index.php?option=downloads');
			return;
		}
	}
	
	// external URLs and unprotected downloads
	if (is_url($row['url']) || !$flags['protected'])
		header('Location: '.$row['url']);
	else {
		global $d_root;
		include_once $d_root.'includes/download.php';
		global $d_private;
		// allow download of files which are local but are not protected
		//FIXME: there should not be protected files if not under the private directory
		if ($flags['protected'] && strpos($row['url'], $d_private)===0) {
			$fn = basename($row['url']);
			$p=strpos($fn, '_');
			// remove random prefix and custom extension
			if ($p!==false)
				$fn = substr($fn, $p+1, -4);
			else trigger_error('Invalid protected download');
		} else $fn=null;
		download($d_root.$row['url'], filesize($d_root.$row['url']), $fn);
	}
	exit();
}

?>