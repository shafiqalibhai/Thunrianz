<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

global $my, $d_type;

	$path = mod_lang($my->lang, $module['module']);
	include_once $path;

// show some custom text when logged in or when not
$pretext = $params->get('pretext', '');
$posttext = $params->get('posttext', '');
if (strlen($pretext)) $pretext = '<p>'.$pretext.'</p>';
if (strlen($posttext)) $posttext = '<p>'.$posttext.'</p>';

//RFC?
if($d_type!="html") {
	if ($my->isuser())
		echo $posttext;
	else
		echo $pretext;
	return;
}

$inst = module_instance($module['instance'], 'login');
  if ($my->id) {
  ?><div ><?php echo _LOGIN_HI.' <strong>'.$my->name;?></strong><br /><?php echo access_bygid($my->gid);
	global $_DRABOTS;
	$results = $_DRABOTS->trigger('OnLoginReminder');
	foreach($results as $r) {
		if (isset($r))
			echo '<p>'.$r.'</p>';
	}
	?><div align="right"><a href="<?php echo "index.php?option=login".$inst."&amp;task=logout"; ?>"><?php echo _LOGOUT; ?></a></div>
	</div>
  <?php  }  else  { ?>
<script language="javascript" type="text/javascript">
function login_precheck() {
  var f=document.login;
  if ((f.username.value=='') || (f.password.value=='')) {
	if (f.username.value=='') {
		alert('<?php echo js_enc(_LOGIN_ENTER_USERNAME); ?>');
		f.username.focus();
	} else {
		alert('<?php echo js_enc(_LOGIN_ENTER_PASSWORD); ?>');
		f.password.focus();
	}
	return false;
  }
  return true;
}
</script>
	<form class="dk_form" action="<?php echo "index.php?option=login".$inst; ?>" method="post" name="login" onsubmit="return login_precheck()">
    <?php	echo $pretext; ?>
      <div class="dk_content">
        <?php 
		
		global $d;
		$cparams = $d->GetComponentParamsRaw('registration');
		
		if (isset($cparams) && $cparams->get('registration_new', 1)) {
			echo _NO_ACCOUNT.' '; ?>
			<a href="index.php?option=registration&amp;task=register">
			<?php echo _CREATE_ACCOUNT;?></a>
		<?php } ?>
	  </div>
      <div class="dk_content">
        <?php echo _USER_NAME; ?><br/>
        <input type="text" name="username" class="dk_inputbox" size="10" />
      </div>
      <div class="dk_content">
        <?php echo _PASSWORD; ?><br/>
        <input type="password" name="password" class="dk_inputbox" size="10" />
      </div>
	  <div class="dk_content">
	  <a href="index.php?option=registration&amp;task=lostpassword"><?php echo _LOST_PASSWORD; ?></a>
      <input type="hidden" name="task" value="login" /></div>
      <?php
	$p = $d->GetComponentParamsRaw('login');
	if ($p->get('captcha', 0)) {
		global $_DRABOTS;
		$_DRABOTS->loadBotGroup('captcha');
		$_DRABOTS->trigger('OnCaptchaRender', array('mod_login'));
	}   ?>
      <div class="dk_content"><input type="submit" class="dk_button" value="<?php echo _BUTTON_LOGIN; ?>" /></div>
	<?php
		global $d_http_auth;
		if ($d_http_auth) { ?>
		<div class="dk_content"><a href="index2.php?option=login&amp;task=auth<?php echo $inst; ?>"><?php echo _LOGIN_VIA_HTTP;?></a></div>
	<?php } ?>
      <div class="dk_content"><label for="remember"><input id="remember" type="checkbox" name="remember" class="dk_inputbox" value="true"<?php if (in_cookie('cremember')) echo ' checked="checked"';?> /><?php echo _REMEMBER_ME; ?></label></div>
	</form>
	<?php	echo $posttext; 
	}
?>
