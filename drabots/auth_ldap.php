<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## LDAP authentication drabot
# @author legolas558
#

// comment if you have broken your LDAP configuration (will allow you a normal access)
$_DRABOTS->registerFunction( 'onAuthenticateOverride', 'botLDAPAuthenticateOverride' );
$_DRABOTS->registerFunction( 'onAuthenticate', 'botLDAPAuthenticate' );

function _init_ldap() {
	global $d_root, $_DRABOTS;
	include_once $d_root.'classes/ldap.php';
	
	$params = $_DRABOTS->GetBotParameters('core', 'auth_ldap');

	return new LDAPAuth(	$params->get('server', 'localhost'),
				$params->get('base_dn'),		// 'dc=example,dc=com'
				$params->get('bind_dn'),		// 'cn=root,dc=example,dc=com'
				$params->get('bind_password'),
				$params->get('role_field', 'businessCategory'),
				$params->get('role', ''),
				$params->get('organization', ''),	// '(organizationname=*Traffic)'
				$params->get('uid_field', 'cn'),
				$params->get('port', '389')		// unnecessary if URL specified
	);
}

function botLDAPAuthenticateOverride($username, $password, $min_gid) {
//	return true;

	$ldap = _init_ldap();
	
	if (!$ldap->Authenticate($username, $password))
		return $ldap->Error();
	
	// LDAP authentication successful
	return true;
}

function botLDAPAuthenticate(&$user) {
	$ldap = _init_ldap();
	
	$pw = $user['clear_password'];
	global $my;
	if (!$my->ClearPwCheck($pw))
		// this message will never be shown due to prior redirection
		return 'password is empty';

	if (!$ldap->Authenticate($user['username'], $pw))
		return $ldap->Error();

	// LDAP authentication successful
	return '';
}

?>
