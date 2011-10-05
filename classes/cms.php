<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

define('__SCRIPT_TAG', '<script type="text/javascript" language="javascript"');

global $d__max_description, $d__max_keywords,$d__module_positions;
$d__max_description = $d__max_keywords = 250;

class CMSConfig {

	/* public */ var $configArr = array();
	var $_changed = false;
	
	var $_src;
	// used for informative purposes only
	//var $name;
	
	## $path must point to a valid CMS private directory
	//L: configuration parameters should possibly be moved into database (if convenient)
	function CMSConfig($path) {
		$this->_src = $path;
	}
	
	// can fail if file does not exist, can happen for subsites
	function _getConfig() {
		if (!is_readable($this->_src.'config.php'))
			return false;
		$cfg = file_get_contents($this->_src.'config.php');

		preg_match_all('/^\\$([a-z_]+)=(.*?);\s*$/m', $cfg, $m); $cfg = null;
	
		foreach ($m[1] as $var) {
			$this->configArr[$var] = str_decode(substr(current($m[2]), 1, -1));
			next($m[2]);
		}
		return true;
	}

	// can return null when variable was not set
	function getVar($var) {
		if (!count($this->configArr)) {
			if (!$this->_getConfig())
				return null;
		}
		return $this->configArr[$var];
	}
	
	function getVarNames() {
		if (!count($this->configArr)) {
			if (!$this->_getConfig())
				return null;
		}
		$n = array();
		foreach(array_keys($this->configArr) as $v) {
			$n[] = substr($v, 2);
		}
		return $n;
	}

	function setVar($var,$val) {
		if (!count($this->configArr))
			$this->_getConfig();
		if (isset($this->configArr[$var])) {
			if ($this->configArr[$var]!=$val)
				$this->_changed = true;
		} else
			$this->_changed = true;
		$this->configArr[$var]=$val;
	}

	//L: configuration should be stored in an array and (if feasible) in the database
	function Save($p = null) {
		if (isset($p))
			$this->_src = $p;
		else {
			if (!$this->_changed)
				return true;
		}
		global $d_root;
		include_once $d_root.'admin/classes/fs.php';
		$fs = new FS();
		$cf = $fs->write_open($this->_src.'config.php');
		fputs($cf,"<"."?php\n// Lanius CMS v".cms_version(true)." configuration file\n");
		foreach($this->configArr as $var => $val)	{
			fwrite($cf,'$'.$var."=\"".str_encode($val)."\";\n");
		}
		fputs($cf,'?'.'>');
		$fs->write_close($cf, $this->_src);
		return true;
	}

}

class CMSCore {


function DateFormat($ts) {
	return lc_strftime(_DATE_FORMAT_LC, $ts);
}

function CurrentDate() {
	global $time;
	return $this->DateFormat($time);
}

	var $_log_recurse = false;
	
	function LogTimestamp() {
		return gmstrftime('%Y/%m/%d %H:%M:%S', $GLOBALS['time']).' GMT';
	}
	
	function log($priority, $message) {
		if ($this->_log_recurse)
			return false;
		$this->_log_recurse = true;
		global $_DRABOTS;
		$_DRABOTS->loadCoreBotGroup('logger');
		$results = $_DRABOTS->trigger('OnLog', array($priority, $message));
		$rv = null;
		foreach($results as $result) {
			if (isset($result)) {
				if (!isset($rv))
					$rv = $result;
				else
					$rv &= $result;
			}
		}
/*			case 3: // email based log (may be slow, and crash too)
				global $d_root;
				include_once $d_root.'classes/gelomail.php';
				$bcc = GeloMail::admin_emails();
				if (count($bcc)) {
					global $my;
					$m = new GeloMail();
					if ($my->is_admin())
						$main_email = $my->email;
					else
						$main_email = array_shift($bcc);
					$m->bcc = $bcc;
					$rv = $m->Send($main_email, 'Lanius CMS log message', 'Message priority: '.$priority."\n\n".$message."\n\n--\nThis is an auto-generated message, please do not answer");
				} else $rv = false;
			break;	*/
		$this->_log_recurse = false;
		return $rv;
	}

/* const */ function Encoding() {
		return 'utf-8';
	}


}

class CMS	extends CMSCore {
//var $template=null;

var $cfg;

/* public */
var $content;
var $title;		// item title
var $keywords; 	// item keywords
var $desc; 		// item description
var $additional_head;
var $pre_body;
var $pathway;

function CMS($dummy = false) {
	if (!$dummy) {
		// read&set the custom cookie template - if any
		$this->setCookieTemplate();
		$this->title = $GLOBALS['d_title'];
		$this->keywords = $GLOBALS['d_keywords'];
		$this->desc = $GLOBALS['d_desc'];
		$this->additional_head = '';
		global $d_root, $d_private;
		$this->cfg = new CMSConfig($d_root.$d_private);
	}
}

function ValidTemplate($template) {
	if (empty($template)) return false;
	global $d_root;
	$template_dir=$d_root.'templates/';
	return file_exists($template_dir.$template.'/index.php' );
}

function setTemplate($template) {
	global $d_template;
	if (d_hascookie('user_template'))
		d_setcookie('user_template', $template, 60*10);
	$d_template = $template;
}

function _setRandomTemplate() {
	global $d_root;
	$templates = read_dir($d_root.'templates/', 'dir');
	if (!empty($templates)) {	// if cookies not enabled generate the same random template for the same user
		$sum=0;
		$sid = session_id();
		$l = strlen($sid);
		for($i=0;$i<$l;$i++)
			$sum += ord($sid{$i});
		mt_srand($sum);
		$selected = $templates[mt_rand(0, count($templates)-1)];
		$this->setTemplate($selected);
	}
}

// in case of custom cookie for template
// @private
function setCookieTemplate() {
	global $d_root, $d_template;

	// process a different template in cookies
	$ctemplate = in_cookie('user_template');
	if (!isset($ctemplate)) {
		if (!$this->ValidTemplate($d_template)) { // fix the config.php file when an invalid template is default
			$this->_setRandomTemplate();
//			$this->cfg->setVar('d_template', $d_template);
//			$this->cfg->Save();
		}
	} else {
		$ctemplate = path_safe($ctemplate);
		if ($this->ValidTemplate($ctemplate))
			$d_template = $ctemplate;
	}
	$this->setTemplate($d_template);
}

function add_title($title) {
	$this->title = $title." - ".$this->title;
}

// complete meta keywords and description tags
// checks limits without caring about previously cached description/keywords (on purpose)
function add_meta($desc, $keywords = null) {
	if (strlen($desc)) {
		global $d__max_description, $d__max_keywords;

		if (strlen($this->desc))
			$this->desc.="\n".$desc;
		else
			$this->desc = $desc;
		// if keywords were not supplied, try to get them from the supplied description
		if (!isset($keywords)) $keywords = content_keywords($desc);
	}
	if (isset($keywords)) {
		if (!strlen($this->keywords))
			$this->keywords = $keywords;
		else
			$this->keywords.=','.$keywords;
	}
}

/*var $_loaded_css = array();

function _CSS_index($css_file) {
	$this->_loaded_css[] = $css_file;
}*/

function ShowHead() {
	global $conn, $d_root;
	global $d_subpath,$d_template,$d_title, $d_website;

	?><title><?php echo $this->title; ?></title>
	<?php $this->ShowMainHead(); ?>
	<meta name="keywords" content="<?php echo $this->keywords; ?>" />
	<meta name="description" content="<?php echo $this->desc; ?>" />
	<?php //LEGACY - deprecated
	if (!file_exists($d_root.'templates/'.$d_template.'/template.style.css')) { ?>
	<link href="<?php echo $d_subpath; ?>templates/<?php echo $d_template; ?>/css/template_css.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo $d_subpath; ?>templates/<?php echo $d_template; ?>/drake.css" rel="stylesheet" type="text/css" />
	<?php
//		$this->_CSS_index('templates/'.$d_template.'/css/template_css.css');
//		$this->_CSS_index('templates/'.$d_template.'/css/drake.css');
	} else { ?>
	<link href="<?php echo $d_subpath; ?>templates/<?php echo $d_template; ?>/template.style.css" rel="stylesheet" type="text/css" />
	<?php
//		$this->_CSS_index('templates/'.$d_template.'/template.style.css');
	}

	// process additional <link /> tags
	global $my;
	$rows = $conn->SelectArray('#__links', 'rel,type,title,href',' WHERE access<='.$my->gid);

	foreach($rows as $row) {
		echo '<link rel="'.xhtml_safe($row['rel']).'" type="'.xhtml_safe($row['type']).
			'" title="'.xhtml_safe($row['title']).'" href="'.xhtml_safe($row['href'])."\" />\n";
	}

	$this->additional_head = null;
}

// @private
function ShowMainHead() { ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo raw_strtoupper($this->Encoding()); ?>" />
<meta name="generator" content="Lanius CMS v<?php echo cms_version(); ?>" />
<link rel="shortcut icon" href="media/favicon.png" type="image/png" />
<?php
	
	global $_DRABOTS;
	$_DRABOTS->trigger('onGenerateHead');
	
	// outputs the additional javascript, CSS and other <head /> content
	echo $this->additional_head;
	
//	if (strlen($this->_js_onload))
//		echo $this->script('function dk_js_onload() {'."\n".$this->_js_onload."\n}");

}

// @private
function CatchComponent() {
	$this->content = ob_get_clean();
}

// @private
function DumpComponent() {
	echo $this->content;
	$this->content = null;
}

#add raw lines into the <head /> tag
function add_head($raw_head, $not_necessary = false) {
	if (!isset($this->additional_head)) {
		if ($not_necessary)
			echo $raw_head;
		else
			trigger_error('Cannot modify the HEAD tag outside component rendering');
		return;
	}
	$this->additional_head.=$raw_head;
}

# add a css hyperlink for external source
function add_css($relpath, $media = 'all') {
	global $d_subpath;
	$this->add_head('<link href="'.$d_subpath.$relpath.'" rel="stylesheet" type="text/css" media="'.$media.'" />'."\n");
//	$this->_CSS_index($relpath);
}

// creates a popup window with given parameters
function popup_js($url, $width, $height, $scrollbars = 'no') {
	$this->add_unique_js('lcms_popup',
	'function lcms_popup(url, w, h, sb) {
		var dt = new Date();
		window.open(url, "lcms_popup"+dt.getTime(), "status=no,toolbar=no,scrollbars=" + sb + ",titlebar=no,menubar=no,resizable=yes,width="+w+",height="+h+",directories=no,location=no");
	}');
	return xhtml_safe('lcms_popup('.$url.','.$width.','.$height.',\''.$scrollbars.'\');');
}

// creates a popup window with given parameters and sets an 'opener_window'
// property to a custom javascript object
function popup_js_ref($url, $w, $h, $scrollbars = 'no', $window = 'window') {
	global $d;
	$d->add_unique_js('lcms_popup_ref',
	'function lcms_popup_ref(url, w, h, sb, parent_win) {
		var dt = new Date();
		var popup = parent_win.open(url, "lcms_popup"+dt.getTime(), "status=no,toolbar=no,scrollbars=" + sb + ",titlebar=no,menubar=no,resizable=yes,width="+w+",height="+h+",directories=no,location=no");
		popup.opener.opener_window = parent_win;
	}');
	return xhtml_safe('lcms_popup_ref('.$url.','.$w.','.$h.',\''.$scrollbars.'\', '.$window.');');
}

// alternate url used for example side by side with popup triggering code
function alternate_url($url, $label) {
	if (strpos($url, '&amp;') !== false)
		trigger_error("Alternate URL is double-encoded");
	return '<noscript><a rel="alternate" href="'.xhtml_safe($url).'">'.$label.'</a></noscript>';
}

//var $_js_onload = '';

function IsAgentIE() {
	return preg_match('/msie/i', (string)@$_SERVER['HTTP_USER_AGENT']);
}

# allow to attach an onload function
function add_js_onload($js_func) {
	if($this->IsAgentIE())
		$this->add_raw_js('window.attachEvent("onload",'.$js_func.');'."\n");
	else
		$this->add_raw_js('window.addEventListener("load",'.$js_func.',false);'."\n");
//	$this->_js_onload .= $js_func."();\n";
}

function place_pwd_pb($linked_pwd_id, $div_id, $label) {
	$this->add_js('includes/js/jquery.js');
	$this->add_js('includes/js/progressbar.js');
	$this->add_js('includes/js/passwordquality.js');
	$this->add_css('includes/css/progressbar.css');
	$this->add_raw_js(
'function _init_pwd_box() {
	initQualityMeter("'.$linked_pwd_id.'", "'.$div_id.'");
}
pb_addEvent(window, "load", _init_pwd_box);
');
	return $label.'<div id="'.$div_id.'" class="progressbar">&nbsp;</div>';
//	return '<div id="'.$div_id.'" class="progressbar"><div style="width: 0%;"><p>'.$label.'</p></div></div>';
}

/*
function JSOnLoad() {
	if (strlen($this->_js_onload)) {
		echo ' onload="dk_js_onload()"';
	}
}
*/

function script($s) {
	return __SCRIPT_TAG.">\n/* <![CDATA[ */\n".
$s."\n/* ]]> */\n</script>\n";
}

var $_once_js = array();
var $_once_css = array();

# include the javascript file once
function add_js_once($relpath) {
	if (in_array($relpath, $this->_once_js))
		return;
	$this->_once_js[] = $relpath;
	$this->add_js($relpath);
}

# include the css file once
function add_css_once($relpath, $cie = false) {
	if (in_array($relpath, $this->_once_css))
		return;
	$this->_once_css[] = $relpath;
	// Check If Exists
	if ($cie && !file_exists($GLOBALS['d_root'].$relpath))
		return;
	$this->add_css($relpath);
}

# add an external javascript in head
function add_js($relpath) {
	global $d_subpath;
	$this->add_head(__SCRIPT_TAG.' src="'.$d_subpath.$relpath.
	'"></script>'."\n", true);
}

function add_raw_css($css) {
	$this->add_head('<style type="text/css">'."\n$css\n</style>\n", true);
}

# add an external javascript in body
function add_body_js($relpath) {
	if (!defined('_VALID_ADMIN'))
		trigger_error('Method not supported in frontend');
	global $d_subpath;
	$this->pre_body.=__SCRIPT_TAG.' src="'.$d_subpath.$relpath.
	'"></script>'."\n";
}

# add an external javascript in body
function add_raw_body_js($js) {
	if (!defined('_VALID_ADMIN'))
		trigger_error('Method not supported in frontend');
	$this->pre_body.=$this->script($js);
}

# add a raw javascript snippet
function add_raw_js($raw_js) {
	$this->add_head($this->script($raw_js), true);
}

var $_js_uid = array();

function add_unique_js($uid, $raw_js) {
	if (isset($this->_js_uid[$uid])) return false;
	$this->_js_uid[$uid] = true;
	$this->add_raw_js($raw_js);
	return true;
}

function MainBody() {
	echo $this->content;
}

// output the generated pathway
function PathWay() {
	echo $this->pathway;
//	$this->pathway = null;		// pathway is not cleaned up because you might want to use ::PathWay() more than once
}

//TODO: the menu instance matters *A LOT* here!!
function GetComponentParamsRaw($option, $id = 0) {
	global $conn, $access_sql;
	if (!$id) {
		$row = $conn->SelectRow('#__components', 'id', ' WHERE option_link=\'com_'.sql_encode($option).'\'');
		if (!count($row)) 	// component does not exist
			return null;
		$id = $row['id'];
	}
	$row = $conn->SelectRow('#__menu', 'params', ' WHERE componentid='.$id.' '.$access_sql);
	if (!count($row)) 	// menu instance of component does not exist or is not accessible
		return null;
	return new param_class($row['params']);
}

// will cause termination in case of NotFound or Unauthorized
function GetComponentParams($option) {
	global $conn, $Itemid, $access_sql;
	if ($Itemid!==0) {	// check the supplied Itemid
		$row = $conn->SelectRow('#__menu', 'id,params,componentid', ' WHERE id = '.$Itemid.' '.$access_sql);
		// the Itemid was not valid
		if (!count($row)) $row = null; else {
			if ($row['componentid']) {	// check if the component id is relative to the selected $option
				$row2 = $conn->SelectRow('#__components', 'id', ' WHERE id ='.$row['componentid'].' AND option_link=\'com_'.sql_encode($option).'\'');
				if (!count($row2))
					// the supplied Itemid is not valid, but anyway let's fallback to Itemid redirection
					$row = null;
			}
		}
	} else $row = null;
	if (!isset($row)) {	// find a valid component instance
		$row = $conn->SelectRow('#__components', 'id', ' WHERE option_link=\'com_'.sql_encode($option).'\'');
		if (!count($row)) {	// component does not exist
			// Unauthorized because this might be a crafted ID
			CMSResponse::Unauthorized();
			exit();
//			return null;
		}
		$row = $conn->SelectRow('#__menu', 'id', ' WHERE componentid='.$row['id']
								.' '.$access_sql);
		if (!count($row)) {	// menu instance of component does not exist or is not accessible
			CMSResponse::Unauthorized("<br />"._INSTANCE_ERROR);
			exit();
//			return null;
		}
		$Itemid = $row['id'];
		// issue a full redirect (301 Moved)
		$qs = @CMSRequest::Querystring();
		if (empty($_POST) && ($qs!=='')) {
			parse_str($qs, $data);
			$has_iid = isset($data['Itemid']);
			if ($has_iid) {
				$data['Itemid'] = $Itemid;
				$qs = '';
			} else {
				$tmp = key($data);
				$qs = '&'.$tmp.'='.array_shift($data).'&Itemid='.$Itemid;	
//				$qs = '&Itemid='.$Itemid;
			}

			end($data);
			$tmp = key($data);
			if ($tmp[0]=='-') {
				//DEBUG code?
				if (!is_array($tmp))
					$pqs = '&'.rawurlencode($tmp);
				else
					$this->log(1, 'CMS::GetComponentParams(): $tmp = ('.implode(', ', $tmp).')');
				array_pop($data);
			} else $pqs = '';
			reset($data);
			foreach ($data as $arg => $val) {
				if (!is_array($val))
					$qs .= '&'.$arg.'='.rawurlencode($val);
//				else
//					$this->log(1, 'CMS::GetComponentParams(): ['.$arg.'] = ('.implode(', ', $val).')');
			} $data = null;
			$qs .= $pqs;
		} else { 		// this should not happen...without a querystring we should not have any component instance
			$row = $conn->SelectRow('#__menu', 'params', ' WHERE id='.$Itemid);
			// returns in case of POSTs
			return new param_class($row['params']);
			//$qs = 'Itemid='.$Itemid.'&option='.$option;
		}
		$index = CMSRequest::ScriptName();
		CMSResponse::Move($index.'?'.substr($qs,1));
		exit;
	}
	return new param_class( $row['params'] );
}

function ModulePositions() {
	return $GLOBALS['d__module_positions'];
}

var $_modules = array();

function PreloadModules() {
	global $conn, $access_acl, $Itemid;
	
	foreach(array_keys($this->ModulePositions()) as $pos) {
		$this->_modules[$pos] = array();
	}
	
	global $d_template;
	foreach($conn->SelectArray('#__modules', 'id,title,message,module,ordering,position,showtitle,showon,params,instance',
			" WHERE $access_acl ORDER BY ordering ASC") as $row) {
		if (
			($row['showon']==='') ||
			(strpos($row['showon'],'_'.$Itemid.'_')!==false) ||
			(strpos($row['showon'],'_0_')!==false)
			) {
		// load the CSS file if exists
		$this->add_css_once('modules/css/'.substr($row['module'], 4).'.style.css', true);
		// load the custom CSS file in template (override) if exists
		$this->add_css_once('templates/'.$d_template.'/modules/'.substr($row['module'], 4).'.style.css', true);

		$this->_modules[$row['position']][] = $row;
		}
	}

}

function PreloadPosition($position) {
	global $conn, $my,$Itemid,$access_sql;
	
	$rsa = $conn->SelectArray('#__modules', 'id,title,message,module,ordering,position,showtitle,showon,params,instance',
			" WHERE position='$position' $access_sql ORDER BY ordering ASC");
	$result = array();
	foreach($rsa as $row) {
		if (
			($row['showon']==='') ||
			(strpos($row['showon'],'_'.$Itemid.'_')!==false) ||
			(strpos($row['showon'],'_0_')!==false)
			)
				$result[] = $row;
	}
	return $result;
}

function CountModules($position) {
//	if (!isset($this->_modules[$position]))
//		$this->_modules[$position] = $this->PreloadPosition($position);
	return count($this->_modules[$position]);
}

function InlineModule($name, $custom_options = '') {
	global $conn;
	$row = $conn->SelectRow('#__modules', 'id,title,message,module,ordering,position,showtitle,showon,params', ' WHERE module=\'mod_'.sql_encode($name)."'");
	$this->ShowModule($row, new param_class($row['params']), 'inline', $custom_options);
}

function LoadModules( $position, $type = null) {
	global $conn,$Itemid,$access_sql;
	
//	if (!isset($this->_modules[$position]))
//		$this->_modules[$position] = $this->PreloadPosition($position);
	foreach($this->_modules[$position] as $module) {
		$this->ShowModule( $module, new param_class($module['params']), $type );
	}
	$this->_modules[$position] = null;	// free associated memory
}

function ShowModule($module, $params, $_type = null, $custom_options = '') {
	global $Itemid, $my, $d_root;
	
	if (!isset($_type))
		$_type = $GLOBALS['d_type'];
		
	//TODO: if cache active enter the cache-system branch
	
	$custom_class = $params->get('custom_class', '');
	//RFC: what other values for $_type are acceptable? design issue
	if ($_type == 'html') {
		?><div class="dk_module <?php echo 'dk'.$module['module'].' '.$custom_class ?>">
			<?php    if ($module['showtitle']) { ?>
				<div class="dk_header"><h3><?php echo $module['title'];?></h3></div>
			<?php  }    ?>
			<div class="dk_content">
	    <?php
	}
	// check if this is a proper module
	if (strpos($module['module'], 'mod_')===0) {
		require $d_root.'modules/'.$module['module'].'.php';
	} else // if this is a XHTML snippet, then it has no module associated
		echo $module['message'];
	if ($_type == 'html') { ?>
	    </div>
	  </div>
    <?php
	}
}

	function ObfuscateEmail($email) {
		static $dots = array(' [.] ', ' {.} ', ' dot ', ' {dot} ', ' d0t ', ' DOT ');
		static $ats = array(' AT ', ' A.T. ', ' at ', ' :at: ', ' {at} ', ' [at] ');
		$obf = array($dots[mt_rand(0, count($dots)-1)], $ats[mt_rand(0, count($ats)-1)]);
		$email = str_replace(array(".","@"),$obf,$email);
		return $email;
	}
	
	function CloakEmail($email) {
		return $this->ComplexEmail($this->ObfuscateEmail($email));
	}

	function ComplexEmail($email) {
		$s = '';
		$c=strlen($email);
		$b=(int)($c/3);
		$c-=$b;
		for($i=$b;$i<$c;++$i) {
			$h = dechex(ord($email[$i]));
			$s .= '&#x'.$h.';';
		}
		return substr_replace($email, $s, $b, $c-$b);
	}
	
	function TextareaEditor($area_name,$area_content,$rows='10',$cols='60',$extra='') {
		return sprintf('<textarea id="%s" name="%s" cols="%d" rows="%d" %s>%s</textarea>', $area_name,
 $area_name, $cols, $rows, $extra, $area_content);
	}
	
	function SimpleEditor($area_name,$area_content,$rows='10',$cols='60',$extra='') {
		return	$this->TextareaEditor($area_name, $area_content, $rows, $cols, $extra).
				$this->ImagesUploadButton();
	}
/*	
	function Set($area_name, $ct) {
		global $htmlareaset;
		return $htmlareaset.'('.$area_name.', '.$ct.');';
	}	*/
	
	function EditorSaveAll() {
		global $_DRABOTS;
		$type = $_DRABOTS->trigger('OnEditorSaveAll');
		$js = '';
		foreach($type as $t) {
			if (isset($t))
				$js .= $t;
		}
		return $js;	
	}
	
//	var $_editor_save_js = '';
	function EditorSaveJSMultiple($editors) {
		$js = $this->EditorSaveAll();
		if (strlen($js))
			return $js;
		foreach($editors as $area_name) {
			$js .= $this->EditorSaveJS($area_name, false);
		}
		return $js;
	}
	
	function EditorSaveJS($area_name, $save_all = true) {
		if ($save_all) {
			$js = $this->EditorSaveAll();
			if (strlen($js))
				return $js;
		}
		global $_DRABOTS;
		$js_events = $_DRABOTS->trigger('OnEditorSaveJS', array($area_name));
		$editor_save_js = '';
		foreach($js_events as $js) {
			if (isset($js))
				$editor_save_js .= $js;
		}
		return $editor_save_js;
	}
	
	var $_editor_setup_done = false;
	
	function SubsitePath() {
		if (strlen($GLOBALS['d_subpath']))
			return CMSRequest::ScriptDirectory().'/';
		return '';
	}
	
	function SitePath() {
		return $GLOBALS['d_root'].$this->SubsitePath();
	}
	
	function SetupAdvancedEditor() {
		if ($this->_editor_setup_done)
			return;
//		$this->add_raw_js('var _d_subsite_path = "'.$this->SubsitePath().'";');
		global $_DRABOTS;
			$_DRABOTS->loadCoreBotGroup('editor');
		$this->_editor_setup_done = true;
	}
	
	function AdvancedEditor($area_name,$area_content,$rows='10',$cols='60',$extra='') {
		$this->SetupAdvancedEditor();
		global $_DRABOTS;
		$editor = $_DRABOTS->trigger('OnEditor', array($area_name, $area_content, $rows, $cols, $extra), -1);
		if (isset($editor[0]))
			return $editor.$this->ImagesUploadButton();
		// if no editor was created, return the simple textarea
		return $this->SimpleEditor($area_name, $area_content, $rows, $cols, $extra);
	}
	
	function ImagesUploadButton() {
		global $d_uid,$d_root;
		$p = $this->GetComponentParamsRaw('fb');
		// there is no filebrowser instance
		if (!isset($p)) return '';
		out_session('fb_ext', $GLOBALS['d_pic_extensions']);
		// all files accepted in URL
		out_session('fb_root_path', 'media/');
		out_session('fb_excluded_dirs', array(
			$d_root.'media/gallery/',
			$d_root.'media/forum/avatars/custom/',
		));
		return '<br /><input type="button" onclick="'.$this->popup_js("'index2.php?option=fb'", 600, 400).'" value="'._CONTENTIMG_BUTTON.'" />';
	}
	
	var $_valid_langs;
	
	function LangLabel($lang) {
		if ($lang === '') return 'Auto';
		$langs = $this->GetActiveLangs();
		if (!isset($langs[$lang]))
			return _NA;
		global $d_root;
		include_once $d_root.'classes/anyxml/anyxml.php';
		$xml = new AnyXML();
		if (!$xml->fromString(file_get_contents($d_root.'lang/'.$lang.'/language.xml')))
			return _NA;
		$e = $xml->getElementByPath('languages/language/name');
		return $e->getValue();
	}
	
	function GetActiveLangs() {
		if (isset($this->_valid_langs))
			return $this->_valid_langs();
		global $d_dlangs, $d_root;
		$tmp=read_dir($d_root.'lang/','dir');
		$langs = array();
		foreach($tmp as $lid) {
			// this language is disabled
			if (strpos($d_dlangs, $lid) !== false)
				continue;
			if (!is_file($d_root.'lang/'.$lid.'/language.xml'))
				continue;
			$langs[] = $lid;
		}
		return $langs;
	}
	
}

?>