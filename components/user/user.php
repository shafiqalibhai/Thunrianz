<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include(com_path("html"));

$pathway->add_head(_USER_TITLE);

$task = in_raw('task', $_REQUEST);

// allow user profile deletion only from POST
if (isset($task)) {
	if ($task == 'delete') {
		if (!$params->get('allow_delete', 1)) {
			CMSResponse::Unavailable();
			break;
		}
		$id = in_userid('id', $_POST);
		if ( ($id == $my->id) || ($my->gid>=4)) { }
		else {
			CMSResponse::Unauthorized();
			break;
		}
		$msg = $my->Remove($id);
		if ($msg !== true)
			user_frontpage($msg);
		else
			user_frontpage(_USER_DELETE_PROFILE_SUCCESS);
		return;
	}
} else
	$task = in_raw('task', $_GET, 'info');

switch( $task ) {
	case "details": // was once used as alias for 'edit', now 'for info' instead
	case 'info':
		if ($my->gid < $params->get('info_gid', 1)) {
			CMSResponse::TrivialUnauthorized();
			break;
		}
		$id = in_userid('id', $_GET, $my->id);
		$row=$conn->SelectRow('#__users', '*', " WHERE id=".$id);
		if (empty($row)) {
			CMSResponse::NotFound();
			break;
		}
		$advanced = ($my->gid>=4) || ($my->id = $id);
		if ($my->gid < $row['gid'])
			$advanced = false;
		info_profile($row, $advanced);
	break;
	case 'edit':
		$id = in_userid('id', $_REQUEST, $my->id);
		// only profile owner and managers+ can edit profiles
		if (($id!=$my->id) && ($my->gid<4)) {
			CMSResponse::Unauthorized();
			break;
		}
		$row=$conn->SelectRow('#__users', '*', " WHERE id=".$id);
		if (empty($row)) {
			CMSResponse::NotFound();
			break;
		}
		edit_profile($row);
		break;
	case "success":
		confirmation();
		break;
	case "custom":
		//TODO: offer customization in profile editor
		custom_handler();
		break;
	case "update":
		if( ('' === ($user_name = in('user_name', /*__SQL|*/__NOHTML, $_POST, '', 50))) ||
				('' === ($user_email = in('user_email', /*__SQL|*/__NOHTML, $_POST, '', 100)))
//				('' === ($user_user = in_sql('user_user', $_POST, '', 50))) ||
			) {
			CMSResponse::Redir('index.php?option=user', _FORM_NC);
			break;
		}
		$id = in_userid('id', $_POST, $my->id);
		// can be empty string, means 'Auto'
		$user_lang = in_raw('user_lang', $_POST, '', 2);
		if (!is_email($user_email)) {
			CMSResponse::Redir('index.php?option=user', _EMAIL_NOT_VALID);
			break;
		}
		
		$user_password_orig = in_raw('user_password_orig', $_POST, '', 100);
		
		$rs = $conn->Select('#__users', 'id', ' WHERE id='.$id.' AND password=\''.md5($user_password_orig).'\'');
		if (!$rs->RecordCount()) {
			CMSResponse::Redir('index.php?option=user&Itemid='.$Itemid, _USER_ORIG_PASS_MISMATCH);
			return;
		}
		
		$user_password = in_raw('user_password', $_POST, '', 100);
		$user_password1 = in_raw('user_password1', $_POST, '', 100);

		if (strlen($user_password)) {
			if ($user_password != $user_password1) {
				CMSResponse::Redir('index.php?option=user', _PASS_MATCH);
				return;
			}
		}
		
		if ($my->Modify($id, $user_name, null, $user_password, $user_email, $user_lang, in_raw('user_tz', $_POST),
				null, null))
			user_frontpage(_USER_DETAILS_SAVE);
		else
			user_frontpage($my->errorMsg);
		break;
}

?>