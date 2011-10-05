<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function can_read_message($id) {
	global $conn, $my;
	$row = $conn->SelectRow('#__receipts', 'status', ' WHERE userid='.$my->id.' AND message_id='.$id);
	if (!empty($row)) {
		// check if message has been read
		if ($row['status']==0)
			$conn->Update('#__receipts', 'status=1', ' WHERE userid='.$my->id.' AND message_id='.$id);
		return true;
	}
	CMSResponse::Unauthorized();
	return false;
}

function _message_table_data($extra = '') {
	global $my, $conn;
	$ids = $conn->SelectColumn('#__receipts', 'message_id',
				' WHERE userid='.$my->id.' ORDER BY message_id DESC');
	if (count($ids)) {
		$statuses = $conn->SelectColumn('#__receipts', 'status',
				' WHERE userid='.$my->id.' ORDER BY message_id DESC');
	
		$table_data=$conn->SelectArray('#__messages', 'id,name,email,message_subject,cdate'.$extra,
			' WHERE '.each_id($ids).' ORDER BY id DESC');
		$c=count($table_data);
		$smatrix = array( 0 => '<strong>'._TB_NEW.'</strong>',
						1 => _MSG_STATUS_READ,
						2 => '<em style="color:navy;">'._MSG_STATUS_REPLIED.'</em>'
					);
		// add the status
		for($i=0;$i<$c;++$i) {
			$table_data[$i]['status'] = $smatrix[ (int)$statuses[$i] ];
		}
	} else
		$table_data = array();
	return $table_data;
}

function _message_view(&$gui, &$rsar) {
	// check messaging options
	global $conn;
	$to = $rsar['userid'];
	if ($to) {
		$row = $conn->SelectRow('#__contacts', 'flags', ' WHERE userid='.$to);
		if (empty($row)) {
			$html = 0;
			$allow = 1;
		} else {
			include_once usr_com_path('const.php', 'message');
			$allow = ($row['flags'] & __MESSAGE_FLAG_ALLOW);
			$html = ($row['flags'] & __MESSAGE_FLAG_HTML);
		}
	} else {
		$html = 0; $allow = 1;
	}
	global $my;
	// email address shown only to administrative personnel
	$show_email =  !$to || ($my->gid>=4);

	$gui->add('hidden', 'message_id', '', $rsar['id']);
	// prepare the from field
	$from = $rsar['name'];
	if ($to && ($allow | $show_email))
		$from .= '&nbsp;&nbsp;<sub>[';
	if ($to && $allow)
		$from .= '<a href="'.
		xhtml_safe(CMSResponse::BaseUrl().'&task=message&to='.$to).'">'.
		_MESSAGE_SEND_PM.'</a>]';
	if ($show_email)
		$from .= '&nbsp;[<a href="mailto:'.$rsar['email'].'">'._EMAIL.'</a>]';
	if ($allow | $show_email)
		$from .= '</sub>';
	$gui->add('text',"",_MSG_FROM, $from);
	$gui->add("text","",_MSG_SUBJECT,'<pre>'.xhtml_safe($rsar['message_subject']).'</pre>');
	$gui->add('spacer');
	$gui->add("text","",_MSG_MESSAGE,'<pre>'.xhtml_safe($rsar['message_text']).'</pre>');
}

// remove a message if it has no more receipts
function _check_no_receipts($ids) {
	global $conn;
	foreach($ids as $id) {
		$msgc = $conn->SelectCount('#__receipts', '*', ' WHERE message_id='.$id);
		if (!$msgc)
			$conn->Delete('#__messages', ' WHERE id='.$id);
	}
}

function delete_task() {
	global $my, $conn;
	if (!$my->isuser()) {
		CMSResponse::Unauthorized();
		return false;
	}
	$cid = in_arr('cid', __NUM, $_POST);
	if (!isset($cid)) {
		$cid = in_num('message_id', $_POST);
		if (!isset($cid))
			return false;
		$cid = array($cid);
	}
	$conn->Delete('#__receipts', ' WHERE userid='.$my->id.' AND ('.each_id($cid, 'message_id').')');
	_check_no_receipts($cid);
	return true;
}

?>