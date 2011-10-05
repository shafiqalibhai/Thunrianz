<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

	function user_frontpage($msg='') { ?>
		<div class="dk_header"><h2><?php echo _USER_WELCOME; ?></h2></div>
		<div class="dkcom_userwelcome"><?php echo _WELCOME_DESC; ?></div>
		<div class="dkcom_usermessage"><?php echo $msg; ?></div><?php
	}

include $d_root.'includes/langsel.php';

function edit_profile($row) {
	global $d;
	$d->add_raw_js('function profile_verify() {
		var f=document.getElementById(\'edit_user_form\');
		
		if (!f.user_password_orig.value.length) {
			alert(\''.js_encode(_FORM_NC).'\');
			return false;
		}
		
		if (f.user_password.value.length) {
			if (f.user_password.value != f.user_password1.value) {
				alert(\''.js_encode(_PASS_MATCH).'\');
				return false;
			}
		}
		return true;
	}');
	?>
		<div class="dk_header"><h2><?php echo _EDIT_TITLE; ?></h2></div>
		<form id="edit_user_form" action="index.php?option=user" method="post" name="edit_user_form" class="dk_form" onsubmit="return profile_verify()"<?php
			global $_DRABOTS;
			$results = $_DRABOTS->trigger('OnEditUserProfileHasFileUpload', array(&$row));
			$complex = false;
			foreach($results as $r) {
				$complex |= $r;
			}
			if ($complex) echo ' enctype="multipart/form-data"';
		?>>
			<div class="dk_content"><?php echo _USER_NAME; ?>: <strong><?php echo $row['username'];?></strong></div>
			<div class="dk_content"><label for="user_name">* <?php echo _YOUR_NAME; ?></label></div>
			<div class="dk_content"><input name="user_name" maxlength="50" type="text" class="dk_inputbox" id="user_name" value="<?php echo $row['name'];?>" size="40" /></div>
			<div class="dk_content"><label for="user_email">* <?php echo _EMAIL; ?></label></div>
			<div class="dk_content"><input name="user_email" maxlength="100" type="text" class="dk_inputbox" id="user_email" value="<?php echo xhtml_safe($row['email']);?>" size="40" /></div>
			<div class="dk_content"><label for="user_lang"><?php echo _LANGUAGE; ?></label></div>
			<div class="dk_content"><?php echo select_language('user_lang', $row['lang'],'','dk_inputbox'); ?></div>
			<div class="dk_content"><label for="user_tz"><?php echo _USER_TIMEZONE; ?></label></div>
			<div class="dk_content"><select name="user_tz" id="user_tz" class="dk_inputbox"><?php
			global $timezones;
			if (substr(phpversion(), 0, 1)=='4')
				$tzs = array_keys($timezones);
			else {
				global $d_root;
				include $d_root.'includes/i18n/timezones.php';
				$tzs = $timezones;
			}
			if ($row['timezone'] === '')
				$sel = ' selected="selected"';
			else $sel = '';
			echo '<option value="" '.$sel.'>'.'-- Auto --'.'</option>';
			foreach($tzs as $tz) {
				$sel = ($tz===$row['timezone']) ? ' selected="selected"' : '';
				$tz = xhtml_safe($tz);
				echo '<option value="'.$tz.'"'.$sel.'>'.$tz.'</option>';
			}
			?></select></div>
			<div class="dk_content"><label for="user_password_orig">* <?php echo _USER_CURRENT_PASS; ?>:</label><br />
			<input name="user_password_orig" type="password" class="dk_inputbox" id="user_password_orig" value="" size="20" maxlength="100" /></div>
			<div class="dk_content"><label for="user_password"><?php echo _PASSWORD; ?>:</label><br /><div class="dk_content"><?php echo _USER_PASSWORD_INFO; ?></div><?php echo $d->place_pwd_pb('user_password', 'the_password', _CMS_PASSWORD_QUALITY); ?>
			<input name="user_password" type="password" class="dk_inputbox" id="user_password" value="" size="20" maxlength="100" /></div>
			<div class="dk_content"><label for="user_password1"><?php echo _USER_VPASS; ?></label><br />
			<input name="user_password1" type="password" class="dk_inputbox" id="user_password1" value="" size="20" maxlength="100" /></div><hr />
			<?php
				global $_DRABOTS;
				$_DRABOTS->trigger('OnEditUserProfile', array(&$row));
			?>
			<p><input class="dk_button" type="submit" name="btnsubmit" value="<?php echo _UPDATE; ?>" />
			<input type="button" class="dk_button" value="<?php echo _CANCEL; ?>" onclick="history.go(-1)" />
				<input type="hidden" name="id" value="<?php echo encode_userid($row['id']);?>" />
				<input type="hidden" name="task" value="update" />
			</p>
		</form>
		<?php
		// delete user profile feature
		global $params, $d_title;
		if ($params->get('allow_delete', 1)) {
			$d->add_raw_js('function confirm_delete_profile() {
				if (confirm(\''.js_encode(sprintf(_USER_DELETE_PROFILE_CONFIRM, $d_title)).'\'))
					return true;
				return false;
			}');
?>		<hr /><p align="right"><?php echo _USER_DELETE_PROFILE_NOTICE; ?></p><form id="delete_user_form" action="index.php?option=user" method="post" name="delete_user_form" class="dk_form" onsubmit="return confirm_delete_profile()">
			<input type="hidden" name="task" value="delete" />
			<input type="hidden" name="id" value="<?php echo encode_userid($row['id']);?>" />
			<p align="right"><input type="submit" value="<?php echo _USER_DELETE_PROFILE; ?>" /></p>
		</form><?php
	}
} // end of userEdit()

	function confirmation() {
		?>
		<div class="dk_header"><h2><?php echo _SUBMIT_SUCCESS; ?></h2></div>
		<div class="dkcom_userconfirmation"><?php echo _E_ITEM_SAVED; ?></div>
		<?php
	}

// handler for customizable Lanius CMS parameters
// e.g. template and language
function custom_handler() {
	$template = in_path('template', $_GET);
	// process a different template in querystring
	if (isset($template)) {
		global $d;
		if ($d->ValidTemplate($template))
			d_setcookie('user_template', $template, 60*10);
	}
	
	// process a different language
	$lang = in('lang', __PATH, $_GET, '', 2);
	if (strlen($lang)) {
		global $my;
		if (is_file($GLOBALS['d_root'].'lang/'.$lang.'/language.xml'))
			$my->setLangCookie($lang);
	}

	// redirect back to home page
	global $d;
	CMSResponse::Redir('index.php');
}

function info_profile($row, $advanced = false) {
	global $d; ?>
		<div class="dk_header"><h2><?php echo sprintf(_USER_PROFILE, $row['name']); ?></h2></div>
		<div class="dk_content"><?php echo _NAME; ?>: <strong><?php echo $row['name'];?></strong></div>
		<?php
		global $_DRABOTS;
		$_DRABOTS->trigger('OnViewPublicProfile', array(&$row));
		if ($advanced) { ?>
			<hr />
			<div class="dk_content"><?php echo _USER_PRIVATE_PROFILE; ?></div>
			<div class="dk_content"><?php echo _USER_NAME; ?>: <strong><?php echo $row['username'];?></strong></div>
			<div class="dk_content"><?php echo _EMAIL; ?>: <strong><?php echo xhtml_safe($row['email']);?></strong></div>
			<div class="dk_content"><?php echo _LANGUAGE; ?>: <strong><?php echo $d->LangLabel($row['lang']); ?></strong></div>
			<?php global $d_stats;
			if ($d_stats) { ?>
			<div class="dk_content"><?php echo _USER_LAST_VISIT; ?>: <strong><?php echo $d->DateFormat($row['lastvisitDate']); ?></strong></div>
			<?php } ?>
			<div class="dk_content"><?php echo _USER_TIMEZONE; ?>: <strong><?php
			if ($row['timezone'] === '')
				echo 'Auto';
			else
				echo $row['timezone'];
			?></strong></div>
			<?php
			$_DRABOTS->trigger('OnViewPrivateProfile', array(&$row));
			?>
			<form id="info_user_form" action="index.php" method="get" name="info_user_form" class="dk_form">
			<p><input class="dk_button" type="submit" name="btnsubmit" value="<?php echo _E_EDIT; ?>" />
				<input type="hidden" name="option" value="user" />
				<input type="hidden" name="task" value="edit" />
				<input type="hidden" name="id" value="<?php echo encode_userid($row['id']);?>" />
				<input type="hidden" name="Itemid" value="<?php echo $GLOBALS['Itemid']; ?>" />
			</p>
		</form>
<?php		
	}
} // end of info_profile()

?>