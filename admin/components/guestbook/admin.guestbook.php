<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

switch($task) {
	case "delete":
		$cid = in_arr('cid', __NUM, $_REQUEST);
		$easydb->delete_np("guestbook", $cid);
		CMSResponse::Redir("admin.php?com_option=guestbook");
		break;
	case "save":
		//name,email,url,country,title,message
		$easydb->Update('guestbook', 'guestbook',
			array('name', 'email', 'url', 'country', 'title', 'message', 'reply'),
			null,
			array(50, 50, 1024, 50, 255, 1024, 1024),
			'id');
//		$conn->Update('#__guestbook', "name = '$guestbook_name', email = '$guestbook_email' , url = '$guestbook_url' , country = '$guestbook_country' , title = '$guestbook_title' ,message = '$guestbook_message',reply = '$guestbook_reply' ".		"WHERE id = $guestbook_id");
		CMSResponse::Redir("admin.php?com_option=guestbook");
		break;
	case "edit":
		$id = in('cid', __ARR0 | __NUM, $_REQUEST);
		edit_guestbook($id);
		break;
	default:
		guestbook_table();
		break;
}

?>