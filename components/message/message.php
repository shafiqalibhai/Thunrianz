<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include com_path('const');
include com_path($d_type);

$task = in_raw('task', $_REQUEST, 'message');

$pathway->add_head(_MESSAGE_TITLE);

switch ( $task ) {
	case 'delmsg':
	case 'delete':
		include $d_root.'components/message/message.common.php';
		delete_task();
		CMSResponse::Redir(CMSResponse::BaseUrl().'&task=inbox');
	break;
	case 'reply':
		if (!$my->isuser()) {
			CMSResponse::Unauthorized();
			break;
		}
		$id = in_num('msg_id', $_POST);
		if (!isset($id))
			$id = in('cid', __ARR0|__NUM, $_REQUEST);
		if (isset($id)) {
			include $d_root.'components/message/message.common.php';
			if (!can_read_message($id))
				break;
			CMSResponse::Redir(CMSResponse::BaseUrl().'&task=message&reply_to='.$id);
		}
	break;
	case "post":
		// show the captcha error message if following criteria are met:
		if	(!$my->isuser()) {		// 1.the user is not registered
			if ($params->get('captcha',1) &&	// 2.the captcha parameter is set
			!$my->valid_captcha('message'))		// 3.the submit captcha is not valid
				break;
			
			if ('' === ($message_sender_name = in_raw('message_sender_name', $_POST, '', 50))
				or '' === ($message_sender_email = in_raw('message_sender_email', $_POST, '', 50))) {
				CMSResponse::Redir('index.php?option=message', _FORM_NC);
				break;
			}
			if (!is_email($message_sender_email)) {
				CMSResponse::Redir('index.php?option=message', _EMAIL_NOT_VALID);
				break;
			}
			// give caesar what belongs to caesar
			$message_sender_username = _ANONYMOUS;
		} else {
			// take them from the database
			$message_sender_name = $my->name;
			$message_sender_email = $my->email;
			$message_sender_username = $my->username;
		}
		
		// check if message has to be stored/sent
		$send_message = (int)$params->get('send_message', '1');
		$store_mode = (int)$params->get('store_mode', '1');
		// invalid configuration
		if (!$send_message && !$store_mode) {
			CMSResponse::Unavailable(_MESSAGE_BROKEN_CFG);
			break;
		}
		
		// check that the reply message is readable and get the destination userid
		$reply_to = in_num('reply_to', $_POST, 0);
		if ($reply_to) {
			include $d_root.'components/message/message.common.php';
			if (!can_read_message($reply_to)) {
				CMSResponse::Unauthorized();
				break;
			}
			$rrow = $conn->SelectRow('#__messages', 'userid', ' WHERE id='.$reply_to);
			$to = $rrow['userid']; // can be 0 for anonymous contact
			$anonym_reply = ($to == 0);
		} else $anonym_reply = false;
		
		$recp_type = $params->get('recp_list', 'name');
		if ($anonym_reply) {
			$recipients = _mk_anonym_recp($recp_type, $reply_to);
		} else {
			// validate the recipient ID
			$recipients = in_arr('message_recipients', __NUM, $_POST);
			if (!isset($recipients) || !count($recipients)) {
				CMSResponse::Redir('index.php?option=message', _FORM_NC);
				break;
			}

			// remove unwanted recipients if not allowed
			$multiple_recp = $params->get('multiple_recp', 0);
			if (!$multiple_recp)
				$recipients = array($recipients[0]);
			
			// a small optimization
			rsort($recipients, SORT_NUMERIC);

			$recipients = _filter_contactable('email'.($store_mode == 2 ? ',lang' : ''),
						' AND ('.each_id($recipients, 'userid').')');
			if (!count($recipients)) {
				CMSResponse::BadRequest();
				break;
			}
		}

		// check if this recipient is not reachable
		if (!$anonym_reply && $reply_to && !isset($recipients[ $to ])) {
			CMSResponse::Unauthorized();
			break;
		}
/*		if (!($row['flags'] &__MESSAGE_FLAG_ALLOW)) {
			CMSResponse::Unauthorized();
			break;
		} */
		// take care of the uploaded file, if allowed
		if ($params->get('allow_attachment', 1) && ($send_message && ($store_mode<2))) {
			include $d_root.'includes/upload.php';
			$upload = in_upload('contact_attach');
		} else
			$upload = '';
		// show the error message, if any
		if (!is_array($upload) && strlen($upload)) {
			CMSResponse::Back($upload);
			break;
		}
		
		$message_max_size = (int)$params->get('message_max_size', 1024);
		if ('' === ($message_subject = in('message_subject', __RAW, $_POST, '', 255))
			or '' === ($message_text = in('message_text', __RAW, $_POST, '', $message_max_size))
			) {
				CMSResponse::Redir('index.php?option=message', _FORM_NC);
				if (is_array($upload)) @unlink($upload[0]);
				break;
		}
		
		// load the mailing code only if necessary
		if ($send_message || ($store_mode == 2)) {
			include $d_root.'classes/gelomail.php';
			$m = new GeloMail();
		}
		// if the message will be stored in the message box area (store_mode == 1 or 2)
		if ($store_mode && !$anonym_reply) {
			$conn->Insert('#__messages', '(name,email,message_subject,message_text,cdate,userid)', '\''.
					sql_encode($message_sender_name)."','".sql_encode($message_sender_email)."','".
					sql_encode($message_subject)."','".sql_encode($message_text)."', ".
					$time.', '.$my->GetID());
			$msg_id = $conn->Insert_ID();
			if ($store_mode == 2) {
				// add all receipts
				$langs = array();
				$emails = array();
				foreach( $recipients as $id => $row) {
					$conn->Insert('#__receipts', '(userid,message_id)', $id.','.$msg_id);
					$emails[] = $row['email'];
					$langs[] = $m->ValidLang($row['lang']);
				}
				// full message content or URL
				if ($send_message)
					$message_ct = sprintf(_MESSAGE_NOTIFY_CONTENT_FULL, $message_text);
				else
					$message_ct = sprintf(_MESSAGE_NOTIFY_CONTENT_URL, $d_website.
							'index.php?option=message&Itemid='.$Itemid.'&task=inbox&view='.$msg_id);
				// prepare the notification message
				$message = array('_MESSAGE_NOTIFY_BODY',
					$d_website,
					_get_recp(array('name' => $message_sender_name,
						'username' => $message_sender_username),
						$recp_type),
					$message_subject,
					$message_ct);
				$m->I18NSend($emails, $langs, array('_MESSAGE_NOTIFY', $d_website),
							$message, $option);
			} else { // (store_mode == 1) add all receipts, no notification message
				foreach( array_keys($recipients) as $id) {
					$conn->Insert('#__receipts', '(userid,message_id)', $id.','.$msg_id);
				}
			}
		} else { // message was not stored, see if we have to fully deliver it (store_mode == 0)
			if (is_array($upload))
				$m->attach = $upload;
				
			// case when send_message == 0 already handled at start
			
			// multiple message send
			if (count($recipients)>1) {
				$dest = array();
				foreach($recipients as $row) {
					$dest[] = $row['email'];
				}
				$m->bcc = $dest;
				$to = null;
			} else {
				// we have only 1 destination recipient, use it explicitely
				$to = current($recipients); $to = $to['email'];
			}
			// send the full delivered email
			$m->Send($to, $message_subject, $message_text,
						$message_sender_email, $message_sender_name);
			// remove the temporary uploaded file
			if (is_array($upload)) @unlink($upload[0]);
		}
		// set the status flag to 'replied', if necessary
		if ($reply_to)
			$conn->Update('#__receipts', 'status=2',
					' WHERE userid='.$my->id.' AND message_id='.$reply_to);
		// trigger action for message sent
		global $_DRABOTS;
		$_DRABOTS->trigger('OnAfterMessageSent', array($message_subject,
			$message_text, $message_sender_email, $message_sender_name));

		CMSResponse::SelfRedir('task=thanks');
	break;
	case 'thanks':
		$d->add_meta(_THANK_MESSAGE);
		thank_message();
	break;
	case 'inbox':
		if (!$my->isuser()) {
			CMSResponse::Unauthorized();
			break;
		}
		message_inbox();
	break;
	case 'view':
		if (!$my->isuser()) {
			CMSResponse::Unauthorized();
			break;
		}
		$id = in('cid', __ARR0 | __NUM, $_REQUEST);
		if (!isset($id)) {
			CMSResponse::NotFound();
		}
		include $d_root.'components/message/message.common.php';
		if (!can_read_message($id))
			return;
		view_message($id);
	break;
	case 'message':
	default:
		// check if message has to be stored/sent
		$send_message = (int)$params->get('send_message', '1');
		$store_mode = (int)$params->get('store_mode', '1');
		if (!$send_message && !$store_mode) {
			CMSResponse::Unavailable(_MESSAGE_BROKEN_CFG);
			break;
		}
		// if user is logged in, then he can reply
		if ($my->isuser())
			$reply_to = in_num('reply_to', $_GET, 0);
		else
			$reply_to = 0;
		// prepare the message to be replied
		if ($reply_to) {
			include $d_root.'components/message/message.common.php';
			// check authorization to access specified message
			if (!can_read_message($reply_to))
				break;
			// retrieve message details
			$rsar = $conn->SelectRow('#__messages', 'message_subject,message_text,userid',
					' WHERE id='.$reply_to);
			$subject = 'Re: '.$rsar['message_subject'];
			$body = "\n> ".preg_replace("/(\\r\\n|\\n)/","\n> ",$rsar['message_text'])."\n";
			$to = $rsar['userid'];
		} else {
			$subject = in('subject', __NOHTML, $_GET, '', 255);
			$body = in('body', __NOHTML, $_GET, '', 1024);
			$to = in_userid('to', $_GET, 0);
		}
		message_form($subject, $body, $to, $reply_to);
}

?>