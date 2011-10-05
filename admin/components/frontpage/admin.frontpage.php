<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

$easydb->rev_order = true;

function content_publication($cid, $pub) {
	global $conn;
	foreach($cid as $id) {
		$conn->Update('#__content', 'published='.$pub, ' WHERE id='.$id);
	}
}

switch($task) {
	case "orderup":
	case "orderdown":
	case "reorder":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		$easydb->data_table($cid, $task, "content_frontpage","admin.php?com_option=frontpage");
		break;
	case 'massop':
		$easydb->MassOp('content','admin.php?com_option=frontpage');
		break;
	case "delete":
		$cid = in_arr('cid', __ARR|__NUM, $_REQUEST);
			foreach($cid as $id) {
				$conn->Execute('UPDATE #__content SET frontpage=0 WHERE id='.$id);
				$conn->Execute('DELETE FROM #__content_frontpage WHERE id='.$id);
			}
			CMSResponse::Redir('admin.php?com_option=frontpage');
	break;
	case "publish":
		$cid = in_arr('cid', __ARR|__NUM, $_REQUEST);
		content_publication($cid, 1);
		CMSResponse::Redir('admin.php?com_option=frontpage');
	break;
	case "unpublish":
		$cid = in_arr('cid', __ARR|__NUM, $_REQUEST);
		content_publication($cid, 0);
		CMSResponse::Redir('admin.php?com_option=frontpage');
	break;
	default: frontpage_table(); break;

} 


?>