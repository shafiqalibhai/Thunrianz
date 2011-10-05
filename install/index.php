<?php
## Lanius CMS EZInstall script
#
# this is the main script, loads each subscript

define('_VALID', 1);

define('_VALID_ADMIN', 1);

// this is the default write mode used to install Lanius CMS
global $d_setmode;
$d_setmode = '';

include '../version.php';

function mt_float() {
   $a = explode(' ', microtime());
   return ((float)$a[0] + (float)$a[1]);
}

$page_start_time = mt_float();

// load internationalization settings
require $d_root.'classes/user.php';
$GLOBALS['d_deflang'] = 'en';
$my = new CMSUser();
$my->lang = $my->recognize_lid();
//$my->gid = 5;

// include Lanius CMS normal language files
include $d_root.'lang/'.$my->lang.'/common.php';

// include install language files
$path = $d_root.'lang/'.$my->lang.'/install/install.php';
include $path;

require $d_root.'admin/includes/admin_functions.php';

include $d_root.'includes/functions.php';

include $d_root.'includes/user_functions.php';

include $d_root.'includes/errtrace.php';

$d_subpath = '../';

if (file_exists($d_root.$d_private.'config.php')) {
	$d_title = _INSTALL_INSTALLATION;
	include $d_root.'includes/servererror.php';
	service_msg(_REINSTALLATION, _REINSTALL_MSG, sprintf(_REINSTALL_ADVICE, '<strong>'.fix_root_path($d_root).$d_private.'config.php</strong>'), 'stop');
	exit;
}

if (ini_get('memory_limit')!='64M') { // the php.ini was not read
	$d_iniset = (@ini_set('memory_limit', '64M')!==false);
} else $d_iniset = false; // not necessary

if ($d_iniset) {	// attempt to manually set the PHP ini custom settings
	require $d_root.'includes/iniset.php';
}

require $d_root.'admin/classes/fs.php';
$fs = new FS(true);

// hack!
include $d_root.'classes/drabots.php';
global $_DRABOTS;

// cannot have custom session handling - hence drabots are disabled
$_DRABOTS = new DrabotHandler(false);
include $d_root.'includes/session.php';

header("Content-Type: text/html; charset=\"utf-8\"");
header('Content-Language: '.$my->lang);

$lids = explode("\n", _LOCALE);
foreach($lids as $lid) {
	if (setlocale (LC_ALL,$lid))
		break;
}

// if $cfg_template exists go straight to stage 3
$cfg_template = $d_root.$d_private.'config.template.php';
if (file_exists($cfg_template) && !isset($_GET['stage'])) $stage = 2;
else $stage = (int)@$_GET['stage'];

/*if ($stage==0) {
	d_setcookie('dktest', 'BD34CFSS', 60*15);
}*/

?><html>
<head>
<title>Lanius CMS - <?php echo _INSTALL_INSTALLATION.' - Stage '.$stage; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/install.css" rel="stylesheet" type="text/css">
</head>
<body>
<p>&nbsp;</p>
<div class="divcell">
<div class="mainheader">
<strong><font color="#cccccc" size="4">Lanius CMS </font></strong><strong>v<?php echo cms_version(true);?><br />
      </strong> <font color="#999999"><?php echo _INSTALL_LICENSE.sprintf(' <a href="'.create_context_help_url('GPL').'" target="_blank" class="menulink" >%s</a>', _INSTALL_GPL); ?></font> </div>
<?php
	include 'stage'.$stage.'.php';
?>
</div>
</body>
</html>