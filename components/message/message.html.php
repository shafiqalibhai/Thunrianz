<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function validateJS() {
	global $d, $my;
	$d->add_raw_js('
	var email_regex = new RegExp("^[\\\\w_\\\\.\\\\-]+\\\\@[\\\\w_\\\\.\\\\-]+$");

	function validate(){
		var f = document.getElementById("message_form");
		if ( '.($my->isuser() ? '' : '(f.message_sender_name.value=="")|| (!email_regex.test(f.message_sender_email.value)) ||').
		' (f.message_text.value=="")) {
			alert("'.js_enc(_FORM_NC).'");
		} else {
			f.action = "index.php?option=message";
			f.submit();
		}
	}');
}

function _filter_contactable($fields, $where = '') {
	global $params, $my;
	// managers and administrators can message users of any level (whose receive flag is active)
	if ($my->gid >= 4)
		$recp_min_gid = 1;
	else
		$recp_min_gid = $params->get('recp_min_gid', '4');
	// get the two most interesting columns of contacts table
	global $conn;
	$where = ' WHERE userid<>'.$my->GetID().' '.$where.' ORDER BY userid';
	$ids = $conn->SelectColumn('#__contacts', 'userid', $where);
	$flags = $conn->SelectColumn('#__contacts', 'flags', $where);
	// collect all valid recipients
	$recipients = array();
	foreach($flags as $i => $flag) {
		if ($flag & __MESSAGE_FLAG_ALLOW) {
			$row = $conn->SelectRow('#__users', $fields.',gid',
				' WHERE published=1 AND gid>='.$recp_min_gid.
				' AND id='.$ids[$i]);
			if (empty($row))
				continue;
//			$row['flags'] = $flag;
			$recipients[(int)$ids[$i]] = $row;
		}
	}
	return $recipients;
}

function _mk_anonym_recp($recp_type, $reply_to) {
	global $conn;
	// craft the fake user row. Email will be used in the form to show the real target
	$row = $conn->SelectRow('#__messages', 'name,email',
				' WHERE id='.$reply_to);
	switch ($recp_type) {
		default:
		case 'username':
			$row['username'] = _ANONYMOUS;
		break;
		case 'name':
		break;
	}
	$recipients = array ( 0 => $row ); $row = null;
	return $recipients;
}

function message_form($subject = '', $body = '', $to = 0, $reply_to = 0) {
	global $Itemid, $d_root, $my, $params;

	// get the allowed fields for recipients list
	$recp_type = $params->get('recp_list', 'name');

	// in case of replies to messages sent by non-registered users
	if ($reply_to && !$to) {
		$recipients = _mk_anonym_recp($recp_type, $reply_to);
	} else {
		// check for the normal user
		$recipients = _filter_contactable($recp_type);
		// nobody wants to receive messages!
		if (!count($recipients)) { ?>
			<div class="dk_header"><h2><?php echo _MESSAGE_TITLE ?></h2></div>
			<div class="dk_content"><?php echo _MESSAGE_NO_RECIPIENTS; ?></div><?php
			return;
		}
		
		if (!$to)
			$to = key($recipients);
		else {
			if (!isset($recipients[$to])) { ?>
			<div class="dk_header"><h2><?php echo _MESSAGE_TITLE ?></h2></div>
			<div class="dk_content"><?php echo _MESSAGE_INVALID_RECIPIENT; ?></div><?php
			return;
			}
		}
	}
	
	global $_DRABOTS;
	$_DRABOTS->trigger('OnBeforeRenderMessageForm', array($subject, $body, $to , $reply_to, &$recipients));
	
	validateJS();

?><div class="dk_header"><h2><?php echo _MESSAGE_TITLE ?></h2></div>
	<form enctype="multipart/form-data" action="index.php?option=message&amp;Itemid=<?php echo $Itemid; ?>" method="post" name="message_form" target="_top" id="message_form" class="dk_content">
    <?php
	$store_mode = (int)$params->get('store_mode', 1);
	$send_message = (int)$params->get('send_message', 1);
	// show a notice if email is going to be disclosed
	$full_email = ($send_message && ($store_mode < 2));
	if ($full_email) {
    ?><p><?php echo _MESSAGE_FULL_DELIVERY_NOTICE; ?></p><?php }
	// show the own name and email fields only if user is not logged in
	if ($my->isuser()) {
		if ($full_email) { ?>
    <div class="dk_content">
    <p><label for="name"><?php echo _NAME; ?>:</label></p>
    <input name="name" type="text" class="dk_inputbox" value="<?php echo xhtml_safe($my->name); ?>" id="name" size="30" disabled="disabled" />
    <p><label for="email"><?php echo _EMAIL; ?>:</label></p>
    <input name="email" type="text" class="dk_inputbox" value="<?php echo xhtml_safe($my->email); ?>" id="email" size="30" disabled="disabled" />
    </div>
    <?php } } else {?>
    <div class="dk_content">
    <label for="message_sender_name">* <?php echo _YOUR_NAME; ?></label>
    <div class="dk_content"><input name="message_sender_name" type="text" class="dk_inputbox" id="message_sender_name" value="" maxlength="255" size="30" /></div>
    <div class="dk_content"><label for="message_sender_email">* <?php echo _EMAIL_PROMPT; ?></label></div>
    <div class="dk_content"><input name="message_sender_email" type="text" class="dk_inputbox" id="message_sender_email" value="" maxlength="255" size="30" /></div>
    <?php } ?>
	<div class="dk_content"><label for="message_subject">* <?php echo _SUBJECT_PROMPT; ?></label></div>
	<div class="dk_content"><input name="message_subject" type="text" class="dk_inputbox" id="message_subject" value="<?php echo $subject; ?>" maxlength="255" size="30" /></div>
	<div class="dk_content"><label for="message_text">* <?php echo _MESSAGE_PROMPT; ?></label></div>
	<div class="dk_content"><textarea name="message_text" cols="45" rows="5" class="dk_inputbox" id="message_text"><?php echo $body; ?></textarea><br /><?php
	$message_max_size = (int)$params->get('message_max_size', 1024);
	echo sprintf("Maximum message length is %d bytes (%s)", $message_max_size, convert_bytes($message_max_size)); ?></div>
<?php
	//TODO: offer parameter customization
	if ($params->get('allow_attachment', 1) && $full_email) {
		//L: attachments available only if full email delivery is active
?><div class="dk_content"><label for="message_attach"><?php echo _MESSAGE_ATTACH; ?></label></div>
    <div class="dk_content"><?php echo file_input_field('message_attach'); ?></div>
<?php
	}
	// show captcha if user is not logged in
	if ($params->get('captcha', 1) and !$my->isuser()) {
		global $_DRABOTS;
		$_DRABOTS->loadBotGroup('captcha');
		$_DRABOTS->trigger('OnCaptchaRender', array('message'));
	}
?><div class="dk_content">
	<?php if ($reply_to && !$to) { ?>
		<div class="dk_content"><?php echo _MESSAGE_RECIPIENTS; ?>:</div>
	<div class="dk_content"><input type="text" class="dk_inputbox" value="<?php echo xhtml_safe($recipients[0]['email']); ?>" size="30" disabled="disabled" /></div>
		<?php } else { ?>
	<label for="message_recipients">* <?php echo _MESSAGE_RECIPIENTS; ?>:</label>
		<div class="dk_content">
		<select name="message_recipients[]" id="message_recipients"<?php
		$multiple_recp = $params->get('multiple_recp', 0);
		
		if ($multiple_recp)
			echo ' size="8" multiple="multiple"';
	?>>
	<?php
	foreach($recipients as $id => $row) {
		_mk_recp_option($id, $row, $recp_type, $to);
	}
	?>
		</select>
		</div>
	</div>
<?php
	}
	$_DRABOTS->trigger('OnRenderMessageForm', array($subject, $body, $to , $reply_to));
?>
<input name="task" type="hidden" id="task" value="post" />
<input name="reply_to" type="hidden" id="reply_to" value="<?php echo $reply_to; ?>" />
	<div class="dk_content"><input type="button" name="send" value="<?php echo _SEND; ?>" class="dk_button" onclick="validate()" /></div>
	</div>
</form>
<?php
} // end of function message_form()

function _get_recp($row, $recp_type) {
	switch ($recp_type) {
		case 'username':
		case 'name':
			return $row[$recp_type];
			break;
//		default:
	}
	return $row['username'].' ('.$row['name'].')';
}

function _mk_recp_option($id, $row, $recp_type, $sel) {
	$r = _get_recp($row, $recp_type);
	if ($row['gid']>=4)
		$r = '* '.$r;
	echo '<option value="'.$id.'"'.( ($sel == $id) ? ' selected="selected"' : '');
	if ($row['gid']>=4)
		echo ' style="font-weight: bold"';
	echo '>';
	echo xhtml_safe($r);
	echo "</option>\n";
}

function thank_message() {
?>	<div class="dk_header"><h2><?php echo _MESSAGE_TITLE; ?></h2></div>
	<div class="dk_aligncenter" >
		<p><?php echo _THANK_MESSAGE; ?></p>
	</div>
<?php

} // end of function thank_message()

function message_inbox() {
	global $d_root;
	include $d_root.'includes/userui.php';
	include_once $d_root.'lang/'.$my->lang.'/admin/components/message.php';
	
	$toolbar->add_custom_list(_MSG_REPLY,'reply');
	$toolbar->add_custom_list(_MSG_READ,'view');
	$toolbar->add_custom_list(_DELETE,'delete');

	$gui=new ScriptedUI();
//	$gui->add("form","adminform", '', 'index.php?option=nest');
	$gui->enable_filter=false;
	$gui->add("form","inboxform");
	$gui->add("com_header", "Your inbox");
//	$gui->add("tab_link","dtab");
//	$gui->add("tab_head");

	include $d_root.'components/message/message.common.php';
	include_once $d_root.'lang/'.$my->lang.'/admin/components/message.php';
	$table_head = array ( array('title'=>'#' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>'checkbox' , 'val'=>'id' , 'len'=>'1%','align'=>'center') ,
						  array('title'=>_E_SUBJECT,'val'=>'message_subject','len'=>'60%','ilink'=>
						  CMSResponse::BaseUrl().'&task=view&cid[]=ivar1','ivar1'=>'id') ,
						  array('title'=>_MSG_FROM,'val'=>'name','len'=>'20%',
						  'ilink'=>CMSResponse::BaseUrl().'&task=message&to=ivar1','ivar1'=>'userid') ,
						  array('title'=>_MSG_POSTED,'val'=>'cdate','date'=>'1','len'=>'20%',
						  'align'=>'center') ,
						  array('title'=>_MSG_STATUS,'val'=>'status','len'=>'10%','align'=>'center')
						 );

	$table_data = _message_table_data(',userid');

	$gui->add("data_table_arr","maintable",$table_head,$table_data);
//	$gui->add("tab_tail");
	
	$gui->add("end_form");

	$gui->generate();
}

function view_message($id) {
	global $d_root;
	include $d_root.'includes/userui.php';

	include_once $d_root.'lang/'.$my->lang.'/admin/components/message.php';
	$toolbar->add('back');
	$toolbar->add_custom(_MSG_REPLY,'reply');
	$toolbar->add_custom(_DELETE,'delmsg');
	
	global $conn;
	$rsar=$conn->SelectRow('#__messages', '*', ' WHERE id='.$id);

	$gui=new ScriptedUI();
	$gui->enable_filter=false;
	$gui->add("form","messageform");
	$gui->add("com_header", _MSG_VIEW_HEAD);
//	$gui->add("tab_head");
	
	_message_view($gui, $rsar);
	
	$gui->add("end_form");
	
//	$gui->add("tab_tail");

	$gui->generate();
}

?>