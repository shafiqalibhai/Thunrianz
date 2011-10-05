<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

switch($task) {
	case 'test_email':
		include $d_root.'install/choose_mailer.php';
		echo '<h2>'._CONFIG_MAILING_SYSTEM.'</h2>';
		echo '<p>'._CONFIG_MAILING_SYSTEM_RESULTS.': ';
		include $d_root.'classes/gelomail.php';
		$m = new GeloMail();
		if ($m->Send($my->email, _TEST_SUBJECT, _TEST_BODY))
			echo _CONFIG_MAILING_SYSTEM_SUCCESS;
		else
			echo _CONFIG_MAILING_SYSTEM_FAILURE;
		echo '</p>';
		break;
	case "save":
		// this is a list of all valid config variables
		$_CFG_VARS = $d->cfg->getVarNames();
		$_CFG_VALS = array();
		
		// first take the database variables
		foreach(array('db','prefix','dbhost','dbname','dbusername','dbpassword') as $var) {
			$cvar = 'c'.$var;
			$_CFG_VALS[$var] = $$cvar = in_raw($cvar, $_POST);
		}		

		// handle database move operation
		$cdbmove = in_checkbox('cdbmove', $_POST);
		if ( $cdbmove && (($d->cfg->getVar('d_db') !== $cdb) ||
			($d->cfg->getVar('d_prefix') !== $cprefix.'_') ||
			($d->cfg->getVar('d_dbhost') !== $cdbhost) ||
			($d->cfg->getVar('d_dbname') !== $cdbname) )) {
			include $d_root.'admin/classes/dbbackup.php';
			$dbbak = new DbBackup();
			$newconn = new DbFork($cdb);
			$newconn->Initialize($cdbhost,$cdbusername,$cdbpassword,$cdbname, $cprefix.'_');
			$dbbak->Replicate( $newconn );
		}
		
		foreach($_CFG_VARS as $var) {
			if (substr($var, 0, 2)=='db')
				continue;
			$val = in_raw('n'.$var, $_POST);
			if (isset($val))
				$_CFG_VALS[$var] = $val;
		}
		
		// apply some custom fixes to variables
		$_CFG_VALS['prefix'] = $cprefix.'_';
		// set the new access options before saving the config file
		global $d_setmode, $d_setowner, $d_setgroup;
		$d_setmode = $_CFG_VALS['setmode'];
		$d_setgroup = $_CFG_VALS['setgroup'];
		$d_setowner = $_CFG_VALS['setowner'];
		
//		$d->cfg->setVar("d_private",$d_private);
		$nwebsite = $_CFG_VALS['website'];
		if ($nwebsite[strlen($nwebsite)-1]!='/')
			$nwebsite.='/';
		$_CFG_VALS['website'] = $nwebsite;
		
		$ndocs_server = $_CFG_VALS['docs_server'];
		if (strlen($ndocs_server)) {
			if (strtolower($ndocs_server) == strtolower($d_website))
				$ndocs_server = '';
			else if (substr($ndocs_server, -1)!='/')
				$ndocs_server .= '/';
		}
		$_CFG_VALS['docs_server'] = $ndocs_server;
		$_CFG_VALS['seo'] = (int)$_CFG_VALS['seo'];
		if ($_CFG_VALS['force_text_email'])
			$_CFG_VALS['email_text'] = 0;
		
		foreach($_CFG_VALS as $var => $val) {
			$d->cfg->SetVar('d_'.$var, $val);
		}
		
		$d->cfg->Save();
		CMSResponse::Back(_CONFIG_SAVED);
	break;

	default:
//		$pathway->add(_CONFIG_HEAD);
		show_config(is__writable($d_root.$d_private.'config.php'));
	break;
}

?>