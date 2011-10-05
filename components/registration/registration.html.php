<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function lostPassForm() {
?><div class="dk_header"><h2><?php echo _PROMPT_PASSWORD; ?></h2></div>
  <form action="index.php?option=registration" method="post">		
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="dk_content">
    <tr>
      <td colspan="2"><?php echo _NEW_PASS_DESC; ?></td>
    </tr>
    <tr>
      <td><label for="checkusername"><?php echo _USER_NAME; ?>:</label></td>
      <td><input type="text" name="checkusername" id="checkusername" class="dk_inputbox" size="40" maxlength="25" /></td>
    </tr>
    <tr>
      <td><label for="confirmEmail"><?php echo _EMAIL; ?>:</label></td>
      <td><input type="text" name="confirmEmail" id="confirmEmail" class="dk_inputbox" size="40" /></td>
    </tr>
    <tr>
      <td colspan="2"> 
	  <input type="hidden" name="task" value="sendNewPass" /> <div class="dk_content">
	  <input type="submit" class="dk_button" value="<?php echo _BUTTON_SEND_PASS; ?>" /></div></td>
    </tr>
</table>
</form>
<?php
}

function authForm($key = '', $resend = false) {
?><div class="dk_header"><h2><?php echo _REGISTRATION_ACTIVATION; ?></h2></div>
  <form action="index.php?option=registration" method="post">		
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="dk_content">
    <tr>
      <td colspan="2"><?php
	if ($resend) echo
		_REGISTRATION_ACTIVATION_RESEND_DESC;
	else
		echo _REGISTRATION_ACTIVATION_DESC; ?></td>
    </tr>
    <tr>
      <td><label for="confirm_email"><?php echo _EMAIL; ?>:</label></td>
      <td><input type="text" name="confirm_email" id="confirm_email" class="dk_inputbox" size="40" /></td>
    </tr>
    <?php if (!$resend) { ?>
    <tr>
      <td><label for="activation_code"><?php echo _REGISTRATION_ACTIVATION_CODE; ?>:</label></td>
      <td><input type="text" name="activation_code" id="activation_code" value="<?php echo $key; ?>" class="dk_inputbox" size="40" maxlength="32" /></td>
    </tr>
    <tr><td colspan="2"><hr /></td></tr>
    <tr>
      <td><label for="reg_password1"><?php echo _PASSWORD; ?>:</label></td>
      <td><div class="dk_content"><input class="dk_inputbox" type="password" name="reg_password" id="reg_password1" size="40" maxlength="100" /></div></td>
    </tr>
    <tr>
      <td><label for="reg_password2"><?php echo _REGISTER_VPASS; ?></label></td>
      <td><div class="dk_content"><input class="dk_inputbox" type="password" name="reg_password2" id="reg_password2" size="40" maxlength="100" /></div></td>
    </tr><?php } ?>
    <tr>
      <td colspan="2"> 
	<input type="hidden" name="task" value="<?php if ($resend) echo 'confirm_auth_resend'; else echo 'confirm_auth'; ?>" /> 
	<div class="dk_content" align="center">
		<input type="submit" class="dk_button" value="<?php if ($resend) echo _REGISTRATION_RESEND; else echo _REGISTRATION_ACTIVATE; ?>" />
	</div></td>
    </tr>
</table>
</form>
<?php
}


function registerForm( $d_useractivation ) {
global $d_root, $my, $d, $params;

// if user is already registered
if ($my->id>=1) {
	//TODO: show a message
	CMSResponse::Redir('index.php?option=login');
	return;
}

include $d_root.'includes/langsel.php';

$xup_support = $params->get('xup_support', 1);
$useractivation = $params->get('registration_activation', 3);

commonJS($xup_support);

if ($xup_support) {
	$xup_req = '* ';
	$n_req = '** ';
} else {
	$n_req = $xup_req = '* ';
}
?>
<div class="dk_header"><h2><?php echo _REGISTER_TITLE; ?></h2></div>
<form <?php if ($xup_support) echo 'enctype="multipart/form-data" '; ?>action="index.php?option=registration" method="post" name="regForm" onsubmit="return regForm_validate()">
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="dk_content">
<?php if ($xup_support) { ?>
<tr>
<td colspan="2"><?php echo sprintf(_REGISTRATION_XUP_DESCRIPTION, '<a target="_blank" rel="nofollow" href="http://www.w3.org/TR/xup/">'._REGISTRATION_XUP_READ_MORE.'</a>'); ?></td>
</tr>
    <tr>
      <td width="30%"><?php echo _REGISTRATION_XUP_FILENAME; ?>:</td>
      <td><div class="dk_content"><?php echo file_input_field('reg_xup', null, 40, 100); ?></div></td>
    </tr>
    <tr><td colspan="2">&nbsp;</td></tr>
<?php } ?>
    <tr>
      <td width="30%"><label for="reg_name"><?php echo $xup_req._NAME; ?>:</label></td>
      <td><div class="dk_content"><input type="text" name="reg_name" id="reg_name" size="40" class="dk_inputbox" maxlength="50" /></div></td>
    </tr>
    <tr>
      <td><label for="reg_user"><?php echo $xup_req._USER_NAME; ?>:</label></td>
      <td><div class="dk_content"><input type="text" name="reg_user" id="reg_user" size="40" class="dk_inputbox" maxlength="25" /></div></td>
	</tr>
    <tr>
      <td><label for="reg_email"><?php echo $xup_req._EMAIL; ?>:</label></td>
      <td><div class="dk_content"><input type="text" name="reg_email" id="reg_email" size="40" class="dk_inputbox" maxlength="100" /></div></td>
    </tr>
    <?php if ($d_useractivation<=1) { ?>
    <tr>
      <td><label for="reg_password"><?php echo $n_req._PASSWORD; ?>:</label></td>
      <td><?php echo $d->place_pwd_pb('reg_password', 'the_password', _CMS_PASSWORD_QUALITY); ?><div class="dk_content"><input class="dk_inputbox" type="password" id="reg_password" name="reg_password" size="40" maxlength="100" />
      </div>
</td>
    </tr>

    <tr>
      <td><label for="reg_password4"><?php echo $n_req._REGISTER_VPASS; ?></label></td>
      <td><div class="dk_content"><input class="dk_inputbox" type="password" name="reg_password2" id="reg_password4" size="40" /></div></td>
    </tr>
	<?php } ?>
	 <tr>
      <td colspan="2"><?php
		switch ($d_useractivation) {
			case 0:
				echo '&nbsp;';
			break;
			case 1:
				echo _SENDING_PASSWORD;
			break;
			case 2:
				echo _SENDING_NEW_PASSWORD;
			case 3:
				echo _SENDING_ACTIVATION;
		}
      ?></td>
	</tr>
    <tr>
      <td><?php echo $n_req._LANGUAGE; ?>:</td>
      <td><div class="dk_content"><?php echo select_language('reg_lang'); ?></div></td>
    </tr>
	<tr><td><label for="user_tz"><?php echo _USER_TIMEZONE; ?></label></td>
	<td><div class="dk_content"><select name="user_tz" id="user_tz" class="dk_inputbox"><?php
//			$this_tz = $my->GetTimezone();
			global $timezones;
			if (substr(phpversion(), 0, 1)=='4')
				$tzs = array_keys($timezones);
			else {
				global $d_root;
				include $d_root.'includes/i18n/timezones.php';
				$tzs = $timezones;
			}
			echo '<option value="" selected="selected">'.'-- Auto --'.'</option>';
			foreach($tzs as $tz) {
//				$sel = ($tz===$this_tz) ? ' selected="selected"' : '';
				$tz = xhtml_safe($tz);
				echo '<option value="'.$tz.'">'.$tz.'</option>';
			}
		?></select></div>	</td></tr><?php
		if ($params->get('captcha', 1)) { ?><tr><td><?php
			global $_DRABOTS;
			$_DRABOTS->loadBotGroup('captcha');
			$_DRABOTS->trigger('OnCaptchaRender', array('registration'));
			?></td></tr><?php
		}
    ?>
    <tr>
      <td colspan="2">&nbsp;<input type="hidden" id="tz_offset" name="tz_offset" value="0" /></td>
    </tr>
<tr>
<td colspan="2"><?php echo $n_req.' = '._REGISTER_REQUIRED; ?></td>
</tr><?php if ($xup_support) { ?>
<tr>
<td colspan="2"><?php echo $xup_req.' = '._REGISTER_XUP_REQUIRED; ?></td>
</tr><?php } ?>
    <?php  if ($useractivation == 3) { ?>
	<tr><td colspan="2"><?php echo sprintf(_REGISTRATION_ACTIVATION_RESEND_MSG, '<a href="index.php?option=registration&amp;task=resend_auth">', '</a>'); ?></td>
   </tr><?php } ?>
    <tr>
      <td>&nbsp;</td>
		<td><div class="dk_content"><input type="hidden" name="task" value="saveRegistration" />
          <input name="submit" type="submit" class="dk_button" value="<?php echo _BUTTON_SEND_REG; ?>" /></div></td>
    </tr>
</table>
</form>
<?php
}

function registerComplete($msg) {
	?><div class="dk_header"><h2><?php echo _REGISTRATION_COMPLETE; ?></h2></div>
	<p align="center"><?php echo $msg; ?></p><?php
}

function registerDone($msg, $url = '') {
	?><div class="dk_header"><h2><?php echo _REGISTER_TITLE; ?></h2></div>
	<p align="center"><?php echo $msg; ?></p>
	<p align="center"><a href="<?php if (strlen($url)) echo $url; else echo 'javascript:history.back(-1)'; ?>"><?php echo _CONTINUE; ?></a><?php
}


function authDone($msg) {
?><div class="dk_header"><h2><?php echo _REGISTRATION_ACTIVATION; ?></h2></div>
<p align="center"><?php echo $msg; ?></p><?php
}

?>