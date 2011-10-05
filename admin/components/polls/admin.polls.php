<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

switch($task) {
	case "orderup":
	case "orderdown":
	case "reorder":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		$easydb->data_table($cid, $task, "categories","admin.php?com_option=polls","section='com_polls'");
		break;
	case 'massop':
		$easydb->MassOp('categories','admin.php?com_option=polls', 'section=\'com_polls\'');
		break;
	case "create":
		if ('' === ($poll_title = in_sql('poll_title', $_POST, ''))) 
			CMSResponse::Redir("admin.php?com_option=polls", _FORM_NC);

		$poll_access = in_num('poll_access', $_POST);
		$order=$easydb->neworder("categories","section='com_polls'");
		$conn->Insert('#__categories', '(name,section,access,ordering)', "'$poll_title','com_polls',$poll_access,$order");
		$pollid=$conn->Insert_ID();
		$added = 0;
		for($i=1;$i<11;$i++) {
			if ( (null !== ($opt = in_sql('poll_option'.$i, $_POST))) && ($opt !== '')) {
				$conn->Execute("INSERT INTO #__polls_data (pollid,polloption) VALUES ($pollid,'$opt')");
				$added++;
			}
		}
		$conn->Execute("UPDATE #__categories SET count=".$added." WHERE id=$pollid");
		CMSResponse::Redir("admin.php?com_option=polls");
	break;
	case "save":
		if (	(null === ($poll_access = in_num('poll_access', $_POST))) ||
			('' === ($poll_title = in_sql('poll_title', $_POST, ''))) ||
			('' === ($pollid = in_num('pollid',$_POST, ''))) )
			CMSResponse::Redir("admin.php?com_option=polls", _FORM_NC);
		$conn->Update('#__categories', "name='$poll_title', access='$poll_access'", " WHERE id=$pollid");

		$conn->Execute("DELETE FROM #__polls_data WHERE pollid=$pollid");
		$added = 0;
		for($i=1;$i<11;$i++) {
			if ( (null !== ($opt = in_sql('poll_option'.$i, $_POST))) && ($opt !== '')) {
				$conn->Execute("INSERT INTO #__polls_data (pollid,polloption) VALUES ($pollid,'$opt')");
				$added++;
			}
		}
		$conn->Execute("UPDATE #__categories SET count=".$added." WHERE id=$pollid");
		CMSResponse::Redir("admin.php?com_option=polls");
	break;
	case 'delete':
		if (null === ($cid = in('cid', __ARR | __NUM, $_POST))) {
			CMSResponse::Redir("admin.php?com_option=polls", _FORM_NC);
			break;
		}
		foreach($cid as $val) {
			$conn->Delete('#__polls_votes' , ' WHERE pollid='.$val);
			$conn->Delete('#__polls_data' , ' WHERE pollid='.$val);
			$conn->Delete('#__categories' , ' WHERE id='.$val);
		}
		CMSResponse::Redir("admin.php?com_option=polls");
		break;
	case 'reset':
		if (null === ($cid = in('cid', __ARR | __NUM, $_POST)))
			CMSResponse::Redir("admin.php?com_option=polls", _FORM_NC);
		foreach($cid as $val) {
			$conn->Update('#__polls_data', 'hits=0', ' WHERE pollid='.$val);
			$conn->Delete('#__polls_votes', ' WHERE pollid='.$val);
		}
		CMSResponse::Redir("admin.php?com_option=polls");
		break;
	case 'new':
		edit_polls();
	break;
	case "edit":
		if (null === ($id = in('cid', __ARR0 | __NUM, $_REQUEST)))
			CMSResponse::Redir("admin.php?com_option=polls", _FORM_NC);
		edit_polls($id);
	break;
default:
	polls_table();

}
?>