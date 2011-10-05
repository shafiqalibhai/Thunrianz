<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Http_Auth class
# @author legolas558
#
# HTTP authentication utility class
#

class HttpAuth {
	
	function GetRealm() {
		return str_replace('"', '%22', $GLOBALS['d_title']);
	}

	function RequireAuthentication($digest) {
		global $d_uid;
		$realm = HttpAuth::GetRealm();
		if ($digest) {
			header('WWW-Authenticate: Digest realm="'.$realm.'" qop="auth" nonce="'.uniqid('').'" opaque="'.md5($realm.$d_uid).'"');
		} else {
			header('WWW-Authenticate: Basic realm="'.$realm.'"');
		}
		CMSResponse::_service_fatal('401 Unauthorized', _UNAUTHORIZED_ACCESS);
		exit;
	}
	
	function Authenticate($min_level = 1, $digest = false) {
		global $my;
		if ($digest) {
			if (!isset($_SERVER['PHP_AUTH_DIGEST']))
				HttpAuth::RequireAuthentication(true);
			if (!$my->CanLogin()) {
				CMSResponse::Forbidden();
			}
			if (!preg_match('/username="([^"]*)",'.
			'\s*realm="([^"]+)",\s*nonce="([^"]+)",\s*uri="([^"]+)",'.
			'\s*response="([^"]+)",\s*opaque="([^"]+)",\s*qop=([^,]+),'.
			'\s*nc=([^,]+),\s*cnonce="([^"]+)"'.
			'/As', $_SERVER['PHP_AUTH_DIGEST'], $digest))
			{ //var_dump($_SERVER['PHP_AUTH_DIGEST']); trigger_error('Unable to parse AUTH_DIGEST');
				CMSResponse::BadRequest();
			}
			global $d_uid;
			$realm = HttpAuth::GetRealm();
			// opaque mismatch
			if ($digest[6]!==md5($realm.$d_uid))
				CMSResponse::BadRequest();
			$username = $digest[1];
			$password = $my->GetPassword($username);
			// not available service
			if ($password === false)
				return;
			if (!isset($password))
				$logged_in = false;
			else {
				// username
				$A1 = md5($username . ':' . $realm . ':' . $password);
				$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$digest[4]); // uri
				// nonce - nc - cnonce - qop
				$valid_response = md5($A1.':'.$digest[3].':'.$digest[8].':'.$digest[9].':'.$digest[7].':'.$A2);
				// the HTTP auth check is performed outside the core class
				$logged_in = $my->RelayedLogin( ($digest[5] === $valid_response), 
								$username, $min_level);
			}
		} else {
			// Basic authentication
			if (!isset($_SERVER['PHP_AUTH_USER']))
				HttpAuth::RequireAuthentication(false);
/*			if (!$my->CanLogin()) {
				CMSResponse::Forbidden();
			}	*/
			$data = array('username' => $_SERVER['PHP_AUTH_USER'],
					'password' => $_SERVER['PHP_AUTH_PW']);
			$logged_in = $my->Login($min_level, $data, true);
		}
		if ($logged_in)
			CMSResponse::Redir($my->PreviousPage());
		else
			HttpAuth::RequireAuthentication($digest);
	}
	
} // class HttpAuth

?>