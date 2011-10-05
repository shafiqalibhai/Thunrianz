<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## CAPTCHA Security Images plugin
## @author legolas558
## @version 0.1
##
## Provides integration of Captcha Security Images by S.Jarvis
## directly into Lanius CMS

$_DRABOTS->registerFunction( 'OnCaptchaRender', 'csi_render' );
$_DRABOTS->registerFunction( 'OnCaptchaGenerate', 'csi_generate' );
$_DRABOTS->registerFunction( 'OnCaptchaJSFailCondition', 'csi_js_fail_condition' );
$_DRABOTS->registerFunction( 'OnCaptchaVerify', 'csi_verify' );

function csi_generate($prefix = '') {
	$sid = 'captcha';
	$id = 'code';
	if (strlen($prefix)) {
		$id = $prefix.'_'.$id;
		$sid = $prefix.'_'.$sid;
	}
	global $d_root, $d_uid;
	// set proper headers
	header('Content-Type: image/png');
	CMSResponse::NoCache();

	include $d_root.'classes/captcha/captcha.php';
	
	$code = generateCode(5);
	
	$_SESSION[$d_uid.'-'.$sid] = md5($code);
	
	$captcha = new Captcha($code, $d_root.'classes/captcha/davis.ttf', 100, 40, 3, 8);
	
	// generation successful
	return true;
}

function _csi_render_captcha_img($ccode, $prefix = '') {
	$sid = 'captcha';
	$id = 'code';
	if (strlen($prefix)) {
		$id = $prefix.'_'.$id;
		$sid = $prefix.'_'.$sid;
	}
	out_session($sid.'_page', CMSRequest::Querystring());
	if (function_exists('gd_info')) {
		$gdi = gd_info();
		if ($gdi['FreeType Support'] && $gdi['PNG Support']) {
			global $d, $d_website;
			$d->add_unique_js('csi_reload_captcha', '
			function csi_reload_captcha(cid) {
				var im=document.getElementById(cid);
				im.src = "'.$d_website.'index2.php?option=service&service=captcha&pfx='.$prefix.'&no_html=1&rand=" + Math.round(Math.random() * 999999);
			}');
			$captcha_rid = $sid.'_pic'.mt_rand();
?><img id="<?php echo $captcha_rid; ?>" src="index2.php?option=service&amp;service=captcha&amp;pfx=<?php echo $prefix; ?>&amp;no_html=1" vspace="5" class="dk_inputbox" border="1" alt="" /><br />
<input type="button" class="dk_button" value="<?php echo _RELOAD_CAPTCHA; ?>" onclick="csi_reload_captcha('<?php echo $captcha_rid; ?>')" /><?php
		return;
		}
	} // textual captcha otherwise
	?><input type="text" readonly="readonly" style="text-align:center; width:100px; cursor:default; font-size: 24px; font-weight:bold; border: 0px" value="<?php echo $ccode; ?>" /><?php
}

# Returns the captcha <img> tag or a textual replacement for OSes where GD is not available
# integrated with S.Jarvins Captcha class
function csi_render($prefix = '') {
	$sid = 'captcha';
	$id = 'code';
	if (strlen($prefix)) {
		$id = $prefix.'_'.$id;
		$sid = $prefix.'_'.$sid;
	}
	global $captcha, $d_subpath,$d_uid, $d_root;
	
	include_once $d_root.'classes/captcha/captcha.php';

	// will forcefully change the CAPTCHA each time the page is loaded
	$ccode = generateCode(5);
	$_SESSION[$d_uid.'-'.$sid] = md5($ccode);

	?><div class="dk_content"><label for="<?php echo $id; ?>"><?php echo _CAPTCHA_VALID_CODE; ?></label></div>
	<div class="dk_content"><?php _csi_render_captcha_img($ccode, $prefix); ?></div>
	<div class="dk_content"><input type="text" name="<?php echo $id; ?>" id="<?php echo $id; ?>" class="dk_inputbox" maxlength="10" /></div><?php
	return true;
}

// return the failure condition for client-side javascript check of form data
function csi_js_fail_condition($fo, $prefix = '') {
	$id = 'code';
	if (strlen($prefix))
		$id = $prefix.'_'.$id;
	// $id is our captcha field
	return '!'.$fo.'.'.$id.'.value.length';
}

function csi_verify($prefix = '') {
	$sid = 'captcha';
	$id = 'code';
	if (strlen($prefix)) {
		$id = $prefix.'_'.$id;
		$sid = $prefix.'_'.$sid;
	}
	global $d_uid;
	if (!isset($_SESSION[$d_uid.'-'.$sid]) || !isset($_POST[$id]))
		return false;
	$rv = ($_SESSION[$d_uid.'-'.$sid] === md5(raw_strtoupper($_POST[$id])));
	unset($_SESSION[$d_uid.'-'.$sid]);
	return $rv;
}

?>
