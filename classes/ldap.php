<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## LDAPBase class
# @author legolas558
# original @author countnikon@phpfreaks.com
# authentication code adapted from Mantis Bug Tracker
#
# LDAP authentication and query facilities
#

/*
global $d__ldap_resources, $d__ldap_clean;
$d__ldap_resources = array();
$d__ldap_clean = false;

function _free_ldap() {
	global $d__ldap_resources;
	foreach($d__ldap_resources as $ldap_bind)
		ldap_close($ldap_bind);
	return true; // unnecessary?
}
*/

class LDAPBase {
	var $ldapConn; //ldap connection storage variable
	var $ldapBind; //ldap bind storage variable
//	var $entries;	//ldap entries variable
//	var $ldapLookupUser;
//	var $ldapLookupPass;
	var $server;
	var $port;
//	var $by;
//	var $search;
	var $baseDN;
	var $ldapError;
	var $bindDN;
	var $bindPassword;

	function LDAPBase($server, $baseDN, $bindDN, $bind_pwd, $port = 389) {
		## server can be an URL or an hostname
		$this->server=$server;
		## port is not used if URL si specified
		$this->port=$port;
		$this->baseDN=$baseDN;
		$this->bindDN = $bindDN;
		$this->bindPassword = $bind_pwd;
	}
	
	function Error() {
		return $this->ldapError;
	}

	function Connect() {
		$this->ldapConn = @ldap_connect($this->server,$this->port);
		// it is unlikely to experience a failure here, new LDAP implementations perform actual connection
		// upon other ldap_* functions
		if ($this->ldapConn === false)
			$this->ldapError = _LDAP_CONNECT_FAILED;
		return ($this->ldapConn !== false);
	}

	function Bind() {
		if (!$this->Connect())
			return false;
		$res = @ldap_bind($this->ldapConn,$this->bindDN,$this->bindPassword);
		if ($res) {
			$this->ldapBind = $res;
/*			global $d__ldap_resources, $d__ldap_clean;
			$d__ldap_resources[] = $res;
			if (!$d__ldap_clean)
				register_shutdown_function('_free_ldap');	*/
			return true;
		} else {
			$this->ldapError = ldap_error($this->ldapConn);
			// to prevent resource leakage
			$this->Free();
			return false;
		}
	}
	
	function Free() {
		@ldap_unbind($this->ldapConn);
	}
	
} // class LDAPBase

class LDAPAuth extends LDAPBase {
	var $organization;
	var $uidField;
	var $roleField;
	var $role;
	
	function LDAPAuth($server, $baseDN, $bindDN, $bind_pwd,
			$role_field = '',
			$role = '',
			$organization = '',
			$uid_field = 'uid',
			$port = 389) {
		$this->role_field = $role_field;
		$this->role = $role;
		$this->organization = $organization;
		$this->uidField = $uid_field;
		parent::LDAPBase($server, $baseDN, $bindDN, $bind_pwd, $port);
	}

	# --------------------
	# Attempt to authenticate the user against the LDAP directory
	#  return true on successful authentication, false otherwise

	function Authenticate($username,$password) {
/*		# if password is empty and ldap allows anonymous login, then
		# the user will be able to login, hence, we need to check
		# for this special case.
		if ( is_blank( $password ) ) {
			return false;
		} */

		if (!$this->Bind())
			return false;

		$search_attrs  	= array( $this->uidField, 'dn' );
		$search_filter 	= "(&".$this->organization."(".$this->uidField.'='.$username.')';
		
		// apply role filtering
		if (strlen($this->role))
			$search_filter .= "(".$this->roleField."=*".$this->role."*)";
		
		$search_filter .= ")";

		# Search for the user id
		$sr	= ldap_search( $this->ldapConn, $this->baseDN, $search_filter, $search_attrs );
		$info	= ldap_get_entries( $this->ldapConn, $sr );

		$this->ldapError = _LDAP_ENTRY_ERROR;
		
		$authenticated = false;
		if ( $info ) {
			# Try to authenticate to each until we get a match
			$c = $info['count'];
			for ( $i = 0 ; $i < $c ; ++$i ) {
				$t_dn = $info[$i]['dn'];

				# Attempt to bind with the DN and password
				if ( @ldap_bind( $this->ldapConn, $t_dn, $password ) ) {
					$authenticated = true;
					break; # Don't need to go any further
				} else
					// get last error message if authentication attempt failed
					$this->ldapError = ldap_error($this->ldapConn);
			}
		}

		ldap_free_result( $sr );
		$this->Free();

		return $authenticated;
	}
	
}

?>
