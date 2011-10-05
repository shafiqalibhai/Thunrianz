<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## LDAP registration drabot
# @author legolas558
#
# will register the user into LDAP system, acting as a hook
#

$_DRABOTS->registerFunction( 'BeforeCreateUser', 'botLDAPRegister' );
$_DRABOTS->registerFunction( 'BeforeDeleteUser', 'botLDAPRemove' );
$_DRABOTS->registerFunction( 'BeforeModifyUser', 'botLDAPModify' );

function _init_reg_ldap() {
	global $d_root, $_DRABOTS;
	include_once $d_root.'classes/ldap.php';
	
	// get parameters from sister drabot
	$base_params = $_DRABOTS->GetBotParameters('core', 'auth_ldap');
	$params = $_DRABOTS->GetBotParameters('core', 'reg_ldap');

	return new LDAPBase($base_params->get('server', 'localhost'),
				$base_params->get('base_dn'),		// 'dc=example,dc=com'
				$params->get('bind_dn'),		// 'cn=root,dc=example,dc=com'
				$params->get('bind_password'),
				$base_params->get('port', '389')		// unnecessary if URL specified
	);
}

function botLDAPModify($id, $name, $username = null, $password = '',$email, $lang, $user_tz) {
//	return true;
	global $_DRABOTS;
	// get parameters from this drabot
	$params = $_DRABOTS->GetBotParameters('core', 'reg_ldap');
	
	$fmt = $params->get('dn_format', 'cn=%s,dc=example,dc=org');
	
	// this is a modification from front-end, the username password is empty
	// the username will be anyway retrieved because we need to know the previous username
	$fsql = '';
//	if (!isset($username)) {
		global $conn;
		$row = $conn->SelectRow('#__users', 'username', ' WHERE id='.$id);
		$username = $row['username'];
//	}
	$info = _botLDAP_prepare_info_block($username, $password, $email, $params->get('custom_attributes', ''));

	$ldap = _init_reg_ldap();
	if (! $ldap->Bind())
		return $ldap->Error();

	// modify LDAP entry
	if (!@ldap_modify($ldap->ldapConn, sprintf($fmt, $username), $info) ) {
		return ldap_error($ldap->ldapConn);
	}
	
	$ldap->Free();

	// LDAP registration successful
	return true;

}

function _botLDAP_prepare_info_block($reg_user, $reg_password = '', $email, $custom_attrs) {
	$custom_fields = array_map('trim', explode("\n", $custom_attrs));
	
	// prepare data
	$info["cn"] = $info["sn"] = $info['uid'] = $reg_user;
	$info['mail'] = $email;
	$info["objectclass"] = array('top', 'inetOrgPerson', 'person');
	// add the password
	if (strlen($reg_password))
		$info['userPassword'] = "{md5}".base64_encode(pack("H*",md5($reg_password)));
	
	foreach($custom_fields as $couple) {
		if (!strlen($couple))
			continue;
		$p = strpos($couple, '=');
		if ($p===false)
			trigger_error("Invalid custom field specified");
		$info[trim(substr($couple, 0, $p))] = trim(substr($couple, $p+1));
	}
	return $info;
}


function botLDAPRegister($reg_name, $reg_user, $reg_email, $reg_password) {
//	return true;
	global $_DRABOTS;
	// get parameters from this drabot
	$params = $_DRABOTS->GetBotParameters('core', 'reg_ldap');
	
	$fmt = $params->get('dn_format', 'cn=%s,dc=example,dc=org');
	
	$info = _botLDAP_prepare_info_block($reg_user, $reg_password, $reg_email, $params->get('custom_attributes', ''));

	$ldap = _init_reg_ldap();
	if (! $ldap->Bind())
		return $ldap->Error();

	// add data to directory
	if (!@ldap_add($ldap->ldapConn, sprintf($fmt, $reg_user), $info) ) {
		return ldap_error($ldap->ldapConn);
	}
	
	$ldap->Free();

	// LDAP registration successful
	return true;
}

function botLDAPRemove($id) {
//      return true;
        global $_DRABOTS;
        // get parameters from this drabot
        $params = $_DRABOTS->GetBotParameters('core', 'reg_ldap');

        $fmt = $params->get('dn_format', 'cn=%s,dc=example,dc=org');

        $ldap = _init_reg_ldap();
        if (! $ldap->Bind())
                return $ldap->Error();

	global $conn;
	$row = $conn->SelectRow('#__users', 'username', ' WHERE id='.$id);

        // remove entry from directory
        if (!@ldap_delete($ldap->ldapConn, sprintf($fmt, $row['username'])) )
                return ldap_error($ldap->ldapConn);

        $ldap->Free();

        // LDAP remove successful
        return true;
}

?>
