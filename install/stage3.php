<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Lanius CMS wizard install script
#
# Stage 5 - write config.php, create database and show results

$rootid = md5($d_root);

global $d_uid;
$d_uid = $_SESSION[$rootid.'-uid'];

// disable dbcheck.php access
$_SESSION[$d_uid.'-installing'] = null;

$_PG = array_keys($GLOBALS);
if ($d__bad_env) {
	foreach($_GET as $var => $val) {
		if (in_array($var, $_PG))
			continue;
		if (!is_array($val))
			$$var = stripslashes($val);
		else
			$$var = $val;
	}
	foreach($_POST as $var => $val) {
		if (in_array($var, $_PG))
			continue;
		if (!is_array($val))
			$$var = stripslashes($val);
		else
			$$var = $val;
	}
} else {
	extract($_GET, EXTR_SKIP);
	extract($_POST, EXTR_SKIP);
}

require $d_root.'install/install_lib.php';

	function exit_ok() {
		echo '<strong><font color="green">'._INSTALL_3_SUCCESS.'</font></strong>';
	}
	
	function exit_fail($color='red') {
		echo  '<strong><font color="'.$color.'">'._INSTALL_5_FAILURE.'</font></strong>';
	}

function rsection_begin($header, $desc, $doing = '' ) {
?>
        <div class="divhdr menuheader">
          <?php echo $header;?></div>
	<?php if ($desc!=='') {?>
<div align="left" class="f8" style="color:#999999"><?php echo $desc;?></div><?php }
        echo $doing;
	flush();
}

function rsection_end() { ?><hr /><?php flush(); }

require $d_root.'includes/dracon.php';

// included here to let get_domain() work
	$d_website = $cwebsite;
	$d_email_from = $cemail_from;
	$d_email_name = $cemail_name;

// set the default settings first
	// $d_uid gives unique identity of the Lanius CMS installation
	// $d_uid is previously calculated for database naming and used for session prefixing
	// $d_uid is exposed in the database name under private/lcms and used for the passwords generation
//	$d_realm = substr(base64_encode(get_domain(true).':'.random_string(4)), 0, -2);
	// $d_rand is exposed in the cookies
	$d_rand = random_string(6);
	$d_docs_server = '';
	$d_resource_deny = '0';
	$def_temp_path = $d_root.$d_private.'temp/';
	include $d_root.'install/tempdir.php';
	$d_temp = sys_get_temp_dir();
	if ($d_temp===false)
		$d_temp = $def_temp_path;
	else {
		$d_temp = str_replace('\\','/', $d_temp);
		if ($d_temp[strlen($d_temp)-1]!='/')
			$d_temp.='/';
		if (!is__writable($d_temp))
			$d_temp = $def_temp_path;
	}

	$d_deflang = 'en';
	$d_offline_msg = 'Website offline for maintenance';
	$d_event = '3';
	
	// set environment flags
	$d_env_flags = 0;
	if (! @ini_get('precision'))
		$d_env_flags &= _LCMS_NO_ini_get;
	if (! @putenv('LCMS_TEST=testvalue'))
		$d_env_flags &= _LCMS_NO_putenv;
	
	if (!($d_env_flags & _LCMS_NO_ini_get)) {
		$d_gzip = (string)((int)@ini_get('zlib.output_compression')==0);
		$d_max_upload_size = convert_bytes(return_bytes(@ini_get('upload_max_filesize')));
	} else {
		$d_gzip = '0';
		$d_max_upload_size = '2097152';
	}
	$d_online = '1';
/*	if (is__writable($d_root.$d_private.'log.php'))
		$d_log = '2';
	else {
		if (@syslog(1, 'Lanius CMS installation syslog() test'))
			$d_log = '1';
		else
			$d_log = '0';
	} */
	
	
	$d_force_text_email = '0';
	$d_email_split = '0';
	$d_email_text = '1';
	$d_email_hashcash = '0';
	$d_http_auth = '0';
	$d_cache = '0';
	$d_cache_debug = '0';
	$d_clear_pw = '0';
//	$d_ldap_auth = '0';
	$d_view_filter = '1';
	$d_sqldebug = '0';
	
// allow direct access to stage 5
if (!isset($cdb)) {
	$cfg_template = $d_root.$d_private.'config.template.php';
	if (!file_exists($cfg_template)) {
		echo $cfg_template.' does not exist';
		//TODO: redirect to stage 1
		exit;
	}
	include $cfg_template;
	// overwrite previous variables with new one
	$d_website = $cwebsite;
	$d_email_from = $cemail_from;
	$d_email_name = $cemail_name;
	$d_title = $ctitle;
} else {
	$d_db = $cdb;
	$d_prefix = strtolower(in('cprefix', __PATH, $_POST, 'dk', 10));
	if ($d_prefix!=='') $d_prefix .= '_';
	$d_dbhost=$cdbhost;
	$d_dbname=$cdbname;
	$d_dbusername=$cdbusername;
	$d_dbpassword=$cdbpassword;
	$d_title = $ctitle;
	// fix Digest auth for PHP4
	if ((strnatcmp(phpversion(), '5.1')<0) && $d_http_auth==2)
		$d_http_auth = 0;
}

/*	if ($d_log==2)
		$logger_sql = 'UPDATE #__drabots SET access=0 WHERE element=\'filelog\'';
	else if ($d_log == 1)
		$logger_sql = 'UPDATE #__drabots SET access=0 WHERE element=\'syslog\'';
	else */$logger_sql = null;

$time = time();

rsection_begin(_INSTALL_5_DATABASE_FOLDER, sprintf(_INSTALL_5_DATABASE_FOLDER_DESC, '<strong>'.$d_private.'</strong>'), sprintf(_INSTALL_5_DATABASE_FOLDER_PROGRESS.'...', '<strong>'.$d_dbname.'</strong>'));

$error = false;
$fdb = (($d_db=='gladius') || (strpos($d_db,'sqlite')===0));

// check if the database directory exists for the chosen database name
//TODO: should allow installation even if the DB directory name cannot be changed!
if (!is_dir($d_root.$d_private.$d_dbname)) {
	if (!$fs->rename($d_root.$d_private.'lcms', $d_root.$d_private.$d_dbname)) {
		echo _INSTALL_3_DB_CREATE_ERR;
		$error = true;
	}
}
if (!$error)
	exit_ok();
else {
	if ($fdb) {
		rsection_begin(_INSTALL_3_EXITUS, '', _INSTALL_5_DATABASE_FOLDER_FAILURE);
		rsection_end();
//		echo '</table>';
		exit();
	}
}
rsection_end();

if ($fdb) {
	rsection_begin(_INSTALL_5_DATABASE_FOLDER_PERMS, sprintf(_INSTALL_5_DATABASE_FOLDER_PERMS_DESC, '<strong>'.$d_root.$d_private.$d_dbname.'</strong>'), _INSTALL_5_DATABASE_FOLDER_PERMS_PROGRESS.'...');
	if (!is__writable($d_root.$d_private.$d_dbname.'/')) {
		exit_fail();
		$error = true;
	} else
		exit_ok();
	rsection_end();
}

if ($error) {
//	echo '</table>';
	exit;
}

// snippet adapted from includes/adodb.php
if ($d_db == 'gladius') {
	global $GLADIUS_DB_ROOT, $d_root;
	$GLADIUS_DB_ROOT = $d_root.$d_private;
	include $d_root.'classes/gladius/gladius.php';
	include $d_root.'classes/gladius/gladius_rs.php';
}

include $d_root.'classes/dbfork.php';

include $d_root.'classes/adodb_lite/adodb.inc.php';

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$conn = new DbFork($d_db);
$conn->Initialize($d_dbhost,$d_dbusername,$d_dbpassword,$d_dbname, $d_prefix, true);
// end of snippet

rsection_begin(_INSTALL_5_DATABASE_CONNECTION, '', _INSTALL_5_DATABASE_CONNECTION_PROGRESS);
$error = false;

    if (!$conn->connected) {
    	echo _INSTALL_3_DB_ERROR.'('.$conn->ErrorMsg().')';
    	$error = true;
    } else
		exit_ok();
rsection_end();

if ($error) {
//	echo '</table>';
	exit;
}

	rsection_begin(_INSTALL_3_DB, '', _INSTALL_3_DB_WRITE.'...');

	$err = install_cms($conn, $d_db);

	if (!$err) {
		//TODO: make it configurable
/*		if (function_exists('date_default_timezone_get'))
			$ctz = date_default_timezone_get();
		else
			$ctz = 'Europe/London'; */
		
		// randomly generates admin user id
		$aid = mt_rand(2,789);
		$conn->EnableIdentity('users');
		$conn->Insert('#__users', 
		'(id,name,username,email,password,registerDate,lastvisitDate,gid,lang,published,timezone,clear_password)',
		sprintf("%u,'%s','%s','%s','%s',%u,%u,5,'%s', 1, '%s', '%s'", $aid, sql_encode($cname), sql_encode($cusername), sql_encode($cemail), md5($cpassword), $time, $time, '', '', $d_clear_pw ? sql_encode($cpassword):''));
		$conn->Insert('#__contacts', '(userid,flags)', $aid.', 3');
		$conn->DisableIdentity('users');
//		if (isset($logger_sql))			$conn->Execute($logger_sql);
	}

	?><strong><font color="<?php if (!$err) echo 'green">'._INSTALL_3_SUCCESS; else echo 'red">'._INSTALL_5_ERRORS; ?></font></strong><?php
	rsection_end();

	if ($err!=0) 
		$result = _INSTALL_5_ERRORS;
	else {
// CONFIG.PHP SECTION
rsection_begin($d_private.'config.php', _INSTALL_3_CONFIG_EXP, _INSTALL_3_CONFIG_WRITE.'...');
$error = false;

$out = sprintf("<?php
\$d_website=\"%s\";
\$d_title=\"%s\";
\$d_online=\"%s\";
\$d_offline_msg=\"%s\";
\$d_db=\"%s\";
\$d_prefix=\"%s\";
\$d_dbhost=\"%s\";
\$d_dbname=\"%s\";
\$d_dbusername=\"%s\";
\$d_dbpassword=\"%s\";
\$d_event=\"%s\";
\$d_error_report=\"1\";
\$d_template=\"waverebirth\";
\$d_atemplate=\"default\";
\$d_keywords=\"\";
\$d_desc=\"\";
\$d_gzip=\"%s\";
\$d_seo=\"150\";
\$d_show_count=\"15\";
\$d_emailpass=\"0\";
\$d_stats=\"0\";
\$d_rand=\"%s\";
\$d_uid=\"%s\";
\$d_deflang=\"%s\";
\$d_max_upload_size=\"%s\";
\$d_docs_server=\"%s\";
\$d_force_text_email=\"%s\";
\$d_email_split=\"%s\";
\$d_email_hashcash=\"%s\";
\$d_temp=\"%s\";
\$d_http_auth=\"%s\";
\$d_iniset=\"%s\";
\$d_cache=\"%s\";
\$d_cache_debug=\"%s\";
\$d_setmode=\"\";
\$d_clear_pw=\"%s\";
\$d_email_text=\"%s\";
\$d_resource_deny=\"%s\";
\$d_email_name=\"%s\";
\$d_email_from=\"%s\";
\$d_view_filter=\"%s\";
\$d_setowner=\"\";
\$d_setgroup=\"\";
\$d_env_flags=\"%d\";
\$d_dlangs=\"\";
\$d_sqldebug=\"%d\";
?>",
//TODO: use CMSConfig instead, or str_encode at least
	str_encode($d_website), str_encode($d_title), $d_online, str_encode($d_offline_msg),
	$d_db,$d_prefix,$d_dbhost,$d_dbname,$d_dbusername,$d_dbpassword,
	$d_event, $d_gzip, $d_rand, $d_uid, $d_deflang, $d_max_upload_size,
	$d_docs_server,
	$d_force_text_email, $d_email_split, $d_email_hashcash,
	str_encode($d_temp),
	$d_http_auth, $d_iniset,
	$d_cache, $d_cache_debug, $d_clear_pw,
	$d_email_text, $d_resource_deny, $d_email_name,
	$d_email_from, $d_view_filter, $d_env_flags,
	$d_sqldebug);
	
	$error = !@file_put_contents($d_root.$d_private."config.php",$out);

	if (!$error)
		exit_ok();		
	else
		echo  '<strong><font color="red">'._INSTALL_3_CONFIG_ERROR.'</font></strong>';
	
	rsection_end();

	if ($error) {
		rsection_begin('Manual config.php', sprintf(_INSTALL_5_CONFIG_CONTENT, '<strong>'.fix_root_path($d_root).$d_private.'config.php</strong>'), ''); ?>
            <textarea name="textarea" cols="40" rows="10"><?php echo $out;?></textarea><?php
		rsection_end();
	} unset($out);

		$result = _INSTALL_5_SUCCESS;
	}
	
	rsection_begin(_INSTALL_3_EXITUS, '', '');
		echo $result;
	rsection_end();
?></table><?php 
	
	if ($err==0) {
			?><div align="center" style="background-color: white"><a href="../index.php" class="menulink"><?php echo _INSTALL_3_FRONT;?></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="../admin.php" class="menulink"><?php echo _INSTALL_3_ADMIN;?></a>&nbsp;&nbsp;<br/>&nbsp;</div>
<?php } ?>
