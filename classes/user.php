<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

// maximum number of login attempts
define('_LOGIN_MAX_ATTEMPTS', 20);

// default fallback when everything goes very bad (Auto)
define('_CMS_DEFAULT_TIMEZONE', '');

class CMSUser {

	var $id=false;
	var $captcha_ok;
	var $gid=0;
	var $username='';
	var $name='';
	var $email='';
	var $lang = 'en';
	var $ip;
	var $timezone;
	//groups 1 = registered , 2 = editor  , 3 = publisher , 4 = manager , 5 = admin

	function setLangCookie($lang) {
		$this->lang = $lang;
		d_setcookie( 'user_lang', $lang, 60*15);
	}

	function _cookie_lang() {
		// start user change language
		$lang = in_cookie('user_lang', '', 2);
		
		if (strlen($lang)) {
			$lang = path_safe($lang);
			global $d_root;
			if ($this->IsValidLang($lang)) {
				$this->setLangCookie($lang);
				return true;
			} else
				d_unsetcookie('user_lang');
		}
		return false;
	}
	
	var $_lid;

	function recognize_lid() {
		return $this->BrowserLanguage();
	}
	
	// extract language from browser header and eventually fallback to default
	function BrowserLanguage() {
		if (!isset($this->_lid)) {
			global $d_root;
			$this->_lid = $GLOBALS['d_deflang'];
			$hdr = @$_SERVER["HTTP_ACCEPT_LANGUAGE"];
			if (!empty($hdr)) {
				//TODO: case insensitive match instead?
				if (preg_match_all('/([a-z]{2})/', strtolower($hdr), $m)) {
					foreach( $m[0] as $lid ) {
						if ($this->IsValidLang($lid)) {
							$this->_lid = $lid;
							// added in revision 600, will return first good language
							break;
						}
					}
				}
			}
		}
		return $this->_lid;
	}
	
/*	function _valid_lang($lang) {
		return $this->ValidLang($lang);
	}	*/
	
	var $_valid_langs = array();
	
	function IsValidLang($lang) {
		global $d;
		if (isset($d->_valid_langs) && in_array($lang, $d->_valid_langs))
			return true;
		if (!isset($this->_valid_langs[$lang])) {
			global $d_dlangs;
			$this->_valid_langs[$lang] = ((strpos($d_dlangs, $lang) === FALSE) &&
					is_file($GLOBALS['d_root'].'lang/'.$lang.'/language.xml'));
		}
		return $this->_valid_langs[$lang];
	}

	function ValidLang($lang) {
		// in case of 'auto' mode or invalid language id
		if (!strlen($lang) || !$this->IsValidLang($lang))
			return $this->BrowserLanguage();
		return $lang;
	}

	function GetIP() {
		global $d_root;
		if (!isset($this->ip)) {	// please keep curly braces
			$this->ip = include($d_root.'includes/retrieve_ip.php');
		}
		return $this->ip;
	}
	
	## used to prevent 0 not being written
	function GetID() {
		if (!$this->id)
			return "0";
		return (string)$this->id;
	}

	function initlogin(&$row) {
		global $d_uid;
		//DEBUG
		if (!count($row)) {
			global $d_root;
			include_once $d_root.'classes/http.php';
			CMSResponse::ServiceFatal('500 Server Error', 'Database is corrupted (E001)');
			return false;
		}
		$this->gid = $row['gid'];
		$this->name=$row['name'];
		$this->username=$row['username'];
		$this->email=$row['email'];
		
		if (!$this->_cookie_lang())	
			$this->lang = $this->ValidLang($row['lang']);
		//DEBUG
		if (!isset($this->lang)) {
			global $d_root;
			include_once $d_root.'classes/http.php';
			CMSResponse::ServiceFatal('500 Server Error', 'Database is corrupted (E002)');
			return false;
		}

		$this->timezone = $row['timezone'];

		// the following workaround should prevent some glitchy PHP behaviours
		$id = $this->id;
		$_SESSION[$d_uid.'-uid'] = $id;
		
		return true;
	}

	// check the POST if a 'remember' flag was specified
	// in such case, set the cookies for username and hashed password
	// this function is currently called by com_login
	function remember() {
		if (!$this->gid)
			return;
	//	$etime=60*15; // fifteen minutes
		if (isset($_POST['remember'])) {
			global $conn, $d_uid;
			$row = $conn->SelectRow('#__users', 'password', ' WHERE id='.$this->id);
			$etime=3600*24*14;
			global $d_uid;
			d_setcookie('cusername',$this->username, $etime);
			d_setcookie('cpassword',md5($d_uid.$row['password']), $etime);
			d_setcookie('cremember', 1, $etime);
		}
	}
	
	function CMSUser() {
		$this->_valid_langs = array($GLOBALS['d_deflang'] => true);
	}

	// the initializer
	function Initialize($cookie_login = true) {
		global $d_uid;
		// retrieve user id from session object
		$finished = false;
		if (isset($_SESSION[$d_uid.'-uid'])	) {
			$this->id = (int)$_SESSION[$d_uid.'-uid'];
			global $conn;
			$row=$conn->SelectRow('#__users', 'gid,name,username,password,email,lang,timezone', ' WHERE id='.$this->id);
			// the session contains an invalid ID, clean it up
			//NOTE: no validation is performed on session uid, because it must be trusted data
			if (!count($row))
				unset($_SESSION[$d_uid.'-uid']);
			else if ($this->initlogin($row)) {
				$this->_cookie_lang();
				$finished = true;
			}
		}
		
		if ($cookie_login) {
			if (!$finished) {
				$cusername = in_cookie('cusername');
				$cpassword = in_cookie('cpassword');
				if (isset($cusername) && isset($cpassword)) {
					global $conn;		
					$row=$conn->SelectRow('#__users', 'id,gid,name,username,password,email,lang,timezone', ' WHERE username=\''.sql_encode($cusername).'\'');
					if (count($row)) {
						if ($cpassword===md5($GLOBALS['d_uid'].$row['password'])) {
							$this->id=$row['id'];
							$this->lang = $this->ValidLang($row['lang']);
							if ($this->initlogin($row)) {
								$finished = true;
							}
						}
					}
				}
			}
			
			if (!$finished) {
				if (!$this->_cookie_lang())
					$this->lang = $this->BrowserLanguage();
			}
		} else
			$this->lang = $this->BrowserLanguage();
	}

	function http_logout($destination) {
		header('HTTP/1.0 401 Unauthorized', false);
		header('WWW-Authenticate: Bogus', false);
		global $d, $d_website, $d_rand;
		$url = $d_website.$destination;
	//	$p = strpos($url, '//');
	//	$url = substr($url, 0, $p+2).$d_rand.'-logout:logout@'.substr($url, $p+2);
		CMSResponse::Redir($url);
	}
	
	function RemoveLoginInfo() {
//		if (in_session('uid') === null)			return;
		global $d_stats,$d_uid, $d_http_auth;
		d_unsetcookie('cusername');
		d_unsetcookie('cpassword');
		d_unsetcookie('cremember');
		if ($d_stats) {
			global $conn;
			$conn->Delete('#__simple_stats', " WHERE id>1 AND ip='".$this->GetIP()."'");
		}
		$GLOBALS['_SESSION'][$d_uid.'-uid'] = null;
	}

	function Logout($admin = false) {
		global $d_http_auth;
		$this->RemoveLoginInfo();
		$prev_url = $this->PreviousPage($admin, true);
		if ($d_http_auth)
			$this->http_logout($prev_url);
		else
			CMSResponse::Redir($prev_url);
	}

	function PreviousPage($admin = true, $out = false) {
		global $d_website;
		// go back to the previous page (if any)
		$referer = CMSRequest::Referer();
		if (isset($referer)) {
			$redir = strtolower($referer);
			if (strpos($redir, $d_website)===0) {
				$redir = substr($redir, strlen($d_website));
				if ($redir[0]=='/') $redir = substr($redir, 1);
				if (strpos($redir, 'index.php')===0)
					return $redir;
				if (strpos($redir, 'admin.php')===0) {
					if (!$admin || $out)	return 'index.php';
					return $redir;
				}
			}
		}
		if (!$admin || $out)
			return 'index.php';
		return 'admin.php';
	}

	// compares the captcha and returns its validity
	function valid_captcha($prefix) {
		if (!isset($this->captcha_ok)) {
			global $_DRABOTS;
			$_DRABOTS->loadBotGroup('captcha');
			// trigger only once the verification
			$rv = $_DRABOTS->trigger('OnCaptchaVerify', array($prefix), 1);
			if (empty($rv)) {
				//I18N
				CMSResponse::Back("Cannot verify CAPTCHA because no CAPTCHA drabot active");
				return;
			}
			$this->captcha_ok = $rv[0];
		}
		if (!$this->captcha_ok) {
			// retrieve the old querystring
			$captcha_page = in_session($prefix.'_captcha_page');
			if (strlen($captcha_page))
				$captcha_page = '?'.$captcha_page;
			unset_session($prefix.'_captcha_page');
			CMSResponse::FullRedir(CMSRequest::ScriptName().$captcha_page, _CAPTCHA_INCORRECT_CODE);
			return false;
		}
		return $this->captcha_ok;
	}

	function _can_retry() { return $this->CanLogin(); }

	function CanLogin() {
		global $d_uid;
		$trials = in_session('trials');
		if (!isset($trials))
			return true;
		if ($trials==_LOGIN_MAX_ATTEMPTS) {
			global $d;
			$d->log(LOG_WARNING, 'Massive login trials ('.$_SESSION[$d_uid.'-trials'].' attempts), further requests will be refused');
		}
		if ($trials>_LOGIN_MAX_ATTEMPTS)
			return false;
		// user can keep trying to log in
		return true;
	}

	var $_login_msg;

	function Login($min_gid = 1, &$_S, $relayed = false) {
		global $d;
		
		//TODO: it would be better to fix this hack
		if (!$relayed) {
			$p = $d->GetComponentParamsRaw('login');
			// if the captcha check is enabled for the login component, check it
			if ($p->get('captcha', 0) && !$this->valid_captcha('login'))
				return false;
		}

		if ($min_gid>=3)
			$prev_url = 'admin.php';
		else $prev_url = 'index.php?option=login';
		if ('' === ($username = trim(in_raw('username', $_S, '', 25))) ||
			'' === ($password = in_raw('password', $_S, '', 100)) ) {
			if (!$relayed) {
				CMSResponse::Redir($prev_url, _LOGIN_INCOMPLETE);
			}
			// will not record this as an attempt
			return false;
		}
		
		// check validity of username
		if (unix_name($username) != $username) {
			CMSResponse::Redir($prev_url, _USER_VALID_UNIX);
			return false;
		}
		$username = sql_encode($username);
		
		$success  = $this->CanLogin();
		if (!$success) {
			$this->_failed_attempt();
			CMSResponse::Forbidden();
			return false;
		} else // these are the standard steps to be performed for a valid login procedure
			$success = $this->ActualLogin($username, $password, $min_gid);
		$this->FinalizeLogin($success, $min_gid, $prev_url);
		return $success;	
	}
	
	function _failed_attempt() {
		// brute force attacks prevention
		$trials = in_session('trials', __RAW, 0);
		out_session('trials', $trials+1);
	}

	function FinalizeLogin($success, $min_gid, $prev_url = null) {
		if (!$success) {
			$this->_failed_attempt();
			global $d_http_auth;
			if (!$d_http_auth)
				CMSResponse::Redir($prev_url, $this->_login_msg);
		} else {
			global $d_uid;
			if (isset($_SESSION[$d_uid.'-trials']))
				unset($_SESSION[$d_uid.'-trials']);
			$this->remember();
			global $d_stats, $time;
			if ($d_stats) {
				global $conn;
				$conn->Update('#__users', 'lastvisitDate ='.$time, " WHERE id = ".$this->id);
			}
			// perform redirection after login
			CMSResponse::Redir($this->PreviousPage(($min_gid>=3)));
		}
	}
	
	function _get_user_row($username) {
		global $conn;
		return $conn->SelectRow('#__users', '*', ' WHERE username=\''.sql_encode($username).'\'');
	}

	## finalize login operation checked elsewhere (e.g. in other authentication plugin)
	## when using this method no password check is performed
	function RelayedLogin($success, $username, $min_gid) {
		if ($success) {
			$row = $this->_get_user_row($username);
			if (!$this->CMSLogin($row, $min_gid))
				$success = false;
		}
		if ($min_gid>=3)
			$prev_url = 'admin.php';
		else $prev_url = 'index.php?option=login';

		$this->FinalizeLogin($success, $min_gid, $prev_url);
		return $success;
	}
	
	## implemented for safe RPC logins
	## $username must NOT be sql-encoded
	function ActualLogin($username, $password, $min_gid = 1) {
		global $_DRABOTS;
		
		// load bots which could override the default login mechanism
		$results = $_DRABOTS->trigger('onAuthenticateOverride', array($username, $password, $min_gid));
		$override = false;
		foreach($results as $result) {
			// successful authentication override and successful login
			if ($result === true) {
				$override = true;
				continue;
			}
			// the function choose to not take over the override, but some other function might have
			if ($result === false)
				continue;
			// the function took over the authentication override, failed login and returned an error (string)
			$this->_login_msg = $result;
			return false;
		}
		
		if (!$override) {
			global $conn;
			$row = $conn->SelectRow('#__users', '*', " WHERE username = '".sql_encode($username)."' AND password = '".md5($password)."'");
			if (!count($row)) {
				$this->_login_msg = _LOGIN_INCORRECT;
				return false;
			}
		} else // the existance of this user has already been checked by the overrider function when calling User::ClearPwCheck()
			$row = $this->_get_user_row($username);
		
		return $this->CMSLogin($row, $min_gid, $override);
	}

	## when previous checks have allowed the login, check if the user is enabled and/or if further authentication succeeds
	function CMSLogin(&$rsa, $min_gid, $override = false) {
		if ($rsa['gid']<$min_gid) {
			//TODO: use HTTP error instead?
			$this->_login_msg = _UNAUTHORIZED_ACCESS;
			return false;
		}
	
		if (!$rsa['published']) {
			$this->_login_msg = _LOGIN_BLOCKED;
			return false;
		}
		
		// if the login has not been checked before, then perform the authentication here
		// examples:
		// DIGEST AUTH + LDAP: check clear password in DB (for Digest Auth) + run onAuthenticate LDAP ($override = false here)
		// BASIC AUTH + LDAP: override in ActualLogin (for Basic Auth) using LDAP + do not run onAuthenticate ($override = true here)
		// NORMAL POST + LDAP: override in ActualLogin using LDAP + do not run onAuthenticate ($override = true here)
		if (!$override) {
			// load core drabots (for authentication plugins)
			global $_DRABOTS;
			
			$results = $_DRABOTS->trigger('onAuthenticate', $rsa);
			
			foreach($results as $result) {
				if ( strlen($result) ) {
					$this->_login_msg = $result;
					return false;
				}
			}
		}

		$this->id=$rsa['id'];
		global $d_stats;
		if ($d_stats) {
			global $conn;
			$conn->Execute("DELETE FROM #__simple_stats WHERE id>1 AND ip='".$this->GetIP()."'");
		}
		$this->initlogin($rsa);
		return true;
	}

	function GetPassword($username) {
		global $conn;
		$row = $conn->SelectRow('#__users', 'clear_password', ' WHERE username=\''.sql_encode($username).'\'');
		if (!isset($row['clear_password']))
			return null;
		$pw = $row['clear_password'];
		if (!$this->ClearPwCheck($pw))
			return false;
		return $pw;
	}
	
	function ClearPwCheck($pw) {
		if (!strlen($pw)) {
			global $d_clear_pw;
			CMSResponse::Unavailable(_LOGIN_CLEAR_PW_REQUIRED.'<br />'.
							($d_clear_pw ? _LOGIN_CHANGE_PW : _LOGIN_CLEAR_PW_DISABLED));
			return false;
		}
		return true;
	}

	function admin_login($data) {
		// administrative login for publisher, manager and admin users
		return $this->login(3, $data);
	}

	//L: deprecated/unused??
	function isuser()
	{
		if ($this->id)
			return true;
		else
			return false;
	}

	## tells if an editor can edit a content item
	function can_edit($owner) {
		// return true if editor AND owner of element
		if ($this->gid==2 && $this->id==$owner)
			return true;
		// return true if publisher or above
		return ($this->gid>2);
	}
	
	## tells if user is allowed to publish
	function can_publish() {
		return ($this->gid>=2);
	}
	
	## tells if user is allowed to submit
	function can_submit() {
		return ($this->gid>=1);
	}
	
	function is_admin() { return ($this->gid==5); }

	function GetTimezone() {
		if ( !isset($this->timezone) || !strlen($this->timezone) )
			return constant('_LOCALE_DEFAULT_TIMEZONE');
//			if (function_exists('date_default_timezone_get'))		return date_default_timezone_get();
		return $this->timezone;
	}
	
	// information shown on the logs about this user impersonation
	function LogInfo() {
		if (!$this->id)
			return 'Anonymous (IP '.$this->GetIP().')';
		return sprintf('User %s (ID #%d)', $this->username, $this->id);
	}
	
	var $errorMsg;
	
	function _ValidateUser($id, $username, $password, &$gid, $email, &$user_tz, &$clear_pw) {
		if (isset($username)) {
			// enforce usage of a UNIX username
			if (!preg_match('/^[a-z0-9_\\-]+$/', $username)) {
				$this->errorMsg = _USER_VALID_UNIX;
				return false;
			}
		}

		// the created user cannot have higher GID than creator
		if ($this->gid && ($gid>$this->gid))
			$gid = $this->gid;
			
		// to reset language for this user
		if ($id == $this->id)
			d_unsetcookie('user_lang');
		
		global $conn;
		if (isset($username)) {
			/* check for username and email */
			$urow=$conn->SelectRow('#__users', 'id', ' WHERE username=\''.sql_encode($username)."'".
				(isset($id) ? ' AND id<>'.$id : '')
				);
			if (count($urow)) {
				$this->errorMsg = _USER_UNAME_INUSE;
				return false;
			}
		}
		
		$urow=$conn->SelectRow('#__users', 'id', ' WHERE email=\''.sql_encode($email)."'".
			(isset($id) ? ' AND id<>'.$id : '') );
		if (count($urow)) {
			$this->errorMsg = _USER_EMAIL_INUSE;
			return false;
		}
		
		// retrieve the user timezone
		if (isset($user_tz)) {
			if (substr(phpversion(), 0, 1)=='4') {
				global $timezones;
				if (!isset($timezones[$user_tz]))
					$user_tz = _CMS_DEFAULT_TIMEZONE;
			} else {
				global $d_root, $timezones;
				include_once $d_root.'includes/i18n/timezones.php';
				if (!in_array($user_tz, $timezones))
					$user_tz = _CMS_DEFAULT_TIMEZONE;
			}
		} else $user_tz = _CMS_DEFAULT_TIMEZONE;
		$user_tz = sql_encode($user_tz);
		
		global $d_clear_pw;
		if ($d_clear_pw)
			$clear_pw = sql_encode($password);
		else
			$clear_pw = '';
		return true;
	}
	
	// all values passed must *NOT* be SQL-encoded, they may be XHTML safe
	function	Create($name, $username,$password, $email, $lang, $user_tz, $gid, $user_published = 1) {
		$clear_pw = '';
		if (!$this->_ValidateUser(null, $username, $password, $gid, $email, $user_tz, $clear_pw))
			return false;
		// trigger drabots which could prevent this user from being registered
		global $_DRABOTS;
		$results = $_DRABOTS->trigger('BeforeCreateUser',
				array($name, $username, $email, $password));
		$user_id = null;
		foreach($results as $result) {
			// this is an integer, do not insert anything please
			if (is_int($result)) {
				$user_id = $result;
				continue;
			}
			// this is an error message, failure
			if (is_string($result)) {
				$this->errorMsg = $result;
				return false;
			}
			// else: boolean true
		}
	
		if (!isset($user_id)) {
			global $time, $conn;
			$conn->Insert('#__users', '(name,username,email,password,registerDate,lastvisitDate,lang,timezone,clear_password,published,gid)',
			'\''.sql_encode($name)."','".sql_encode($username)."','".sql_encode($email)."','".
			md5($password)."',$time,$time,'".$lang."', '$user_tz', '$clear_pw', $user_published, $gid" );
			$user_id = $conn->Insert_ID();
		}
		global $d;
		if (!isset($GLOBALS['option']))
			$com_name = $GLOBALS['com_option'];
		else $com_name = $GLOBALS['option'];
		$d->log(6, 'User '.$username.' (#'.$user_id.', GID '.$gid.') created from component '.$com_name);
	
		// trigger drabots after registration
		$_DRABOTS->trigger('AfterCreateUser', array($user_id, $name, $username, $email, $password));

		return $user_id;
	}
	
	function	Modify($id,$name, $username = null,
				$password = '', $email, $lang, $user_tz, $gid = null, $user_published = null) {

		$clear_pw = '';
		if (!$this->_ValidateUser($id, $username, $password, $gid, $email, $user_tz, $clear_pw))
			return false;

		if (strlen($password))
			$upass= ",password='".md5($password)."', clear_password='$clear_pw'";
		else
			$upass='';

		global $_DRABOTS;
		$r = $_DRABOTS->trigger('BeforeModifyUser', array($id, $name, $username, $password,
					$email, $lang, $user_tz));
		if (isset($r)) {
			foreach($r as $rv) {
				if ($rv!==true) {
					$this->errorMsg = $rv;
					return false;
				}
			}
		}
		
		global $conn;
		$conn->Update('#__users',	"name='".sql_encode($name)."',"
								.(isset($username)? "username='".sql_encode($username)."'," : "")
								."email='".sql_encode($email)."',"
								.(isset($gid) ? "gid=".$gid."," : "")
								."lang='".sql_encode($lang)."', "
								.(isset($user_published) ? "published=".$user_published.', ' : "")
								."timezone='$user_tz' "
								.$upass, " WHERE id=$id");
		$_DRABOTS->trigger('AfterModifyUser', array($id));
		return true;
	}
	
	function Remove($id) {
		global $_DRABOTS, $conn;
		// get the username
		$row = $conn->SelectRow('#__users', 'username', ' WHERE id='.$id);
		if (empty($row))
			//I18N
			return 'User not found';
		$r = $_DRABOTS->trigger('BeforeDeleteUser', array($id, $row['username']));
		foreach($r as $rv) {
			if ($rv !== true)
				return $rv;
		}
		global $d;
		$conn->Delete('#__view_filter', ' WHERE userid='.$id);
		// delete the user account
		$conn->Delete('#__users', ' WHERE id='.$id);
		$d->log(6, 'User #'.$id.' ('.$row['username'].') deleted by user '.$this->username.' (#'.$this->id.')');
		$_DRABOTS->trigger('AfterRemoveUser', array($id));
		return true;
	}

} // CMSUser class

?>
