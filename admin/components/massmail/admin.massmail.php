<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

switch($task) {
	case "send":
		$gid='';
		if (!$d_force_text_email) {
			$massmail_text = in_num('massmail_text', $_POST);
			$massmail_message = in_area('massmail_message', $_POST, '');
		} else {
			$massmail_message = in_raw('massmail_message', $_POST, '');
			$massmail_text = false;
		}
		
		$massmail_inclusive = in_num('massmail_inclusive', $_POST);
		$massmail_gid = in_num('massmail_gid', $_POST, 1, 5);
		$massmail_subject = in_raw('massmail_subject', $_POST);
		if (!strlen($massmail_message))
			CMSResponse::Back(_FORM_NC);
		if ($massmail_inclusive) $op='>=';else $op='=';
		if($massmail_gid) $gid='AND gid'.$op.$massmail_gid;
		$sent=0;
		include $d_root.'classes/gelomail.php';
		$m = new GeloMail(!$d_force_text_email && !$massmail_text);
		$rsa=$conn->SelectArray('#__users', 'email,name,username',
				" WHERE published=1 $gid");
		foreach($rsa as $row) {
			$tag=array('{email}','{name}','{username}');
			$ntag=array($row['email'],$row['name'],$row['username']);
			$massmail_subject_2=str_replace($tag,$ntag,$massmail_subject);
			$massmail_message_2=str_replace($tag,$ntag,$massmail_message);
			if ($m->Send( $row['email'], $massmail_subject_2, $massmail_message_2, $my->email, $my->name))
				$sent++;
		}
		CMSResponse::Redir('admin.php', $sent.' '._MASSMAIL_SENT);
	break;

	default:
		edit_massmail();
	break;
}


?>