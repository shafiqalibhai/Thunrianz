<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

$task = in_raw('task', $_REQUEST, '');

switch($task) {
	case 'delete':
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		$easydb->delete_np("forum_users",$cid);
		// remove its backend data
		foreach ($cid as $id) {
			$msg = $my->Remove($id);
			if ($msg !== true) {
				CMSResponse::Redir('admin.php?com_option=user', $msg);
				break 2;
			}
		}
		CMSResponse::Redir('admin.php?com_option=user');
		break;
	case 'publish':
	case 'unpublish':
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		$easydb->data_table($cid, $task, "users", "admin.php?com_option=user");
		break;
	case 'create':
		if( ('' === ($user_name = in_raw('user_name', $_POST, '', 50))) ||
				('' === ($user_user = in_raw('user_user', $_POST, '', 25))) ||
				('' === ($user_email = in_raw('user_email', $_POST, '', 50))) ||
				(0 === ($user_gid = in_num('user_gid', $_POST, 0))) ||
				('' === ($user_password = in_raw('user_password', $_POST, '', 50)))
			) {
			CMSResponse::Redir('admin.php?com_option=user', _FORM_NC);
			break;
		}
		$user_lang = in_raw('user_lang', $_POST, '', 2);
		if (!is_email($user_email)) {
			CMSResponse::Redir('admin.php?com_option=user', _EMAIL_NOT_VALID);
			break;
		}
		
		if ($my->Create($user_name, $user_user,$user_password, $user_email,
					$user_lang, in_raw('user_tz', $_POST), $user_gid))
			CMSResponse::Redir('admin.php?com_option=user');
		else
			show_e_message($my->errorMsg);
		break;

	case 'save':
		if( (null === ($user_id = in_num('user_id', $_POST))) ||
				(0 === ($user_gid = in_num('user_gid', $_POST, 0))) ||
				(null === ($user_email = in_raw('user_email', $_POST, '', 50)))
				) {
			CMSResponse::Redir('admin.php?com_option=user', _FORM_NC);
			break;
		}
		$user_lang = in_raw('user_lang', $_POST, '', 2);
		if (!is_email($user_email)) {
			CMSResponse::Redir('admin.php?com_option=user', _EMAIL_NOT_VALID);
			break;
		}
		
		$user_name = in_raw('user_name', $_POST, '', 50);
		$user_user = in_raw('user_user', $_POST, '', 25);
		$user_password = in_raw('user_password', $_POST, '', 100);
		$user_password1 = in_raw('user_password1', $_POST, '', 100);
		
		if (strlen($user_password)) {
			if ($user_password !== $user_password1) {
				CMSResponse::Redir('admin.php?com_option=user', _ADMIN_USER_ORIG_PASS_MISMATCH);
				break;
			}
		}
		
		//TODO: user_published change
		$r = $my->Modify($user_id,$user_name, $user_user,
				$user_password, $user_email, $user_lang, in_raw('user_tz', $_POST),
				$user_gid, null);
		if ($r)
			CMSResponse::Redir('admin.php?com_option=user');
		else
			CMSResponse::Redir('admin.php?com_option=user', $my->errorMsg);
		break;

	case 'edit' :
		if (null === ($id = in('cid', __ARR0 | __NUM, $_REQUEST)))
			CMSResponse::Redir('admin.php?com_option=user');
		
		edit_users($id);
		;break;

	case 'new' :
		edit_users();
		break;

	default: 
		users_table();
		break;
}


?>