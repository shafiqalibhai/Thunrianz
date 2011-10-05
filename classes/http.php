<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## CMSResponse class
# @author legolas558
#
# various web response related methods

global $d__compressing;

$d__compressing = 	@ini_get('zlib.output_compression') || @ini_get('zlib.output_handler');;

class CMSResponse {

	function Move($location) {
//		header('Status: 301 Moved Permanently', true, 301);
		header('Location: '.$location, true, 301);
		CMSResponse::SafeExit();
	}
	
	// will fix some bad behaviour on Apache 1.3.39 with PHP as CGI
	function SafeExit() {
		CMSResponse::ClearOutput();
		exit();
	}

	function SeeOther($location) {
//		header('Status: 303 See Other', true, 303);
		header('Location: '.$location, true, 303);
		CMSResponse::SafeExit();
	}
	
	function ContentUnauthorized($is_inline = false, $add = '') {
		CMSResponse::Unauthorized($add, true, $is_inline);
	}
	
	function BackendUnauthorized($add = '') {
		// will mask the error only if non-administrative
		CMSResponse::Unauthorized($add, ($my->gid >= 4));
	}
	
	function TrivialUnauthorized() {
		CMSResponse::Unauthorized('', true, false, true);
	}
	
	function Unauthorized($add = '', $can_mask = true, $is_inline = false, $trivial_deny = false) {
		global $my, $pathway, $d, $d_resource_deny;
		// redirect to website if access failed
		$chklo = in_session('chklo');
		if (isset($chklo)) {
			unset($_SESSION[$GLOBALS['d_uid'].'-chklo']);
			if ($chklo) {
				CMSResponse::Redir($GLOBALS['d_website']);
				return;
			}
		}

		if (!$is_inline) {
			// log this attempt if action was not trivial
			if (!$trivial_deny)
				$d->log(3, $my->LogInfo().' attempted to access resource '.CMSRequest::URI());
			// mask the error
			if ($can_mask && $d_resource_deny) {
				CMSResponse::NotFound();
				return;
			}
		}
		//FIXME
		$has_p = isset($pathway);
		if ($has_p)
			$pathway->add(_UNAUTHORIZED_ACCESS);
		echo _UNAUTHORIZED_ACCESS_DESC.$add;
		if (!$is_inline && $has_p && !$my->id) {
			echo "<div><br />" ._DO_LOGIN.' <a href="index.php?option=login">'._BUTTON_LOGIN.'</a></div>';
			
			$cparams = $d->GetComponentParamsRaw('registration');
			if (isset($cparams) && $cparams->get('registration_new', 1)) {
				echo "<br />" ._NO_ACCOUNT.' <a href="index.php?option=registration&amp;task=register"> '._CREATE_ACCOUNT.'</a>';
			}
		}
	}

	function ClearOutput() {
		global $d__compressing;
		if ($d__compressing)
			return;
		$l=ob_get_level()-1;
		for($i=0;$i<$l;++$i)
			ob_end_clean();
	}
	
	function Unavailable($msg = null) {
		$header = '503 Service Unavailable';
		if (!isset($msg)) $msg = $header;
		CMSResponse::ServiceFatal($header, $msg);
	}

	function ServiceFatal($http_header, $msg, $title = null) {
		global $my, $d_root;
		CMSResponse::ClearOutput();
		$code = explode(' ', $http_header);
		$code = current($code);
		header('Status: '.$http_header, true, $code);
		include $d_root.'includes/servererror.php';
		if (!isset($title)) $title = $http_header;
		service_msg($title, $msg);
		/*
		if (preg_match('/msie/i', (string)@$_SERVER['HTTP_USER_AGENT']))
			echo '<!-- DISALLOW MSIE "FRIENDLY" ERROR MESSAGES, see also http://support.microsoft.com/default.aspx?scid=kb;en-us;Q294807#3'."\n".
				random_string(500).' -->';
		*/
		CMSResponse::SafeExit();
	}

	function ServerError() {
		global $d_offline_msg;
		CMSResponse::ServiceFatal('500 Server Error', $d_offline_msg, _SE_OFFLINE);
	}

	function Forbidden() {
		CMSResponse::ServiceFatal('403 Forbidden', _SE_ACCESS_DENIED);
	}

	function NotFound() {
		CMSResponse::ServiceFatal('404 Not Found', _SE_NOT_FOUND);
	}

	function BadRequest() {
		CMSResponse::ServiceFatal('400 Bad Request', _SE_BAD_REQUEST);
	}

/*	L: this function asserts that  output buffering enabled,
	so that page generation time is smaller */
	function Start($want_compression = true) {
		if (!$want_compression) {
			ob_start();
			return;
		}
		global $d_gzip, $d__compressing;
		if ($d_gzip) {
			if (!$d__compressing && (strpos(strtolower(getenv('HTTP_ACCEPT_ENCODING')),'gzip')!==false)) {
				// error silencing applied to fix bug DAF1DXKN (when URL-rewriters are automatically cast by the provider for SEO reasons)
				if (@ob_start("ob_gzhandler"))
					$__compressing =true;
			} else
				ob_start();
		} else // force output buffering
			ob_start();
	}
	
	function Compressing() {
		return $GLOBALS['d__compressing'];
	}
	
	//DEPRECATED because of referral headers / javascript history usage
	function Back($msg = '') {
		CMSResponse::Redir('', $msg);
	}
	
	function FullRedir($url, $msg) {
		// send a refresh header
		if (strlen($url))
			header('Refresh: 0 url='.$url);
		echo '<html><head>';
		if (strlen($msg))
			echo CMS::script("alert('".js_enc($msg)."');".
		(strlen($url) ? "document.location.href='$url';" :
			'history.go(-1);'));
		?></head><body><noscript><h2><?php echo $msg; ?></h2><a href="<?php echo xhtml_safe($url) ?>">Redirect to <?php echo xhtml_safe($url);?></a></noscript></body></html><?php	
	}

	function Redir( $url = '', $msg='' ) {
		// (1) check that headers have not been sent
		$file = $line = null;
		if (headers_sent($file, $line))
			trigger_error('Could not use CMS::Redir(), output started in file '.basename($file).' at line '.$line);
		// (2) in case of message, jump to plain javascript redirect (don't check URL length)
		if (strlen($msg))
			CMSResponse::FullRedir($url, $msg);
		else {
		// (3) no message, get the URL since if it is 0-length too
			if (!strlen($url)) {
				$url = CMSRequest::Referer();
				// if the referer is not available, go back using the history
				if (!isset($url)) {
					CMSResponse::FullRedir('', $msg);
					CMSResponse::SafeExit();
					return;
				}
			}
			// 307 not used because causes Firefox alerts
//			header('HTTP/1.1 307 Temporary Redirect');
//			header('Status: 307 Temporary Redirect');
			header('Location: '.$url);
		}
		CMSResponse::SafeExit();
	}
	
	function ResponseRedir($qs) {
		CMSResponse::Redir( CMSRequest::ScriptName().'?'.$qs );
	}

	function SelfRedir($qs = '') {
		if (strlen($qs))
			$qs = '&'.$qs;
		CMSResponse::ResponseRedir(CMSResponse::OptionUrl().$qs);
	}
	
	//TODO: move all to CMSRequest
	function FrontendOptionUrl() {
		return 'option='.rawurlencode($GLOBALS['option']).'&Itemid='.$GLOBALS['Itemid'];
	}

	function BackendOptionUrl() {
		return 'com_option='.rawurlencode($GLOBALS['com_option']);
	}
	
	function OptionUrl() {
		if (defined('_VALID_ADMIN'))
			return CMSResponse::BackendOptionUrl();
		return CMSResponse::FrontendOptionUrl();
	}
	
	function BaseUrl() {
		return CMSRequest::ScriptName().'?'.CMSResponse::OptionUrl();
	}
	
	function NoCache() {
		// some headers to force update
		header("Expires: Mon, 28 May 2003 05:00:00 GMT"); // date in the past
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
		header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
		header("Cache-Control: pre-check=0", false);
		header("Pragma: no-cache"); // HTTP/1.0
	}

}

// requests go before responses
class CMSRequest {

	function URI() {
		if (isset($_SERVER['REQUEST_URI']))
			return	$_SERVER['REQUEST_URI'];
		if (isset($_SERVER['QUERY_STRING']))
			$qs = '?'.$_SERVER['QUERY_STRING'];
		else $qs = '';
		return CMSRequest::ScriptName().$qs;
	}
	
	function Referer() {
		if (isset($_SERVER['HTTP_REFERER']))
			return $_SERVER['HTTP_REFERER'];
		return null;
	}

	function Querystring() {
		if (isset($_SERVER['QUERY_STRING']))
			return $_SERVER['QUERY_STRING'];
		// not sure that this branch is useful at all (!QUERYSTRING => ?REQUEST_URI)
		if (isset($_SERVER['REQUEST_URI'])) {
			$s = $_SERVER['REQUEST_URI'];
			$sn=CMSRequest::ScriptName();
			$p=strpos($s, $sn);
			$p=strpos($s,'?',$p+strlen($sn));
			if ($p!==false)
				return substr($s,$p+1);
		}
		return '';
	}
	
	function UserAgent() {
		if (isset($_SERVER['HTTP_USER_AGENT']))
			return $_SERVER['HTTP_USER_AGENT'];
		return '';
	}
	
	function Method() {
		return $_SERVER['REQUEST_METHOD'];
	}
	
	function ScriptDirectory() {
		$_cms_req_sv = array('SCRIPT_FILENAME', 'SCRIPT_NAME', 'PHP_SELF');
		// try to get the current directory path
		foreach($_cms_req_sv as $var) {
			if (isset($_SERVER[$var])) {
				$s = dirname($_SERVER[$var]);
				if ($GLOBALS['d__windows'])
					$s = str_replace('\\', '/', $s);
				$p = strrpos($s, '/');
				return substr($s, $p+1);
			}
		}
		return null;
	}
	
	function ScriptName() {
		$_cms_req_sv = array('SCRIPT_FILENAME', 'SCRIPT_NAME', 'PHP_SELF');
		// try to get the current base path
		foreach($_cms_req_sv as $var) {
			if (isset($_SERVER[$var]))
				return basename($_SERVER[$var]);
		}
		return null;
	}

}

?>
