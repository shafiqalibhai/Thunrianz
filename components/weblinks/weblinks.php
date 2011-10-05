<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include(com_path($d_type));
include(com_path("common"));

$task = in_raw('task', $_REQUEST, 'view');
$id = in_num('id', $_REQUEST);

$pathway->add_head(_WEBLINKS_TITLE);

switch ($task) {
	case "visit":
		$row=$conn->SelectRow('#__weblinks', 'url', " WHERE id = $id");
		if (empty($row)) {
			CMSResponse::NotFound();
			break;
		}
		change_val("weblinks",$id,"hits",1);
		CMSResponse::SeeOther($row['url']);
	break;
	case "new":
		submitlink();
	break;
	case "newlink":
		if ((null === ($link_catid = in_num('link_catid', $_POST)))
			|| (null === ($link_title = in('link_title', __SQL|__NOHTML, $_POST)))
			|| (null === ($link_url = in('link_url', __SQL|__NOHTML, $_POST))) )
				CMSResponse::Redir('index.php?option=weblinks&task=new&id='.$id, _FORM_NC);
		$link_description = in('link_description', __SQL|__NOHTML, $_POST);
		
		if (!can_submit_into_category($link_catid))
			break;
		
		include $d_root.'admin/classes/easydb.php';
		$easydb = new EasyDB();
		$order = $easydb->neworder('weblinks');
/*
		if (!EasyDB::Insert('weblinks', 'link',
						array('catid', 'title', 'url'),
						array(__NUM, __SQL|__NOHTML, __SQL|__NOHTML),
						'ordering',
						array('published' => 2, 'date' => $time, 'ordering' => $order)
						))
					CMSResponse::Back(_FORM_NC);
*/

		$conn->Insert('#__weblinks', '(catid,title,url,description,published,date,ordering,userid)',
				"$link_catid, '"
				. $link_title ."', '"
				. $link_url ."', '"
				. $link_description . "', 2, '$time', $order,".$my->id);

		if($d_event) {
			include_once $d_root.'classes/gelomail.php';
			$m = new GeloMail();
			$m->I18NSendNotify(
				array('_WEBLINKS_ADDED_SUBJECT', $d_title),
				array('_WEBLINKS_ADDED_MAIL', 
				$d_website, $my->username, $my->id,
				$link_title, $link_url, $link_description),
				'weblinks');
		}
		CMSResponse::Redir("index.php?option=weblinks&task=success&catid=".$link_catid);
	break;
	case 'success':
		$catid = in_num('catid', $_GET);
		if (isset($catid))
			confirm_submission($catid);
		break;
	default:
	case 'view':
		$catid = in_num('catid', $_GET);
		view_links($catid);
}

?>