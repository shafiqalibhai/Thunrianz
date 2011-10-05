<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Subsites component for Lanius CMS
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# main subsites management script
#

include com_path($d_type);

function &_get_subsite($id) {
	global $conn;
	//L: when is the below used?
	if (!$id) {
		global $d;
		$S =& $d->cfg;
		return $S;
	} else
		$subsite = current($conn->SelectRow('#__subsites', 'subpath',' WHERE id = '.$id));
	global $d_root, $d,  $d_private;
	$S = new CMSConfig($d_root.$subsite.$d_private);
	$S->name = substr($subsite, 0, -1);
	$vf =& $S;
	return $vf;
}

function subsite_publication($online) {
	$ids = in('cid', __ARR | __NUM, $_POST);
	foreach ($ids as $id) {
		$S =& _get_subsite($id);
		$S->setVar('d_online', $online);
		$S->Save();
	}
}

function _get_next_prefix($base) {
	preg_match('/\\d+/', $base, $m);
	if (empty($m))
		$n = 0;
	else
		$n = (int)$m[0];
	$n += mt_rand(1, 99-$n);
	return substr($base, 0, -1).(string)$n.'_';
}

switch ($task) {
	case 'new':
		$clone = in('cid', __ARR0 | __NUM, $_POST, 0);
		subsite_edit($clone, 'create');
	break;
	case 'edit':
		if (null !== ($id = in('cid', __ARR0 | __NUM, $_REQUEST)))
			subsite_edit($id, 'save');
	break;
	case 'update':
		if (null !== ($id = in('cid', __ARR0 | __NUM, $_REQUEST)))
			subsite_update($id);
	break;
	case 'save':
		if ( (null === ($id = in_num('id', $_POST))) ||
			('' === ($subpath = in_sql('subsite_path', $_POST, ''))) ||
			('' === ($p_subpath = in_sql('p_subpath', $_POST, ''))) ||
			(null === ($online = in_num('subsite_online', $_POST)))
		)
			CMSResponse::Redir('admin.php?com_option=subsites', _FORM_NC);

		if ($subpath{strlen($subpath)-1}!='/')
			$subpath.='/';
		
		// modify the config file before moving the subsite
		$title = in('subsite_title', __NOHTML, $_POST);
		$S =& _get_subsite($id);
		$S->setVar('d_title', $title);
		$S->setVar('d_online', $online);
		$S->Save();
		
		include_once $d_root.'admin/classes/fs.php';
		$fs = new FS();

		if ($subpath != $p_subpath) {
			if ($fs->is_dir($d_root.$p_subpath))
				$fs->rename($d_root.$p_subpath, $d_root.$subpath);
			$conn->Execute('UPDATE #__subsites SET subpath = \''.$subpath.'\' WHERE id = '.$id);
		}
		
		CMSResponse::Redir('admin.php?com_option=subsites');
	break;
	case 'create':
	
		include $d_root.'admin/classes/fs.php';
		$fs = new FS();

		if ( (null === ($clone_src = in_sql('src', $_POST))) ||
			('' === ($subpath = in_sql('subsite_path', $_POST, ''))) ||
			(null === ($online = in_num('subsite_online', $_POST))) ||
			(null === ($clean = in_num('subsite_clean', $_POST)))
		)
			CMSResponse::Redir('admin.php?com_option=subsites', _FORM_NC);

		$title = in('subsite_title', __NOHTML, $_POST);

		if ($subpath{strlen($subpath)-1}!='/')
			$subpath.='/';

		if (is_dir($d_root.$subpath))
			CMSResponse::Back(_SUBSITES_DIR_EXISTS);
		
		if (($d_db!=='gladius') && (strpos($d_db,'sqlite')!==0)) {
			$prefix = in_path('subsite_prefix', $_POST, '');
			if (!strlen($prefix))
				CMSResponse::Back(_FORM_NC);
			if ($prefix == $d_prefix)
				CMSResponse::Back(_SUBSITES_SAME_PREFIX);
		} else
			$prefix = $d_prefix;

		// create the subsite folders structure
		$subfolders = array (
							substr($d_private, 0, strlen($d_private)-1),
							$d_private.'backup',
							$d_private.'cache',
							$d_private.'downloads',
							$d_private.$d_dbname,
							$d_private.'temp',
							'media',
							'media/banners',
							'media/icons',
							'media/gallery',
							'media/gallery/thumbs',
							'admin'
						);
		// create the subsite root folder
		$fs->mkdir(substr($d_root.$subpath, 0, -1));
		// create the subdirectories and put a blank index.htm in them
		foreach ($subfolders as $dir) {
			$fs->mkdir($d_root.$subpath.$dir);
			$fs->put_contents($d_root.$subpath.$dir.'/index.htm', '');
		}
		
		// create the empty logfile
		$fs->put_contents($d_root.$subpath.$d_private.'log.php', '<'.'?php if(!defined(\'_VALID\')){header(\'Status: 404 Not Found\');die;} ?'.">\n");

		// create the subsite config.php file
		$S = new CMSConfig($d_root.$clone_src.$d_private);
		$S->name = substr($subpath, 0, -1);
		$S->setVar('d_title', $title);
		$S->setVar('d_online', $online);
		$S->setVar('d_website', $d_website.$subpath);
		$newrand = random_string(6);
		$newuid = random_string(8);
		$S->setVar('d_rand', $newrand);
		$S->setVar('d_uid', $newuid);

		// the previous prefix can be hold if the database is flatfile, otherwise a randomized prefix will be proposed
		if (!$clean) {
			if ($d_db=='gladius') { // plain copy for flatfile databases
				$fs->fcopy($d_root.$clone_src.$d_private.$d_dbname.'/',
								  $d_root.$subpath.$d_private.$d_dbname.'/');
				$was_flat = true;
			} else if (strpos($d_db, 'sqlite')===0) {
				$fs->copy($d_root.$d_private.$d_dbname.'/'.$d_uid.'_'.$d_dbname.'.db',
						$d_root.$subpath.$d_private.$d_dbname.'/'.$newuid.'_'.$d_dbname.'.db');
				$was_flat = true;
			} else
				$was_flat = false;
		} else $was_flat = false;
		
		$S->setVar('d_prefix', $prefix);
		$newconn = new DbFork($d_db);
		$newconn->SubInitialize($S->name, $newuid, $d_dbhost,$d_dbusername,$d_dbpassword,$d_dbname, $S->getVar('d_prefix'));
		// proceed to database replication
		if (!$clean) {
			if (!$was_flat) {
				include $d_root.'admin/classes/dbbackup.php';
				$dbbak = new DbBackup();
				$dbbak->Replicate($newconn);
			}
		} else { // create the subsite as a fresh new one
			require $d_root.'install/install_lib.php';
			install_cms($newconn, $d_db, true);
			admin_insert($newconn, admin_pwd());
		}

		// disable subsites in subsites
		$newconn->Execute('DROP TABLE #__subsites');
		$newconn->Update('#__components', 'admin_access=9', ' WHERE option_link=\'com_subsites\'');
			
		// update paths to common images
		$rows = $newconn->SelectArray('#__content', 'id,bodytext,introtext',
								' WHERE bodytext LIKE \'%src="media/common/%\' OR introtext LIKE \'%src="media/common/%\'');
		foreach($rows as $row) {
			$newconn->Update('#__content', 'bodytext=\''.sql_encode(str_replace('src="media/common/', 'src="../media/common/',$row['bodytext']))."', introtext='".sql_encode(str_replace('src="media/common/', 'src="../media/common/',$row['introtext']))."'", ' WHERE id='.$row['id']);
		}

		// save the configuration file
		$S->Save($d_root.$subpath.$d_private); $S = null;
		
		$fs->fcopy($d_root.$clone_src.'media/', $d_root.$subpath.'media/');
		$fs->fcopy($d_root.$clone_src.'media/icons/', $d_root.$subpath.'media/icons/');
		$fs->fcopy($d_root.$clone_src.'media/banners/', $d_root.$subpath.'media/banners/');
		$fs->tcopy($d_root.$clone_src.'media/gallery/', $d_root.$subpath.'media/gallery/');
		$fs->tcopy($d_root.$clone_src.'media/gallery/thumbs/', $d_root.$subpath.'media/gallery/thumbs/');
		$fs->copy($d_root.$clone_src.'404.php', $d_root.$subpath.'404.php');
		
		$conn->Insert('#__subsites', '(subpath)', '\''.$subpath.'\'');

		$fs->copy($d_root.'admin/index.php', $d_root.$subpath.'admin/index.php');
		$pages = array('index.php', 'index2.php', 'admin.php', 'admin2.php',
					'core.php', 'version.php');
		if ($clone_src !== '') {
			foreach ($pages as $page)
				$fs->copy($d_root.$clone_src.$page, $d_root.$subpath.$page);
		} else {
			foreach ($pages as $page) {
				$content = '<'."?php ## Subsite proxy for $page\n\n\$d_subpath = '../';\n\n\$d_private = '{$subpath}$d_private';\n\ninclude '../$page';\n\n?".'>';
				$fs->put_contents($d_root.$subpath.$page, $content);
				unset($content);
			}
		}
		
		// setup the admin login
/*		global $d_uid;
		$_SESSION[$newuid.'-uid'] = $_SESSION[$d_uid.'-uid'];	*/

		CMSResponse::Redir('admin.php?com_option=subsites&task=redir&ss='.rawurlencode(substr($subpath,0,-1)));
	break;
	case 'redir':
		$ss = in_path('ss', $_GET);
		subsite_created($ss);
	break;
	case 'delete':
		$ids = in('cid', __ARR | __NUM, $_POST);
		include $d_root.'admin/classes/fs.php';
		$fs = new FS();

		foreach ($ids as $id) {
			$row = $conn->SelectRow('#__subsites', 'subpath', ' WHERE id='.$id);
			$dir = $d_root.$row['subpath'];
			if ($fs->is_dir($dir)) {
				$S =& _get_subsite($id);
				$s_db = $S->getVar('d_db');
				// try to remove its associated database, if config.php was available
				if (isset($s_db)) {
					$newconn = new DbFork($s_db);
					// do not spend time if the DB is already a flat file
					//WARNING: may not clear SQLite dbs not in the subsite directory
					if (!$newconn->IsFlatfile()) {
						$newconn->SubInitialize($S->name,
						$S->getVar('d_uid'),
						$S->getVar('d_dbhost'),
						$S->getVar('d_dbusername'), $S->getVar('d_dbpassword'),
						$S->getVar('d_dbname'), $S->getVar('d_prefix'));
						$tables = $newconn->MetaTables();
						foreach($tables as $table) {
							$newconn->Execute('DROP TABLE #__'.$table);
						}
					}
				}
				$fs->deldir($dir);
			}
			$conn->Execute('DELETE FROM #__subsites WHERE id='.$id);
		}
		CMSResponse::Redir('admin.php?com_option=subsites');
	break;
	case 'publish':
		subsite_publication(1);
		CMSResponse::Redir('admin.php?com_option=subsites');
	break;
	case 'unpublish':
		subsite_publication(0);
		CMSResponse::Redir('admin.php?com_option=subsites');
	break;
	default:
		subsites_table();
}

?>
