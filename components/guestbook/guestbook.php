<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include(com_path("html"));

$task = in_raw('task', $_REQUEST, 'view');

$pathway->add_head(_GUESTBOOK_TITLE);

switch ( $task ) {
case "sign":
	$pathway->add(_GUESTBOOK_SIGN, 'option=guestbook&task=sign&Itemid='.$Itemid);
	post_entry();
break;

case "insert" :
	if (!$my->gid) {
		if ('' === ($gb_name = in('gb_name', __SQL | __NOHTML, $_POST, '', 50))
			|| ('' === ($gb_email = in('gb_email', __SQL | __NOHTML, $_POST, '', 50)))
			) {
			CMSResponse::Back(_FORM_NC);
			break;
		}
		if (!is_email($gb_email)) {
			CMSResponse::Back(_EMAIL_NOT_VALID);
			break;
		}

	} else {
		$gb_name = $my->name;
		$gb_email = $my->email;
	}
		
	$timeout = $params->get('timeout', 5);

	$row = $conn->SelectRow('#__guestbook', 'id,ip,date', ' WHERE ip =\''.$my->GetIP()."' AND date > '".($time-($timeout*60)).'\'');

	if(!count($row)) {
		$cl = $params->get('captcha', 1);
		if ( ($cl != 9) && ($my->gid < $cl)) {
			if (!$my->valid_captcha('guestbook'))
				break;
		}
		
		$gb_url = in('gb_url', __SQL | __NOHTML, $_POST, '');
		$gb_country = in('gb_country', __SQL | __NOHTML, $_POST, '', 50);
		$gb_title = in('gb_title', __SQL | __NOHTML, $_POST, '', 255);
		$gb_message = in('gb_message', __SQL | __NOHTML, $_POST, '');

		$conn->Insert('#__guestbook', '(name,email,url,country,title,message,ip,date)',"'$gb_name','$gb_email','$gb_url','$gb_country','$gb_title','".$gb_message."','".$my->GetIP()."','".$time."'");
	} else
		echo _GUESTBOOK_DOUBLE_SIGN;

default:
case "view":
	viewbook();
}

?>