<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Lanius CMS EZInstall script
#
# Stage 1 - PHP configuration and directories writability check

define('__REQUIRED_PHP_MIN_VERSION', '4.3.3');
define('__REQUIRED_PHP_MIN_FILTER', '5.0');
define('__REQUIRED_PHP_MAX_FILTER', '5.1');

$phpv = phpversion();
$is_php_version_ok = (strnatcmp($phpv, __REQUIRED_PHP_MIN_VERSION)>=0);

if ($is_php_version_ok) {
	// check if this is a 5.0<5.1
	if ( (strnatcmp($phpv, __REQUIRED_PHP_MAX_FILTER)<0) && (strnatcmp($phpv, __REQUIRED_PHP_MIN_FILTER)>=0))
		$is_php_version_ok = false;
}

?> 
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr> 
    <td colspan="3" bgcolor="#F7F7F7" class="menuheader"><?php echo _INSTALL_PHP_VERSION_CHECK; ?></td>
  </tr>
  <tr> 
    <td colspan="2"><?php echo sprintf(_INSTALL_PHP_VERSION_CHECK_DESC, 'v'.__REQUIRED_PHP_MIN_VERSION,__REQUIRED_PHP_MIN_FILTER.'.x', __REQUIRED_PHP_MAX_FILTER); ?></td>
    <td align="center"> <strong style="color:<?php if ($is_php_version_ok) echo 'green'; else echo 'red'; ?>"> 
      <?php if ($is_php_version_ok) echo _YES; else echo _NO; ?>
      (PHP v<?php echo phpversion(); ?>)</strong> </td>
  </tr>
  <tr> 
    <td colspan="3" bgcolor="#F7F7F7" class="menuheader"><?php echo "Libraries"; ?></td>
  </tr>
  <?php

// check for availability of specific PHP libraries
$libs = array(
'CTYPE' => 'ctype_alnum',
'PCRE' => 'preg_match',
);

$libs_ok = true;
foreach ($libs as $k => $f) {
	$libs[$k] = function_exists($f);
	if (!$libs[$k]) $libs_ok = false;
?>
  <tr> 
    <td colspan="2"><?php echo $k; ?></td>
    <td align="center"> <strong style="color:<?php if ($libs[$k]) echo 'green'; else echo 'red'; ?>"> 
      <?php if ($libs[$k]) echo _YES; else echo _NO; ?>
      </strong> </td>
  </tr>
  <?php } /*
  <tr><td colspan="2">Cookies</td>
	<td align="center"><?php
		if (isset($_COOKIE['dktest'])) {
			echo _YES;
		} else {
			echo _NO;
			// try to set the cookie again
			$_COOKIE['dktest'] = 'BD34CFSS';
		}
	</td>
  </tr>
*/ ?>
  <tr> 
    <td colspan="3" bgcolor="#F7F7F7" class="menuheader"><?php echo _INSTALL_SERVER;?></td>
  </tr>
  <tr> 
    <td width="35%"><strong><?php echo _INSTALL_DIRECTIVE; ?></strong> </td>
    <td width="15%" align="center"><strong><?php echo _INSTALL_RECCOMENDED; ?></strong> 
    </td>
    <td width="15%" align="center"><strong><?php echo _INSTALL_ACTUAL; ?></strong> 
    </td>
  </tr>
  <?php

$php_recommended_settings = array(
array ('Safe mode','safe_mode',false, 'orange', _INSTALL_SAFE_MODE_DISCLAIM),
array ('File uploads','file_uploads', true, 'orange',
	_INSTALL_FILE_UPLOADS_DISCLAIM),
array ('Magic quotes GPC','magic_quotes_gpc', false, 'gray', 
	_INSTALL_MQGPC_DISCLAIM),
array ('Magic quotes Runtime','magic_quotes_runtime',false, 'red', 
	_INSTALL_MQRT_DISCLAIM),
array ('Register globals','register_globals',false, 'orange',
	_INSTALL_RG_DISCLAIM),
array ('Session auto start','session.auto_start',false, 'green', 
	_INSTALL_SAS_DISCLAIM),
);

$msg1 = '<hr><ol type="1">';

foreach ($php_recommended_settings as $phprec) {
?>
  <tr> 
    <td><?php echo $phprec[0]; ?>&nbsp;:</td>
    <td align="center"><?php echo ($phprec[2] ? _ON:_OFF); ?></td>
    <td align="center"><strong> 
      <?php
		$setting = ini_get($phprec[1]);
		if ($setting == $phprec[2]) { ?>
      <font color="green"> 
      <?php  } else {  ?>
      <font color="<?php echo $phprec[3]; ?>"> 
      <?php  $msg1 .= '<li style="color:"'.$phprec[3].'">'.
						sprintf(_INSTALL_SETTING_BAD, '<strong>'.$phprec[1].'</strong>', '<u>'.($setting ? _ON:_OFF).'</u>').'<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;'.
								$phprec[4].'<br /><br /></li>';
			}
        echo ($setting ? _ON:_OFF);
        ?>
      </font></font></strong><font color="green"> </font></td></tr>
  <?php
$msg1.='</ol>';
}
?>
  <tr> 
    <td colspan="3"> <?php echo $msg1;

$error = false;

?></td>
  </tr>
  <tr> 
    <td bgcolor="#F7F7F7" class="menuheader"><strong><?php echo _INSTALL_0_PATH; ?></strong> 
    </td>
    <td bgcolor="#F7F7F7" class="menuheader"><strong><?php echo _INSTALL_1_WRITABILITY_PURPOSE; ?></strong></td>
    <td bgcolor="#F7F7F7" class="menuheader"><strong><?php echo _INSTALL_1_STATUS; ?></strong></td>
  </tr>
  <?php $directories = array('admin', 'classes', 'components', 'docs', 'editor', 'drabots', 'media', 'includes', 'lang', 'modules', 'private', 'templates');
  
  $uf = _INSTALL_1_UPDATE_FEATURE;
  
	$purposes = array($uf."\n"._INSTALL_1_COMPONENTS_INSTALLATION, $uf, $uf."\n"._INSTALL_1_COMPONENTS_INSTALLATION,
					$uf, $uf."\n"._INSTALL_1_DRABOTS_INSTALLATION, $uf, $uf."\n"._INSTALL_1_GALLERY, $uf, $uf."\n"._INSTALL_1_LANGUAGES_INSTALLATION, $uf."\n"._INSTALL_1_MODULES_INSTALLATION, $uf."\n"._INSTALL_1_BACKUPS,
					$uf."\n"._INSTALL_1_TEMPLATES_INSTALLATION);
  
		foreach ($directories as $directory) { ?>
  <tr> 
    <td><?php echo $directory; ?>/</td>
    <td><pre><?php echo current($purposes); ?></pre></td>
    <td><strong><font color="<?php
                                if(is__writable($d_root.$directory.'/' ))
									echo 'green">'._INSTALL_0_WRITE;
                                else {
									if ($fs->chmod_all($d_root.$directory, 0775))
										echo  'lime">'._INSTALL_1_CHANGED;
									else {
										echo  'red">'._INSTALL_0_NWRITE;
										$error = true;
									}
                                }
                                ?></font></strong>
    </td>
  </tr><?php next($purposes); } ?>
  <tr><td colspan="3">&nbsp;</td></font></strong></tr>
  <tr> 
    <td colspan="3"> 
      <?php
	$can_continue = ($is_php_version_ok && $libs_ok);
	if (!$can_continue) { ?>
      <h2><?php echo INSTALL_1_PHP_CONF; ?></h2>
      <?php echo INSTALL_1_REQ_NOT_MET; ?>
      <?php
	}
	
	if ($error) {
		include $d_root.'admin/includes/permelev.php';
		if (!isset($write_folder))
			$write_folder = find_wf($d_root);
		if ($write_folder!==false)
			echo sprintf(_INSTALL_PERMELEV_FTP, '<strong>'.$write_folder.'</strong>');
		echo '<h2>'.'Writability issues'.'</h2>';
		echo text_to_html(sprintf(_INSTALL_1_ERROR,
				'<a target="_blank" href="'.create_context_help_url('Installation/Stage_1').'">'.
				sprintf('%s - %s %d', _INSTALL_INSTALLATION, _INSTALL_STAGE, 1).'</a>')).'<hr />';
		$cont_label = _INSTALL_READ_ONLY_CONTINUE;
	} else
		$cont_label = _INSTALL_CONTINUE;
		
	if ($can_continue) { ?>
      <div align="right"><a href="index.php?stage=2" class="menulink"><?php echo $cont_label ?></a></div>
      <?php } else { ?>
      <div align="center" style="font-weight:bold; color:red"><?php echo _INSTALL_1_MIN_REQ_NOT_MET; ?></div>
      <?php } ?>
    </td>
  </tr>
</table>
