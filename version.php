<?php

        $d__revision = 1668;
	$d_version = '0.5.2';

## Version information
# @author legolas558
# @see private/config.php
# @see includes/header.php
#
# Version information and basic environment path setting,
# this file is included before anything else and sets up
# \var $d_root for the subsequent scripts inclusion

	$d_root = str_replace('\\','/', dirname(__FILE__)).'/';
	if (!isset($d_private) || isset($_REQUEST['d_private'])) {
		$d_private = 'private/';
		$d_subpath = '';
	}

	function version_info() {
		global $d_private, $d__revision;
		global $d_version, $d_db, $d_subpath;
		if (is_file($d_private.'config.php')) {
			include $d_private.'config.php';
		}
		$version = '<a target="_blank" href="http://laniuscms.svn.sourceforge.net/viewvc/laniuscms/core/trunk/?pathrev='.
						$d__revision.'">v'.$d_version.' r'.$d__revision.'</a>';
		//php_uname() may not work on some configurations
		if (!($os = @php_uname('a'))) {
			if (isset($_SERVER['OS']))
				$os = $_SERVER['OS'];
			else $os = '';
		}
		echo "<pre>\n";
		echo gmstrftime('%Y/%m/%d %H:%M:%S')." GMT\n\n";
		echo '==Server=='."\n";
		echo 'Lanius CMS '.$version."\n";
		echo 'Server: '.
			(isset($_SERVER["SERVER_SOFTWARE"])?$_SERVER["SERVER_SOFTWARE"]:'')."\n";
		echo 'PHP version: '.phpversion()."\n";
//		echo 'Safe mode: '.(@ini_get('safe_mode') ? 'yes':'no')."\n";
		echo 'Protocol: '.
			(isset($_SERVER["SERVER_PROTOCOL"])?$_SERVER["SERVER_PROTOCOL"]:'')."\n";
//		echo 'Host: '.@$_SERVER['HTTP_HOST']."\n";
		echo 'OS: '.$os."\n";
		echo 'Database: '.(isset($d_db)?$d_db:'')."\n";
		if (isset($d_subpath)) {
			echo 'Subsite: '.( ($d_subpath!=='') ? 'yes' : 'no')."\n";
		}
		echo "\n".'==Client=='."\n";
		echo 'User agent: '.(isset($_SERVER["HTTP_USER_AGENT"])?$_SERVER["HTTP_USER_AGENT"]:'')."\n";
		echo 'Accept-charset: '.(isset($_SERVER["HTTP_ACCEPT_CHARSET"])?$_SERVER["HTTP_ACCEPT_CHARSET"]:'')."\n";
		echo 'Accept-language: '.(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])?$_SERVER["HTTP_ACCEPT_LANGUAGE"]:'')."\n";
		global $my;
		echo 'User lang: '.(isset($my->lang)?$my->lang:'')."\n";
		echo '</pre>';
	}
	
	if (!defined('_VALID')) {
		// comment the following line to always show version informations
		header('Status: 403 Forbidden');die;
		version_info();
	}
	
	// the location of the Lanius CMS server is hard-coded
	$d__server = 'http://www.laniuscms.org/';
?>
