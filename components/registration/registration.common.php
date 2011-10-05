<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function retrieve_captcha_js(&$params, $prefix) {
	if (! $params->get('captcha', 1))
		return '';
	global $_DRABOTS;
	$_DRABOTS->loadBotGroup('captcha');
	$r = $_DRABOTS->trigger('OnCaptchaJSFailCondition', array('form', $prefix));
	if (empty($r))
		return '';
	return 'else if ('.$r[0].') {
			alert( "'._FORM_NC.'" );
		}';
}

function commonJS($xup = false) {
	global $params, $d;
	$d_useractivation = $params->get('registration_activation', 0);
	$d->add_raw_js('
		function regForm_validate() {
			var form = document.regForm;'.
			($xup ? '
			if (form.reg_xup.value != "")
				return true;' : '').'
			var r = new RegExp("^[A-Za-z0-9_\\-]+$");

			// do field validation
			if (form.reg_name.value == "") {
				alert( "'._REGWARN_NAME.'" );
			} else if (form.reg_user.value == "") {
				alert( "'._REGWARN_UNAME.'" );
			} else if (!r.exec(form.reg_user.value) || form.reg_user.value.length < 3) {
				alert( "'.sprintf( "Please specify a valid UNIX user name", _USER_NAME, 3 ).'" );
			} else if (form.reg_email.value == "") {
				alert( "'._REGWARN_MAIL.'" );
			} '.($d_useractivation ? '' : ' else if (form.reg_password.value.length < 6) {
				alert( "'._REGWARN_PASS.'" );
			} else if (form.reg_password2.value == "") {
				alert( "'._REGWARN_VPASS1.'" );
			} else if ((form.reg_password.value != "") && (form.reg_password.value != form.reg_password2.value)){
				alert( "'._REGWARN_VPASS2.'" );
			}').retrieve_captcha_js($params, 'registration').' else {
				return true;
			}
			return false;
		}');
}

?>
