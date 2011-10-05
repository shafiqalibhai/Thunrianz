<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Message component options for user profile
# @author legolas558
#

$_DRABOTS->registerFunction( 'OnViewPrivateProfile', 'botMessageOptionsView' );
$_DRABOTS->registerFunction( 'OnEditUserProfile', 'botMessageOptions' );
$_DRABOTS->registerFunction( 'OnLoginReminder', 'botMessageReminder' );
$_DRABOTS->registerFunction( 'AfterModifyUser', 'botMessageOptionsSave' );

function botMessageOptionsSave($id) {
	$allow = in_checkbox('user_message_allow', $_POST);
	$attach = in_checkbox('user_message_attach', $_POST);
	$html = in_checkbox('user_message_html', $_POST);
	include usr_com_path('const.php', 'message');
	$flags = ($allow ? __MESSAGE_FLAG_ALLOW : 0) + ($attach ? __MESSAGE_FLAG_ATTACH : 0) +
			($html ? __MESSAGE_FLAG_HTML : 0);
	// fetch the default values
	global $conn;
	$row = $conn->SelectRow('#__contacts', 'userid', ' WHERE userid='.$id);
	if (empty($row))
		$conn->Insert('#__contacts', '(userid,flags)', $id.','.$flags);
	else
		$conn->Update('#__contacts', 'flags='.$flags, ' WHERE userid='.$id);
	// update the forum poster name
	$row = $conn->SelectRow('#__users', 'name', ' WHERE id='.$id);
	$name = sql_encode($row['name']);
	$conn->Update('#__forum_posts',  'name=\''.$name.'\'', ' WHERE userid='.$id);
	$conn->Update('#__forum_topics', 'name=\''.$name.'\'', ' WHERE userid='.$id);
}

function botMessageReminder() {
	global $conn, $my;
	$msgc = $conn->SelectCount('#__receipts', '*', ' WHERE userid='.$my->id.' AND status=0');
	if (!$msgc)
		return null;
	$path = bot_lang($my->lang, 'msg_options');
	include_once $path;
	global $d_subpath, $_DRABOTS;
	$params = $_DRABOTS->GetBotParameters('core', 'msg_options');
	$url = CMSRequest::ScriptName().'?option=message&amp;task=inbox'.
			component_instance($params->get('com_id', 0), 'message');
	$caption = sprintf(_MSG_NEW, $msgc);
	return '<a href="'.$url.'" title="'.$caption.'"><img src="'.$d_subpath.'components/message/message.png" alt="[M]" border="0" /></a>&nbsp;<a href="'.$url.'" title="'.$caption.'">'.$caption.'</a>';
}

function botMessageOptions(&$urow) {
	global $my;
	$path = bot_lang($my->lang, 'msg_options');
	include_once $path;
	
	// fetch the default values
	global $conn;
	$row = $conn->SelectRow('#__contacts', 'flags', ' WHERE userid='.$urow['id']);
	if (empty($row)) {
		$attach = $html = 0;
		$allow = 1;
	} else {
		include usr_com_path('const.php', 'message');
		$allow = ($row['flags'] & __MESSAGE_FLAG_ALLOW);
		$attach = ($row['flags'] & __MESSAGE_FLAG_ATTACH);
		$html = ($row['flags'] & __MESSAGE_FLAG_HTML);
	}

	?><label for="user_message_allow">
<input id="user_message_allow" name="user_message_allow" type="checkbox" <?php if ($allow) echo ' checked="checked"'; ?> /><?php echo _MSG_OPTIONS_ALLOW; ?></label><br /><?php
?><label for="user_message_show_email">
<input id="user_message_html" name="user_message_html" type="checkbox" <?php if ($html) echo ' checked="checked"'; ?> /><?php echo _MSG_OPTIONS_ALLOW_HTML; ?></label><br /><?php
?>
<label for="user_message_attach">
<input id="user_message_attach" name="user_message_attach" type="checkbox" <?php if ($attach) echo ' checked="checked"'; ?>/><?php echo _MSG_OPTIONS_ATTACH; ?></label><br />
<?php
}

function botMessageOptionsView(&$urow) {
	global $my;
	$path = bot_lang($my->lang, 'msg_options');
	include_once $path;
	
	// fetch the default values
	global $conn;
	$row = $conn->SelectRow('#__contacts', 'flags', ' WHERE userid='.$urow['id']);
	if (empty($row)) {
		$attach = $html = 0;
		$allow = 1;
	} else {
		include usr_com_path('const.php', 'message');
		$allow = ($row['flags'] & __MESSAGE_FLAG_ALLOW);
		$attach = ($row['flags'] & __MESSAGE_FLAG_ATTACH);
		$html = ($row['flags'] & __MESSAGE_FLAG_HTML);
	}

?><div class="dk_content"><?php echo _MSG_OPTIONS_ALLOW; ?>: <strong><?php echo $allow ? _YES : _NO;
?></strong></div>
<div class="dk_content"><?php echo _MSG_OPTIONS_ALLOW_HTML; ?>: <strong><?php echo $html ? _YES : _NO;
?></strong></div>
<div class="dk_content"><?php echo _MSG_OPTIONS_ATTACH; ?>: <strong><?php echo $attach ? _YES : _NO;
?></strong></div>
<?php
}

?>