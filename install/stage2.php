<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Lanius CMS EZInstall script
#
# Stage 3 - gather core settings

$cusername = 'admin';
$cpassword = random_string(7);

//L: still hacky
$d_website = dirname(dirname("http://" . $_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'])).'/';
$d_title = 'Lanius CMS Site';
$d_db = '';
$d_dbpassword = '';

function guess_default_email($d_website, $username = 'webmaster') {
	$email = (string)@ini_get('sendmail_from');

	if (!strlen($email) || !is_email($email)) {
		// pre-compute a default From: address
		$p = strpos($d_website, '/');
		$email = substr($d_website, $p+2);
		$p = strpos($email, '/');
		$email = substr($email, 0, $p);

		if (strpos($email, 'www.')===0)
			$email = substr($email, 4);
		// if the website is an IP address then don't use it for email generation
		if (preg_match('/[\\d\\.]+/A', $email))
			$email = 'example.com';
		$email = $username.'@'.$email;
		// if the so-formed email is not valid (also happens if domain is 'localhost'
		if (!is_email($email))
			$email = $username.'@example.com';
	}
	return $email;
}

$d_email_from = guess_default_email($d_website, 'postmaster');
$cemail = guess_default_email($d_website, 'webmaster');

$p = strpos($cemail, '@');
$cname = ucfirst(substr($cemail, 0, $p));
$p = strpos($d_email_from, '@');
$d_email_name = ucfirst(substr($d_email_from, 0, $p));
// this can cause spam problems
//TODO: client-side and server-side validation!
if ($d_email_name == 'Postmaster')
	$d_email_name = 'Webmaster';

	include $d_root.'admin/includes/permelev.php';
?>          <form name="form1" method="post" action="index.php?stage=3">
      <table bgcolor="white" width="100%" border="0" align="center" cellpadding="5" cellspacing="0">
          <tr>
            <td colspan="2" class="menuheader"><?php echo _INSTALL_1_MAIN;?></td>
          </tr>
          <tr>
            <td colspan="2" class="tip"><?php echo _INSTALL_1_MAIN_EXP; ?></td>
          </tr>
          <tr>
            <td width="58%"><label for="cwebsite"><?php echo _INSTALL_1_URL;?></label></td>
            <td width="42%"><input name="cwebsite" type="text" class="textboxgray" id="cwebsite" value="<?php
			echo $d_website; ?>" size="50"></td>
          </tr>
          <tr>
            <td><label for="ctitle"><?php echo _INSTALL_1_TITLE; ?></label></td>
            <td><input name="ctitle" type="text" class="textboxgray" id="ctitle" value="<?php echo $d_title; ?>" size="50"></td></tr>
          <tr bgcolor="#F7F7F7">
            <td colspan="2" class="menuheader"><?php echo _INSTALL_1_EMAIL_SETTINGS; ?> </td>
          </tr>
             <tr>
            <td colspan="2" class="tip"><?php echo _INSTALL_1_EMAIL_SETTINGS_DESC; ?></td></tr>
          <tr>
            <td><label for="cemail_from"><?php echo _INSTALL_1_FROM_ADDRESS;?></label></td>
            <td><input name="cemail_from" type="text" class="textboxgray" id="cemail_from" value="<?php
			echo $d_email_from; ?>" size="50"></td>
          </tr>
          <tr>
            <td><label for="cemail_name"><?php echo _INSTALL_1_FROM_NAME;?></label></td>
            <td><input name="cemail_name" type="text" class="textboxgray" id="cemail_name" value="<?php
			echo $d_email_name; ?>" size="50"></td>
          </tr>
          <tr bgcolor="#F7F7F7">
            <td colspan="2" class="menuheader"><?php echo _INSTALL_1_ADMIN; ?> </td>
          </tr>
          <tr>
            <td colspan="2" class="tip"><?php echo _INSTALL_1_ADMIN_EXP; ?></td>
          </tr>
          <tr>
            <td><label for="cname"><?php echo _INSTALL_1_NAME;?></label></td>
            <td><input name="cname" type="text" class="textboxgray" id="cname" value="<?php echo $cname; ?>" size="50"></td>
          </tr>
          <tr>
            <td><label for="cemail"><?php echo _INSTALL_1_EMAIL;?></label></td>
            <td><input name="cemail" type="text" class="textboxgray" id="cemail" value="<?php echo $cemail; ?>" size="50"></td>
          </tr>
          <tr>
            <td><label for="cusername"><?php echo _INSTALL_1_USERNAME;?></label></td>
            <td><input name="cusername" type="text" class="textboxgray" id="cusername" value="<?php echo $cusername; ?>" size="50"></td>
          </tr>
          <tr>
            <td><label for="cpassword"><?php echo _INSTALL_1_PASSWORD;?></label></td>
            <td><input name="cpassword" type="text" class="textboxgray" id="cpassword" value="<?php
                echo $cpassword; ?>" size="50"></td>
          </tr>
	    <tr bgcolor="#F7F7F7">
	      <td colspan="2" class="menuheader"><?php echo _INSTALL_3_DB_SETTINGS;?> </td>
        </tr>
<?php
	$dbconfig = !file_exists($cfg_template);
	
	// set the temporary rootid
	$rootid = md5($d_root);

	if (!$dbconfig) {
		include $cfg_template;
	} else {
		// preserve the uid found in the session
		if (!isset($_SESSION[$rootid.'-uid'] ))
			$d_uid = random_string(8);
		else
			$d_uid = $_SESSION[$rootid.'-uid'] ;
	}

	$_SESSION[$rootid.'-uid'] = $d_uid;

	$_SESSION[$d_uid.'-installing'] = true;
	
	if ($dbconfig) {
		include $d_root.'lang/'.$my->lang.'/admin/admin.php';
		include $d_root.'classes/cms.php';
		$d_subpath = '../';
		$d = new CMS(true);
		
		include $d_root.'lang/'.$my->lang.'/admin/includes/dbsettings.php';
		include $d_root.'admin/includes/dbsettings.php'; ?>
	<?php } else { ?>
	<tr><td colspan="2"><?php echo _INSTALL_3_CFG_TEMPLATE; ?></td></tr>
	<?php } ?>
	<tr><td colspan="2" align="right"><a href="javascript:document.form1.submit();" class="menulink"><?php echo _INSTALL_CONTINUE;?></a>&nbsp;&nbsp;&nbsp;&nbsp;</td>
          </tr>
              </table>
</form>
<?php

if ($dbconfig)
	dbtest_form();

?>
