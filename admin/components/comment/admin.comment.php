<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require(com_path("html"));

switch($task) {
	case 'publish':
	case 'unpublish':
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		$easydb->data_table($cid, $task, "content_comment","admin.php?com_option=comment");
		break;
	case "edit":
		$id = in_num('id', $_REQUEST);
		if (!isset($id))
			$id = in('cid', __ARR0 | __NUM, $_POST);
		if (isset($id))
			edit_comment($id);
		break;
	case "save":
		$id = in_num('id', $_POST);
		$_name = in_sql('_name', $_POST);
		$_comment = in_sql('_comment', $_POST);
		$conn->Execute('UPDATE #__content_comment SET name = \''.$_name."', comment = '".$_comment.
						"' WHERE id=".$id);
		CMSResponse::Redir("admin.php?com_option=comment");
		break;
	case "delete":
		$ids = in('cid', __ARR | __NUM, $_POST);
		if (!isset($ids))
			CMSResponse::Redir("admin.php?com_option=comment", _FORM_NC);
		foreach($ids as $id) {
			$conn->Delete('#__content_comment', ' WHERE id='.$id);
		}
		CMSResponse::Redir("admin.php?com_option=comment");
		break;
	default:
		comment_table();
		break;
}
?>