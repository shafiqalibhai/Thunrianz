<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Registration component
# @author legolas558
#

include(com_path('html'));
include(com_path("common"));

$task = in_raw('task', $_REQUEST, 'register');

$pathway->add_head(_REGISTER_TITLE);

switch ($task ) {
	case "lostpassword":
		$pathway->add(_PROMPT_PASSWORD, "option=register&task=lostpassword&Itemid=$Itemid");
		lostPassForm();
	break;
	// triggered by above lost password form
	case "sendNewPass":
		if ('' === ($checkusername = in_raw('checkusername', $_POST, '', 25))) {
			CMSResponse::Back(_FORM_NC);
			break;
		}
		if ('' === ($confirmEmail = in_raw('confirmEmail', $_POST, '', 100)))
		{
				CMSResponse::Back(_FORM_NC);
				break;
		}
		if (!is_email($confirmEmail)) {
			CMSResponse::Back(_EMAIL_NOT_VALID);
			break;
		}
		
		sendNewPass($checkusername, $confirmEmail );
	break;
	case "register":
		if (allow_reg())
			registerForm( $params->get('registration_activation', 0 ));
		break;
	case "saveRegistration":
		if($params->get('captcha', 1) && !$my->valid_captcha('registration')) break;
		if (!allow_reg())
			break;
		saveRegistration();
	break;
	case 'resend_auth':
		if ($params->get('registration_newpass', 2) != 2) {
			CMSResponse::NotAvailable();
			break;
		}
		if ($my->isuser())
			CMSResponse::Unauthorized();
		else
			authForm('', true);
	break;
	case 'auth':
		if ($params->get('registration_newpass', 2) != 2) {
			CMSResponse::NotAvailable();
			break;
		}
		if ($my->isuser())
			CMSResponse::Unauthorized();
		else {
			$key = in('key', __SQL|__NOHTML, $_GET, '', 32);
			authForm($key, false);
		}
	break;
	case 'confirm_auth_resend':
		if ($params->get('registration_newpass', 2) != 2) {
			CMSResponse::NotAvailable();
			break;
		}
		if ($my->isuser()) {
			CMSResponse::Unauthorized();
			break;
		}
		$email = in_raw('confirm_email', $_POST, '');
		if (!is_email($email)) {
			CMSResponse::Back(_EMAIL_NOT_VALID);
			break;
		}
		$row = $conn->SelectRow('#__users', 'id,name,username,email,lang', ' WHERE email=\''.sql_encode($email)."'");
		if (empty($row)) {
			authDone(_REGISTRATION_INVALID_ACTIVATION);
			break;
		}
		// will pick the old auth key, if any
		$message = _craft_auth_message($row['id'], $row['name'], $row['username'], true);
		if (!isset($message)) {
			authDone(_REGISTRATION_INVALID_ACTIVATION);
			break;
		}
		$subject = array('_REGISTRATION_ACTIVATION_SUBJ', $row['username'], $d_title);
			
		// send the mail message
		global $d_root, $my;
		include_once $d_root.'classes/gelomail.php';
		$m = new GeloMail();
		$m->I18NSendOne($row['email'], $my->ValidLang($row['lang']),
				$subject,
				$message,
				'registration');
		authDone(_REGISTRATION_ACTIVATION_RESENT);
	break;
	case 'confirm_auth':
		if ($params->get('registration_newpass', 2) != 2) {
			CMSResponse::NotAvailable();
			break;
		}
		if ($my->isuser()) {
			CMSResponse::Unauthorized();
			break;
		}
		$key = in('activation_code', __SQL|__NOHTML, $_POST, '', 32);
		$email = in_raw('confirm_email', $_POST);
		$reg_password = in_raw('reg_password', $_POST, '', 100);
		$reg_password2 = in_raw('reg_password2', $_POST, '', 100);

		if (strlen($key) != 32) {
			CMSResponse::BadRequest();
			break;
		}
		if (!isset($email) || !strlen($reg_password)) {
			CMSResponse::Back(_FORM_NC);
			break;
		}
		if ($reg_password !== $reg_password2) {
			CMSResponse::Back(_REGWARN_VPASS2);
			break;
		}
		$row = $conn->SelectRow('#__auth_users', '*', ' WHERE authkey=\''.$key."'");
		if (empty($row)) {
			authDone(_REGISTRATION_INVALID_ACTIVATION);
			break;
		}
		$urow = $conn->SelectRow('#__users', 'name,username,email,lang,timezone',
					' WHERE id='.$row['userid']);
		// same error not to give away precious information
		if ($email !== $urow['email']) {
			authDone(_REGISTRATION_INVALID_ACTIVATION);
			break;
		}
		// proceed to actual user modification and activation
		if (!$my->Modify($row['userid'],$urow['name'], $urow['username'],
				$reg_password, $urow['email'], $urow['lang'],
				$urow['timezone'], null, 1)) {
			authDone($my->errorMsg);
			break;
		}
		// finally delete the wasted authentication key
		$conn->Delete('#__auth_users', ' WHERE authkey=\''.$key."'");
		authDone(_REGISTRATION_ACTIVATION_SUCCESS);
}

function _create_authkey($uid, $old_only = false) {
	global $conn, $time;
	// some cleanup of old handles, 1 month old
	$conn->Delete('#__auth_users', ' WHERE created<'.($time-60*24*30));
	if ($old_only) {
		$row = $conn->SelectRow('#__auth_users', 'authkey', ' WHERE userid='.$uid);
		if (!empty($row))
			return $row['authkey'];
		return null;
	}
	// a pretty random auth key
	$authkey = md5(random_string(40));
	$conn->Insert('#__auth_users', '(userid,authkey,created)', $uid.',\''.$authkey.'\', '.$time);
	return $authkey;
}

function _craft_auth_message($regid, $reg_name, $reg_user, $old_only = false) {
	global $d_website;
	$authkey = _create_authkey($regid, $old_only);
	if (!isset($authkey)) return null;
	// authentication link for password changing
	return array ('_USEND_MSG_ACTIVATE',$reg_name,$d_website,
			$d_website.'index.php?option=registration&task=auth&key='.$authkey,
			$d_website.'index.php?option=registration&task=auth',
			$authkey,
			$reg_user);
}

// returns true if new user registrations are enabled
function allow_reg() {
	global $params;
	if (!$params->get('registration_new', 1) ) {
		CMSResponse::Unauthorized('<br /><br />'._REGISTRATION_DISABLED);
		return false;
	}
	return true;
}

function sendNewPass($checkusername, $confirmEmail) {
	global $conn,$d_title,$Itemid;
	
	$row = $conn->SelectRow('#__users', 'id,username,name,email,lang,timezone'," WHERE username='".
							sql_encode($checkusername)."' AND email='".sql_encode($confirmEmail)."'");
	if(!empty($row)) {
		$user_id = $row['id'];
//		$checkusername = $row['username'];
//		$confirmEmail = $row['email'];
	} else {
		CMSResponse::Redir( 'index.php', _ERROR_PASS );
		return;
	}
	
	global $params, $d_website;
	$d_useractivation = $params->get('registration_newpass', 2);
	
	switch ($d_useractivation) {
		case 0:
			//WARNING: possible loophole if clear_passwords was disabled before password creation
			$prow = $conn->SelectRow('#__users', 'clear_password', ' WHERE id='.$user_id);
		/*	$enewpass = md5($newpass);
			if (	*/
			global $my;
			if (!$my->ClearPwCheck($prow['clear_password']))
				return;
		/*	)
				$newpass = sql_encode($newpass);
			else
				$newpass = '';	*/
			
			$message = array('_USEND_MSG_NTY', $row['name'], $row['username'], $prow['clear_password'],
						$d_website.'index.php?option=login');
			$subject = array('_REGISTRATION_OLDPASS_SUBJ', $row['username'], $d_title);
			break;
		case 1:
			// 10 characters is a more secure password
			$newpass = random_string(10);
			$message = array('_REGISTRATION_NEWPASS_MSG', $row['username'], $newpass);
			// will modify user account only in this case
			global $my;
			if (!$my->Modify($user_id,$row['name'], $row['username'],
						$newpass, $row['email'], $row['lang'], $row['timezone'], null, null)) {
				CMSResponse::Redir('index.php?option=login', $my->errorMsg );
				return;
			}
			$subject = array('_REGISTRATION_NEWPASS_SUBJ', $row['username'], $d_title);
			break;
		case 2: //default:
			$message = _craft_auth_message($user_id, $row['name'], $row['username']);
			$subject = array('_REGISTRATION_ACTIVATION_SUBJ', $row['username'], $d_title);
	}
	
	// send the mail message
	global $d_root;
	include_once $d_root.'classes/gelomail.php';
	$m = new GeloMail();
	$m->I18NSendOne($row['email'], $m->ValidLang($row['lang']),
			$subject,
			$message,
			'registration');
	
	registerDone(_NEWPASS_SENT, 'index.php?option=login');
}

function saveRegistration() {
	global $conn,$my,$params, $d_website,$d_title, $d_deflang;
	
	$d_userpublished = $params->get('registration_published', 1);
	// registration_activation can be 0, 1 (give user password), 2 (give new password) or 3 (give auth link)
	$d_useractivation = $params->get('registration_activation', 3);
	// override published setting, if auth link is specified then the user is unpublished by default
	if ($d_useractivation == 3)
		$d_userpublished = 0;
	
	$fsql = null;
	// $_BL is by default the post variable
	$_BL =& $_POST;
	if ($params->get('xup_support', 1)) {
		global $d_root;
		include $d_root.'includes/upload.php';
		$xup = in_upload('reg_xup');
//		$xup = array('private/temp/xup_profile.xml');
		if (is_array($xup)) {
			// setup an empty variable into which we will store the XML input
			$BL = array();
			$_BL =& $BL;
			include_once $d_root.'classes/anyxml/anyxml.php';
			$xml = new AnyXML();
			$ok = $xml->fromString(file_get_contents($d_root.$xup[0]));
			unlink($d_root.$xup[0]);
			if (!$ok) {
				CMSResponse::Back("Invalid XML package descriptor");
				return;
			}
			$text = $xml->getElementByPath('text/meta');
			$text = $text->attributes('content');
			$hp = $xml->getElementByPath('info/link');
			$hp = $hp->attributes('href');
			$xml = $xml->getElementByPath('info/meta');
			if (!isset($xml)) {
				CMSResponse::Back(_FORM_NC);
				return;
			}
			$a = array();
			foreach($xml as $obj) {
				$a[$obj->attributes('name')] = $obj->attributes('content');
			} $xml = $obj = null;
			// retrieve nickname
			$_BL['reg_user'] = $a['NICKNAME'];
			// retrieve user name
			$_BL['reg_name'] = $a['FN'];
			// retrieve email
			$_BL['reg_email'] = $a['EMAIL'];
			// get something from $_POST too
			if ($d_useractivation <= 1) {
				$_BL['reg_password'] = in_raw('reg_password', $_POST, '', 100);
				$_BL['reg_password2'] = in_raw('reg_password2', $_POST, '', 100);
			}
			$_BL['reg_lang'] = in_raw('reg_lang', $_POST, $d_deflang, 2);
			$row = $conn->SelectRow('#__components', 'id', ' WHERE option_link=\'com_forum\'');
			if ($row) {
				$fsql = "'".sql_encode(xhtml_safe($a['GEO']))."', '".sql_encode(xhtml_safe($a['SIG']))."', '";
				$fsql .= sql_encode(xhtml_safe($text))."', '".sql_encode(xhtml_safe($hp))."'";
			}
		// display the XML error message
		} else if ($xup!=='') {
			CMSResponse::Back($xup);
			return;
		}
	} else $xup = '';
	
	if (
		(
		('' == ($reg_user = in('reg_user', __NOHTML, $_BL, '', 25)))
		|| ('' === ($reg_name = in('reg_name', __NOHTML, $_BL, '', 50)))
		) || (($d_useractivation <= 1) &&
		((0 == strlen($reg_password = in_raw('reg_password', $_BL, '', 100))) ||
		(0 == strlen($reg_password2 = in_raw('reg_password2', $_BL, '', 100))))
		) ) {
			CMSResponse::Redir("index.php?option=registration&task=register", _FORM_NC);
			break;
	}
	
	$reg_lang = in_raw('reg_lang', $_BL, $d_deflang, 2);
	
	if (0 == strlen($reg_email = in_raw('reg_email', $_BL, '', 100))) {
		CMSResponse::Back( _REGWARN_MAIL);
		break;
	}
	if (!is_email($reg_email)) {
		CMSResponse::Back(_EMAIL_NOT_VALID);
		break;
	}
	if ($d_useractivation <= 1) {
		if ($reg_password !== $reg_password2) {
			CMSResponse::Back(_REGWARN_VPASS2);
			break;
		}
	} else 
		// in case of new password mode and activation link mode
		$reg_password = random_string(7);

	$regid = $my->Create($reg_name, $reg_user,$reg_password, $reg_email, $reg_lang,
					in_raw('user_tz', $_POST), 1, $d_userpublished);
	if (!$regid) {
		registerDone($my->errorMsg);
		return;
	}
	
	//FIXME: this reference to com_forum should not be here
	//TODO: create a drabot which processes additional XUP data
	if (isset($fsql)) {
		$conn->Insert('#__forum_users', '(id,posts,location,signature,information,url)',
				$regid.",0,".$fsql);
	}

	$local_msg = '';
	$subject = array ('_REGISTRATION_OLDPASS_SUBJ', $reg_name, $d_title);
	
	if ($d_useractivation <= 2) {
		// proper message when user needs approval or not
		if ($d_userpublished) {
			$local_msg .= _REGISTRATION_CAN_LOGIN;
			$login_msg = _REGISTRATION_MAY_LOGIN;
		} else {
			$local_msg .= _REGISTRATION_NOT_CAN_LOGIN;
			$login_msg = _REGISTRATION_MAY_NOT_LOGIN;
		}
		$login_msg = sprintf($login_msg, $d_website."index.php?option=login");
	}

	// user activation switch
	switch ($d_useractivation) {
		case 0: // do not send any password
			$message = array('_USEND_MSG_NOPASS', $reg_name, $d_website, $login_msg);
		break;
		case 1: // send user-provided password
		case 2: // send automatically generated password
			// add note about password being also sent
			if ($d_useractivation == 2)
				$local_msg .= ' '._REGISTRATION_CAN_LOGIN_NPW;
			else
				$local_msg .= ' '._REGISTRATION_CAN_LOGIN_PW;
			$message = array ('_USEND_MSG',$reg_name,$d_website,
					$reg_user,$reg_password, $login_msg); $login_msg = null;
		break;
		case 3: // send authentication link
			$message = _craft_auth_message($regid, $reg_name, $reg_user);
			$local_msg .= _REGISTRATION_COMPLETE_ACTIVATE;
		break;
	}

	// send the registration email
	global $d_root;
	include_once $d_root.'classes/gelomail.php';
	$m = new GeloMail();
	$m->I18NSendOne( $reg_email, $m->ValidLang($reg_lang),$subject,$message, 'registration');
	
	// send the notification event
	if ($params->get('notify', 1)) {
		$m->I18NSendNotify(
				array('_REGISTRATION_NOTIFY_SUBJECT', $d_title),
				array('_REGISTRATION_NOTIFY_MAIL', 
				$d_website, $reg_user, $regid),
				'registration');
	}
	registerComplete($local_msg);
}

?>