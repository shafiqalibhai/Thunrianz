<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Admin login page
# @see admin.php
#
# This is the default admin backend login page.
# It needs some improvements to comply with our output standards
# The page should also output the proper headers as other pages do
# @see classes/http.php about CMSResponse::Start()
# The script is included when the user has not yet logged in

$auth = in_raw('auth', $_GET);
if (isset($auth)) {
	if ($auth == 'http') {
		if ($d_http_auth) {
			include $d_root.'classes/http_auth.php';
			HttpAuth::Authenticate(3, ($d_http_auth==2));
			return;
		} else
			CMSResponse::Unauthorized();
	} else
		CMSResponse::BadRequest();
}

if (isset($_POST["username"]) ) {
	if ($my->admin_login($_POST))
		CMSResponse::Redir($my->PreviousPage());
}
if (isset($_POST["username"]) || isset($_POST["password"]))
	$msg='<font color=\"#FF0000\">'._LOGIN_MSG2.'</font><br/><a href="index.php?option=registration&task=lostpassword">'._LOGIN_FORGOT.'</a>';
else $msg = '';

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
$p = $d->GetComponentParamsRaw('login');
$have_captcha = ($p->get('captcha', 0));
if ($have_captcha) {
	global $_DRABOTS;
	$_DRABOTS->loadBotGroup('captcha');
}
$d->ShowMainHead(); $d->additional_head = null;
?>
<title>Lanius CMS</title>
<link href="<?php echo $d_subpath; ?>admin/templates/default/css/template_css.css" rel="stylesheet" type="text/css" />
<style type="text/css">
<!--
.sinput {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 10px;
}
-->
</style>
<script language="javascript" type="text/javascript">
function auth_submit() {
	if (!document.loginForm.username.value.length) {
		document.loginForm.username.focus();
		return false;
	}
	if (!document.loginForm.password.value.length) {
		document.loginForm.password.focus();
		return false;
	}
	return true;
}
</script>
</head>
<body>
<form action="admin.php" method="post" name="loginForm" onsubmit="return auth_submit()">
<table style="margin-top: 140px;" width="400" border="0" cellpadding="5" cellspacing="1" bgcolor="#CCCCCC" align="center">
           <tr><!--
                  <td width="42%" rowspan="2" bgcolor="#FFFFFF" >
                    <div align="center">&nbsp;<img src="<?php echo $d_subpath; ?>media/common/logo.png" alt="Lanius CMS" /><br />
                      <br />
                      <strong><font color="#0066FF">Lanius CMS</font></strong>
					  <br /><?php echo sprintf('%s <a target="_blank" href="'.create_context_help_url('GPL').'">%s</a>.', _CMS_LICENSED, _GPL); ?>
                      </div></td>-->
                  <td width="58%" bgcolor="white" ><br />
                    <table width="100%" border="0" cellspacing="5" cellpadding="0">
                      <tr>
                        <td><?php echo _USER_NAME; ?></td>
                        <td><input name="username" type="text" class="sinput" id="username" /></td>
                      </tr>
                      <tr>
                        <td><?php echo _PASSWORD; ?></td>
                        <td><input name="password" type="password" class="sinput" id="password" /></td>
                      </tr>
                      <?php if ($have_captcha) { ?>
                      <tr>
                        <td colspan="2"><?php $_DRABOTS->trigger('OnCaptchaRender', array('login')); ?></td>
                      </tr>
                      <?php } ?>
                      <tr>
                        <td colspan="2" align="center"><input type="submit" style="height:24px" value="<?php echo _BUTTON_LOGIN;?>" />
                          </td>
                      </tr>
                      <tr>
                        <td colspan="2" align="center">
<p><input id="remember" type="checkbox" name="remember" value="true"<?php if (in_cookie('cremember')) echo ' checked="checked"';?> />
                              <label for="remember"><?php echo _LOGIN_REM;?></label></p>
							  </td>
                      </tr><?php 
		      if ($d_http_auth) { ?>
		      <tr><td colspan="2" align="center"><a href="admin.php?auth=http"><?php echo _LOGIN_VIA_HTTP;?></a></td>
                      <?php } ?>
                    </table>
                  </td>
                </tr>
				<?php if ($msg) { ?>
                <tr>
                  <td bgcolor="#FFFFFF" ><table width="100%" border="0" cellspacing="5" cellpadding="0">
                      <tr>
                        <td height="12"><div align="center"><?php echo $msg; ?></div></td>
                      </tr><?php } ?>
                    </table>
				</form>
<br /><div align="center"><a href="<?php echo $d_website; ?>" title="<?php echo $d_website; ?>"><?php echo $d_title; ?></a></div>
</body>
</html>