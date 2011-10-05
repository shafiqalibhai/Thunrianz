<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function loginpage (){
	global  $d_website,$d_root,$d_userregistration;
	global $pathway, $d, $params;
	$pathway->add(_BUTTON_LOGIN);
	
	$d->add_raw_js("function login__precheck() {
  var f=document.loginForm;
  if ((f.username.value=='') || (f.password.value=='')) {
	alert('".js_enc(_LOGIN_INCOMPLETE)."');
	if (f.username.value=='')
		f.username.focus();
	else
		f.password.focus();
	return false;
  }
  return true;
  }");
?>
<form action="index.php?option=login" method="post" name="loginForm" id="loginForm" onsubmit="return login__precheck()">
        <div class="dk_header"><h2><?php echo _USER_NAME; ?></h2></div>
  <table width="100%" border="0" align="center" cellpadding="4" cellspacing="0">
        
    <tr>
      <td><?php if ($d_userregistration) {
	echo _NO_ACCOUNT.' '; ?><a href="index.php?option=registration&amp;task=register"><?php echo _CREATE_ACCOUNT;?></a><?php } ?></td>
    </tr>
	<tr>
      <td><strong><?php echo _YOUR_NAME; ?></strong> <br /> <input name="username" type="text" maxlength="25" class="dk_inputbox" size="20" /></td>
    </tr>
          
    <tr>
            
      <td><strong><?php echo _PASSWORD; ?></strong> <br /> <input maxlength="20" name="password" type="password" class="dk_inputbox" size="20" /><br /><a href="index.php?option=registration&amp;task=lostpassword"><?php echo _LOST_PASSWORD; ?></a></td>
    </tr>
<?php
if($params->get('captcha', 0)) {
	echo '<tr><td>';
	global $_DRABOTS;
	$_DRABOTS->loadBotGroup('captcha');
	$_DRABOTS->trigger('OnCaptchaRender', array('login'));
?></td></tr>
<?php
}
?>        
    <tr>
      <td> 
	  <input type="submit" class="dk_button" value="<?php echo _BUTTON_LOGIN; ?>" />
			  <input type="hidden" name="task" value="login" />
	        </td>
    </tr>
<?php if ($params->get('http_auth',0)) { ?>
    <tr>
      <td><a href="index2.php?option=login&amp;task=auth"><?php echo _LOGIN_VIA_HTTP;?></a></td>
    </tr>
<?php } ?>
  </table>
	</form>
<?php  
  	}
	
	function logoutpage(){
		global $pathway, $my, $access_level, $Itemid;
		$pathway->add(sprintf(_USER_WELCOME_BACK, $my->username));
		
		//TODO: diversification of com_user and com_login
		?>
              <p><?php echo sprintf(_USER_AUTHENTICATED,
	      '<strong>'.xhtml_safe($my->username).'</strong>', xhtml_safe(access_bygid($my->gid))); ?></p>
	      <ul>
	      <li><a href="index.php?option=login&amp;task=logout&amp;Itemid=<?php echo $Itemid; ?>"><?php echo _LOGOUT; ?></a></li>
	      <li><a href="index.php?option=user&amp;Itemid=<?php echo $Itemid; ?>"><?php echo _USER_EDIT_PROFILE; ?></a></li>
	      </ul>
	<?php
}

?>