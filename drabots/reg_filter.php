<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Account filtering drabot
# @author legolas558
#
# will filter out invalid accounts following some custom criteria
# e.g. not-working email providers
#

$_DRABOTS->registerFunction( 'BeforeCreateUser', 'botFilterRegister' );
$_DRABOTS->registerFunction( 'BeforeModifyUser', 'botFilterModify' );

function botFilterModify($id, $name, $username = null, $password = '',$email, $lang, $user_tz) {
	return _botCheckEmailProvider($email);
}

function _botCheckEmailProvider($email) {
	global $_DRABOTS;
	$params = $_DRABOTS->GetBotParameters('core', 'reg_filter');
	
	if (preg_match('/^[\\w\\.\\-]+@('.$params->get('domains', 'hotmail').')\\.{1,}\\w{1,4}$/', $email)) {
		global $my;
		$path = bot_lang($my->lang, 'reg_filter');
		include_once $path;
		return sprintf(_REG_FILTER_INVALID_EMAIL, '<strong style=\"color:red\">'.$email.'</strong>', '<a href=\"http://www.openspf.org/SPF_vs_Sender_ID\" target=\"_blank\">http://www.openspf.org/SPF_vs_Sender_ID</a>');
	} else
		return true;
}

function botFilterRegister($reg_name, $reg_user, $reg_email, $reg_password) {
	return _botCheckEmailProvider($reg_email);
}

?>
