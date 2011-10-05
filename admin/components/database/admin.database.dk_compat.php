<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

global $amp_fixed;
$amp_fixed = false;
function _fix_ampersands(&$conn) {
	global $amp_fixed;
	if ($amp_fixed) return;
	$rsa = $conn->SelectArray('#__menu' ,'id,link', ' WHERE link LIKE \'%&amp;%\'');
	foreach($rsa as $row) {
		$link = str_replace('&amp;', '&', $row['link']);
		$conn->Update('#__menu', 'link=\''.sql_encode($link).'\'', ' WHERE id='.$row['id']);
	}
	$amp_fixed = true;
}

// convert a topmenu with flat_list and -nav class_sfx to a horizontal topmenu
// remove old CSS class properties
function _fix_module_params($lines) {
	//return implode("\n", $lines);
	$a='';
	$is_topmenu = false;
	$the_class = '';
	$the_ms = '';
	foreach($lines as $line) {
		// skip empty lines
		if (!strlen($line)) continue;
		$p = strpos($line, '=');
		// skip garbled definition
		if ($p === FALSE) continue;
		// split key-value pair
		$k = substr($line, 0, $p);
		$v = substr($line, $p+1);
		// skip empty values
		if (!strlen($v)) continue;
		// always skip class definitions but save once the class value
		if (($k == 'class_sfx') || ($k == 'custom_class') || ($k == 'moduleclass_sfx')) {
			if (!strlen($the_class))
				$the_class = $v;
			continue;
		}
		// check if this is a topmenu
		if ($k == 'menutype')
			$is_topmenu = ($v == 'topmenu');
		else {
			// save the menu style value for later fix
			if ($k == 'menu_style') {
				$the_ms = $v;
				continue;
			}
		}
		// add to buffer
		$a .= $line."\n";
	}
	// apply necessary fixes to topmenus
	if ($is_topmenu) {
		if ($the_ms != 'vertical')
			$the_ms = 'flat_list';
		$the_class = 'topmenu';
	}
	// put back special values
	if (strlen($the_ms))
		$a .= 'menu_style='.$the_ms."\n";
	$a.='custom_class='.$the_class."\n";
	return $a;
}

function _fix_cat_icon($path) {
	$p = strpos($path, '.gif');
	if ($p === FALSE) return $path;
	$l = strlen($path)-4;
	if ($p == $l) {
		$npath = substr($path, 0, $l).'.png';
		if (file_exists($GLOBALS['d_root'].'media/icons/'.$npath))
			return $npath;
	}
	return $path;
}

global $__category_recounted;
$__category_recounted = false;

function _fix_category_count(&$conn) {
	global $__category_recounted;
	if ($__category_recounted)
		return;
	$__category_recounted = true;
	$cats = $conn->SelectArray('#__categories', 'id,count,name,section');
	foreach($cats as $crow) {
		switch ($crow['section']) {
			case 'com_gallery':
			case 'com_downloads':
			case 'com_weblinks':
			case 'com_faq':
				$table = '#__'.substr($crow['section'], 4);
				$c = $conn->SelectCount($table, '*', ' WHERE published=1 AND catid='.$crow['id']);
				if ($c != $crow['count']) {
					$conn->Update('#__categories', 'count='.$c, ' WHERE id='.$crow['id']);
					echo 'Fixed category count '.$table.'/'.$crow['name'].' ('.$crow['count'].' -> '.$c.')<br/>';
				}
			break;
			case 'com_menu':
			case 'com_polls':
			break;
			default:
				if (!lcms_ctype_digit($crow['section'])) {
					echo 'categories of section '.$crow['section'].' skipped<br/>';
				} else {
					$c = $conn->SelectCount('#__content', '*', ' WHERE published=1 AND catid='.$crow['id']);
					if ($c != $crow['count']) {
						echo 'Fixed category count #__content/'.$crow['name'].' ('.$crow['count'].' -> '.$c.')<br/>';
						$conn->Update('#__categories', 'count='.$c, ' WHERE id='.$crow['id']);
					}
				}
		}
	}
}

global $__safety_frontpage;
$__safety_frontpage = false;

function _safety_frontpage(&$conn) {
	global $__safety_frontpage;
	if ($__safety_frontpage)
		return;

	// fix the undead  content frontpage items
	$ids = $conn->GetArray('SELECT id FROM #__content_frontpage');
						
	foreach ($ids as $row) {
		$rs = $conn->Execute('SELECT id FROM #__content WHERE id='.$row['id']);
			if (!$rs->RecordCount())
				$conn->Execute('DELETE FROM #__content_frontpage WHERE id='.$row['id']);
				$rs->Close();
	}
	$__safety_frontpage = true;
}

global $__safety_forums;
$__safety_forums = false;
function _safety_forums(&$conn) {
	global $__safety_forums;
	if ($__safety_forums) return;
	$rsa = $conn->SelectArray('#__forum_topics', 'id,post_list,checked_out,post_count');

	// fix the checked_out and post_count fields of forum_topics
	foreach($rsa as $row) {
		if (preg_match('/(\\d+)_$/', $row['post_list'], $m))
			$co = (int)$m[1];
		else $co=0;
		$c=substr_count($row['post_list'],'_')/2;
		$conn->Update('#__forum_topics', 'post_count='.$c.', checked_out='.$co,
				' WHERE id='.$row['id']);
	}
	$__safety_forums = true;
}

function _convert_params_se($table) {
	global $conn;
	$rsa = $conn->SelectArray($table, 'id,params');
	foreach($rsa as $row) {
		$params = _convert_old_params( $row['params'] );
		$conn->Update($table, 'params=\''.sql_encode(serialize($params))."'", ' WHERE id='.$row['id']);
	}
}

function dk_upgrade(&$conn, &$dbbak, $oldver) {
	$lm_compat = ($oldver === '');

	// finally apply adaptive restructuration if old databases were imported
	if ($lm_compat) {
		echo '<h3>'._DB_COMPAT_FIX.' Limbo</h3>';

		_safety_frontpage($conn);
		
		$dbbak->Push($conn->SelectArray('#__categories', 'id,title', ' WHERE section=\'com_menu\''));
		
		$conn->Execute('DELETE FROM #__drabots WHERE element=\'drapagenav\'');
		
		// replace class definition for topmenu menus
		$rsa = $conn->SelectArray('#__modules', 'id,params', ' WHERE params LIKE \'%menutype=topmenu%\'');
		foreach($rsa as $row) {
			$params = str_replace('class_sfx=-nav', 'moduleclass_sfx=-nav', $row['params']);
			$conn->Update('#__modules', 'params=\''.sql_encode($params).'\'',
									' WHERE id='.$row['id']);
		}
		
		// fix category images
		$rsa = $conn->SelectArray('#__categories', 'id,image', ' WHERE image <>\'\'');
		foreach($rsa as $row) {
			$conn->Update('#__categories', 'image=\''.sql_encode(_fix_cat_icon($row['image']))."'");
		}
	} else $dbbak->Push(array());
	
	//TODO: find which tables needed rebase in v0.3.8 instead of issuing a full upgrade
	$need_rebase = ($lm_compat || (strnatcmp($oldver, '0.3.8')<0) );
	// save the 'created' column
	if (!$lm_compat && (strnatcmp($oldver, '0.4.11')<0))
		$ccr = $conn->SelectArray('#__content', 'id,created_by', ' WHERE created_by<>0');
	else $ccr = array();
	
	// get the forum_config row only from versions 0.3.3-0.3.9
	if ($need_rebase && !$lm_compat && (strnatcmp($oldver, '0.4.0'))<0 && (strnatcmp($oldver, '0.3.3')>=0)) {
		$forum_row = $conn->SelectRow('#__forum_config', '*');
	} else $forum_row = array();
	
	// v0.3.4 introduction of big database changes, UTF-8 re-conversion
	if ($need_rebase) {
		echo '<h3>'._DB_REBASE_PROGRESS.'</h3>';

		if (!$lm_compat && (strnatcmp($oldver, '0.4.6')<0)) {
			$galleries = $conn->SelectArray('#__categories', 'id,name', ' WHERE section=\'com_gallery\'');
			$conn->Update('#__categories', 'name=title');
		} else
			$galleries = array();
		//NOTE: params is not serialized at this time!
		$mc = $conn->SelectArray('#__modules', 'id,message,params', ' WHERE module=\'content\'');
		$dbbak->Push($mc);
	} else {
		if (strnatcmp($oldver, '0.4.6')<0)
			$galleries = $conn->SelectArray('#__categories', 'id,name', ' WHERE section=\'com_gallery\'');
		else
			$galleries = array();
		$dbbak->Push(array());
	}
	
	if ($lm_compat || (strnatcmp($oldver, '0.4.3')<0))
		$dbbak->Push($conn->SelectArray('#__modules', 'id,published'));
	else
		$dbbak->Push( array() );
	if ($need_rebase)
		$dbbak->Rebase();

	switch ($oldver) {
		case '':
		// in case of Limbo CMS
		default:
		// RFC! ^
		case '0.3.4':
			// com_sections was deleted
			echo '<h3>'._DB_COMPAT_FIX.' v0.3.5</h3>';
			$conn->Execute('DELETE FROM #__components WHERE admin_menu_link=\'com_option=sections\'');
		case '0.3.5':
					// an optimization to #__content_frontpage table
						echo '<h3>'._DB_COMPAT_FIX.' v0.3.6</h3>';
//						$rows = $conn->GetArray('SELECT id,ordering FROM #__content_frontpage');
						$dbbak->Rebase('content_frontpage');

/*						foreach($rows as $row) {
							$conn->Execute('INSERT INTO #__content_frontpage (id,ordering) VALUES('.$row['id'].','.$row['ordering'].')');
						}	*/
					
		case '0.3.6':
			// UTF-8 corruption (not fixable), drapagenav bug and single quotes proliferation (fixed since v0.4.0)
			echo '<h3>'._DB_COMPAT_FIX.' v0.3.7</h3>';
			$conn->Execute('DELETE FROM #__drabots WHERE element=\'drapagenav\'');					
		case '0.3.7':
		// count again the content items in default categories, re-base for sections and categories editgroup, add new components for admin and client side
			echo '<h3>'._DB_COMPAT_FIX.' v0.3.8</h3>';
			$ids = $conn->GetColumn('SELECT id FROM #__categories WHERE name=\'Lanius CMS News\'');
			if (count($ids)) {
				$rs = $conn->Execute('SELECT id FROM #__content WHERE catid='.$ids[0]);
				$conn->Execute('UPDATE #__categories SET count = '.$rs->RecordCount().' WHERE id='.$ids[0]);
			}
			$ids = $conn->GetColumn('SELECT id FROM #__categories WHERE name=\'General News\'');
			if (count($ids)) {
				$rs = $conn->Execute('SELECT id FROM #__content WHERE catid='.$ids[0]);
				$conn->Execute('UPDATE #__categories SET count = '.$rs->RecordCount().' WHERE id='.$ids[0]);
			}
			$conn->Execute('UPDATE #__categories SET access=1 WHERE name=\'usermenu\' AND access=0 AND section=\'com_menu\'');
			$conn->Execute('UPDATE #__forum_users SET image=\'custom/admin.png\' WHERE id=1');
			$conn->Execute('UPDATE #__weblinks SET url=\'http://www.laniuscms.org/\' WHERE url=\'http://www.drakecms.org\'');
			$conn->Execute('UPDATE #__menu SET access=1 WHERE menutype=\'usermenu\'');
			$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES ('usermenu','Submit Download','index.php?option=downloads&amp;task=new','url',0,0,0,4,0,1,'')");
			$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES ('usermenu','Submit a Picture','index.php?option=gallery&amp;task=new','url',0,0,0,4,0,1,'')");
			$conn->Execute("UPDATE #__components SET admin_access=3,option_link='com_frontpage' WHERE link='option=frontpage'");
			$conn->Execute("UPDATE #__components SET iscore=0 WHERE option_link='com_banners'");
			$conn->Execute("UPDATE #__components SET iscore=1,option_link='com_user' WHERE link='option=user'");
			$tofix = array('search','contact','login');
			
			foreach($tofix as $com) {
				$conn->Execute("UPDATE #__components SET option_link='com_$com' WHERE link='option=$com'");
			}
				
			$conn->Execute("INSERT INTO #__components (name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES ('Sitemap','',0,0,'option=sitemap','','com_sitemap',0,0,9)");
			
			$conn->Execute("INSERT INTO #__components (name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES ('Content','',0,0,'','','com_content',0,0,3)");
			
			$row = $conn->GetRow('SELECT id FROM #__components WHERE link=\'option=guestbook\'');
			if (count($row)) {
				$conn->Execute("UPDATE #__menu SET componentid=".$row['id']." WHERE link='index.php?option=guestbook'");
			}
			
			$new_components = array(
array('Manage modules','com_modules',4),
array('System','com_system',5),
array('Subsites manager','com_subsites',5),
array('Templates manager','com_templates',4),
array('Languages manager','com_language',4),
array('Menu editor','com_menu',4),
array('Content','com_content',3),
array('Messages','com_messages',4),
array('Start','com_start',3),
array('Manage drabots','com_drabots',4),
array('Global configuration','com_config',4),
array('Tarball backup','com_backup',4),
array('Admin Templates manager','com_admintemplates',4),
array('Help','com_help',4),
array('Patch','com_patch',5),
array('Massmail','com_massmail',4),
array('Manage components','com_components',5) );

						foreach($new_components as $com) {
							if ($conn->Insert('#__components', '(name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access)',
							"'".sql_encode($com[0])."','',0,0,'','','".$com[1]."',0,1,".$com[2])===false)
								echo $conn->ErrorMsg().'<br />';
						}
						
						echo count($new_components).' admin components added';
		case '0.3.8':
					// fixes bad Itemids in menu links (also good for v0.3.7 imports)
					echo '<h3>'._DB_COMPAT_FIX.' v0.3.9</h3>';
					// auto-fix bad component ids
					$ids = $conn->GetArray('SELECT id FROM #__components');
					$rsa = $conn->GetArray('SELECT id,link,componentid FROM #__menu WHERE componentid<>0');
					foreach($rsa as $row) {
						if (in_array($row['componentid'], $ids)) continue;
						$url = $row['link'];
						$p=strpos($url, 'option=');
						if ($p===false) continue;
						$p+=7;
						$url = substr($url, $p);
						$p = strpos($url, '&');
						if ($p!==false) $url=substr($url, 0, $p);
						$row2 = $conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_'.$url.'\'');
						if (count($row2))
							$conn->Update('#__menu',
							'componentid='.$row2['id'],
							' WHERE id='.$row['id']);
					}
					
					$conn->Execute("INSERT INTO #__categories (parent_id,name,image,image_position,section,description,ordering,access,count) VALUES (0,'loginmenu','','','com_menu','',0,1,0)");
					
					$row = $conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_login\'');
					$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES ('loginmenu','Login','index.php?option=login','component',0,".$row['id'].",0,1,0,0,'')");
					
					$conn->Execute("INSERT INTO #__components (name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES ('Registration','option=registration',0,0,'','','com_registration',0,1,9)");
					
					$row = $conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_registration\'');
					$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params)
VALUES ('loginmenu','Registration','index.php?option=registration','component',0,".$row['id'].",0,2,0,0,'')");

					$row = $conn->GetRow('SELECT id FROM #__menu WHERE params=\'\' AND link=\'index.php?option=gallery\'');
					if (count($row))
						$conn->Execute("UPDATE #__menu SET params='show_as_popup=1\nshow_cat=3\nshow_item=3\nshow_item_row=3\n' WHERE id=".$row['id']);
		case '0.3.9':
			// added missing components in database (polls, rss), downloads & users tables changed
			echo '<h3>'._DB_COMPAT_FIX.' v0.4.0</h3>';
						if (count($forum_row)) {
							$params = "rep_word=".$forum_row['rep_word']."\nbad_words=".$forum_row['bad_words']."\nshow_count=".$forum_row['show_count']."\nexpanded_sections=1"; unset($forum_row);
							$row = $conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_forum\'');
							if (isset($row['id'])) {
								$row = $conn->GetRow('SELECT id FROM #__menu WHERE componentid='.$row['id']);
								$conn->Execute('UPDATE #__menu SET params=\''.sql_encode($params).'\' WHERE id='.$row['id']);				}
							$conn->Execute('DROP TABLE #__forum_config');
						}
					
						$conn->Execute("INSERT INTO #__components (name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES ('Syndicate','option=rss',0,0,'','','com_rss',0,1,9)");					
						$row = $conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_rss\'');
						if (isset($row['id'])) {
							$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES ('hiddenmenu','Syndicate','index2.php?option=rss&no_html=1','component',0,".$row['id'].",0,3,0,0,'')");
						}
						$row = $conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_banners\'');
						if (isset($row['id']))
							$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) 
VALUES ('hiddenmenu','Banners','index.php?option=banner','component',0,".$row['id'].",0,3,0,0,'')");

						$row = $conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_polls\'');
						if (isset($row['id']))
							$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES ('hiddenmenu','Polls','index.php?option=polls','component',0,".$row['id'].",0,3,0,0,'')");
						
						$conn->Execute("INSERT INTO #__banners (name,imp,imphits,hits,imageurl,clickurl,published,bannercode) VALUES ('Test banner',0,0,0,'test_banner.png','http://www.laniuscms.org/',0,'')");
						
						$row = $conn->GetRow('SELECT id FROM #__categories WHERE name=\'myphotos\'');
						if (isset($row['id']))
							$conn->Execute('UPDATE #__categories SET image=\'hamadryas_februa.jpg\' WHERE id='.$row['id']);
						$conn->Execute('UPDATE #__components SET iscore=1 WHERE option_link=\'com_content\'');

						$conn->Execute('UPDATE #__components SET link=\'\' WHERE option_link=\'com_fm\'');
						
						$row = $conn->GetRow('SELECT id FROM #__components WHERE admin_menu_link=\'com_option=forum&option=categories\'');
						if (isset($row['id']))
							$conn->Execute('UPDATE #__components SET admin_menu_link=\'com_option=forum&option=categories&sec_id=0\', name=\'Main categories\' WHERE id='.$row['id']);

						$row = $conn->GetRow('SELECT id FROM #__components WHERE admin_menu_link=\'com_option=forum&option=config\'');
						if (isset($row['id']))
							$conn->Execute('UPDATE #__components SET admin_menu_link=\'com_option=forum&option=section\', name=\'Sections\' WHERE id='.$row['id']);
						
						$conn->Execute('UPDATE #__components SET link=\'\' WHERE option_link=\'com_fm\'');
						
						$conn->Execute("INSERT INTO #__gallery (catid,title,description,url,date,hits,published,ordering) VALUES (9,'Hamadryas februa','This specie is particularly intricate in its patterning and color of the flies.','hamadryas_februa.jpg',1177308618,0,1,1)");
						
						if (!$need_rebase) {
							$download_data = $conn->GetArray('SELECT id, url, approved FROM #__downloads');
							$dbbak->Rebase('downloads');
							foreach($download_data as $row) {
								$protected = (preg_match('/^[a-z0-9]{6,}_/', basename($row['url']))!=0);
								$conn->Execute('UPDATE #__downloads SET published='.$row['approved'].
								', protected='.$protected.' WHERE id='.$row['id']);
							}
							$dbbak->Rebase('sections');
							$dbbak->Rebase('users');
						}
						
						// rename loginmenu to hiddenmenu
						$row = $conn->GetArray('SELECT id FROM #__categories WHERE section=\'com_menu\' AND name=\'loginmenu\'');
						if (isset($row['id']))
							$conn->Execute('UPDATE #__categories SET name=\'hiddenmenu\' WHERE id='.$row['id']);
						$conn->Execute('UPDATE #__menu SET menutype=\'hiddenmenu\' WHERE menutype=\'loginmenu\'');
	case '0.4.0':
				echo '<h3>'._DB_COMPAT_FIX.' v0.4.1</h3>';
						
						$dbbak->Rebase('event');
						
						$conn->Execute("INSERT INTO #__modules (title,message,ordering,position,module,access,showtitle,showon,params,iscore) VALUES ('W3C validations','',26,'left','mod_validate',0,0,'','show_xhtml=1
xhtml_version=1.0
show_css=0
css_version=2.1
',1)");
						$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES ('hiddenmenu','User','index.php?option=user','component',0,20,0,3,0,0,'')");
						
						$conn->Execute("INSERT INTO #__components (name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES ('About','option=about',0,0,'','','com_about',0,1,0)");
						
						$row=$conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_about\'');
						
						$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) VALUES ('hiddenmenu','About','index.php?option=about','component',0,".$row['id'].",0,3,0,0,'')");
						
						$conn->Execute('DELETE FROM #__components WHERE option_link=\'com_help\'');
						
						$row = $conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_database\'');
						if (!count($row))
							$conn->Execute("INSERT INTO #__components (name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) VALUES ('Database manager','',0,0,'','','com_database',0,1,4)");
		case '0.4.1':
			echo '<h3>'._DB_COMPAT_FIX.' v0.4.2</h3>';
global $d_userregistration, $d_useractivation, $d_emailpass;
if (!isset($d_userregistration)) {
	$d_userregistration = $d_useractivation = 1;
	$d_emailpass = 0;
}
$conn->Execute(sprintf('UPDATE #__menu SET params=\'registration_new=%s
registration_activation=%s
password_in_email=%s
\' WHERE params=\'\' AND link=\'index.php?option=registration\'',
$d_userregistration, $d_useractivation, $d_emailpass));
						
						$conn->Execute('UPDATE #__modules SET module=\'mod_stats\' WHERE module=\'mod_simple_stats\'');
						
						$conn->Execute('DELETE FROM #__weblinks WHERE url=\'http://www.drakeforge.net/\'');
						
						$conn->Execute('UPDATE #__weblinks SET url=\'http://docs.laniuscms.org/\', title=\'Lanius CMS official documentation\' WHERE url=\'http://wiki.drakecms.org/\'');
		case '0.4.2':
						echo '<h3>'._DB_COMPAT_FIX.' v0.4.3</h3>';
						
						$dbbak->Rebase('drabots', 'modules', 'users', 'content_rating');
						
						// publish/unpublish modules
						foreach($dbbak->Pop() as $row) {
							$conn->Update('#__modules', 'access='.( $row['published'] ? 0 : 9)
								, ' WHERE id='.$row['id']);
						}
						
						$conn->Execute('UPDATE #__modules SET module=\'mod_syndicate\' WHERE module=\'mod_rss\'');
						$conn->Execute('UPDATE #__components SET option_link=\'com_syndicate\', link=\'option=syndicate\' WHERE option_link=\'com_rss\'');
						$row = $conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_syndicate\'');
						if (count($row))
							$conn->Execute('UPDATE #__menu SET link=\'index2.php?option=syndicate&no_html=1\' WHERE componentid='.$row['id']);
						
						$conn->Execute('INSERT INTO #__components (name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) 
VALUES (\'Rss dummy\',\'option=rss\',0,0,\'\',\'\',\'com_rss\',0,1,9)');
						$row = $conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_rss\'');
						$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) 
VALUES ('hiddenmenu','RSS dummy','index2.php?option=rss&no_html=1','component',0,".$row['id'].",0,3,0,0,'')");

						$conn->Execute("INSERT INTO #__components (name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) 
VALUES ('File browser','option=fb',0,0,'','','com_fb',0,1,9)");

						$row = $conn->GetRow('SELECT id FROM #__components WHERE option_link=\'com_fb\'');
						$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) 
VALUES ('hiddenmenu','File browser','index2.php?option=fb','component',0,".$row['id'].",0,3,0,2,'')");

						$conn->Execute("INSERT INTO #__modules (title,message,ordering,position,module,access,showtitle,showon,params,iscore) 	 VALUES ('Language alternate links','',27,'head','mod_altlinks',0,0,'_22_','',1)");

$conn->Execute("INSERT INTO #__components (name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) 
VALUES ('Admin javascript menu','',0,0,'','','com_jsmenu',0,1,3)");
						_fix_ampersands($conn);

		case '0.4.3':
			echo '<h3>'._DB_COMPAT_FIX.' v0.4.4</h3>';
						
						$dbbak->Rebase('forum_topics', 'forum_posts', 'modules', 'drabots');
						
						// applied again since it was broken in v0.4.3
						_fix_ampersands($conn);

						$rsa = $conn->GetArray('SELECT id,params FROM #__modules WHERE params<>\'\'');
						foreach($rsa as $row) {
							$conn->Update('#__modules', 'params=\''.sql_encode(str_replace('moduleclass_sfx', 'custom_class', $row['params']))."'", ' WHERE id='.$row['id']);
						}
						
						$conn->Update('#__modules', 'module=\'mod_polls\'', ' WHERE module=\'mod_poll\'');
						
						$conn->Update('#__components', 'iscore=0', ' WHERE option_link=\'com_contact\'');
		case '0.4.4':
					// no database changes for v0.4.5
		case '0.4.5':
			echo '<h3>'._DB_COMPAT_FIX.' v0.4.6</h3>';
						
						// these are db fixes missed in v0.4.5 update
						_fix_ampersands($conn);

						// fix the categories deprecated field 'title' and save the gallery categories additional info
						if (!$need_rebase) {
							$galleries = $conn->SelectArray('#__categories', 'id,name', ' WHERE section=\'com_gallery\'');
							$conn->Update('#__categories', 'name=title');
							$dbbak->Rebase('categories', 'downloads', 'rating', 'forum_categories', 'forum_posts', 'guestbook',
									'polls_votes', 'simple_stats');
						}
						// reset the new tables in case that they are already in place
						$conn->Delete('#__gallery_category');
						$conn->Delete('#__rating');
						$conn->Delete('#__links');
						
						// convert content ratings to general ratings
						$rows = $conn->SelectArray('#__content_rating', '*');
						foreach($rows as $row) {
							$conn->Insert('#__rating', '', $row['id'].", ".$row['rating_sum'].", ".$row['rating_count'].', \''.
										$row['lastip'].'\', \'content\'');
						}
						$conn->Execute('DROP TABLE #__content_rating');
						
						// convert newsflash items to content items of a specific newsflashes category
						$news = $conn->SelectArray('#__newsflash', 'title,news,access');
						if (isset($news)) {
							$row = $conn->SelectRow('#__sections', 'id', ' WHERE name=\'General Content\'');
							if (!$row) {
								$conn->Insert('#__sections', '(title,name,published,access,count)',
									"'Newsflashes', 'Newsflashes', 1, 0, 1");
								$sectid = $conn->Insert_ID();
							} else						
								$sectid = $row['id'];
							$conn->Insert('#__categories', '(name,section,access,count)',
										"'Miscellanous newsflashes', $sectid, 0, ".count($news));
							$catid = $conn->Insert_ID();
							
							// set all mod_newsflash instances to fetch from the newsflashes category
							$mids = $conn->SelectArray('#__modules', 'id,params', ' WHERE module=\'mod_newsflash\'');
							foreach($mids as $row) {
								$conn->Update('#__modules', 'params=\''.sql_encode($row['params'].'catid='.$catid."\n")."'", ' WHERE id='.$row['id']);
							}
							
							// fix the images/ references in content items
							$rows = $conn->SelectArray('#__content', 'id,bodytext,introtext',
								' WHERE bodytext LIKE \'%src="images/%\' OR introtext LIKE \'%src="images/%\'');
							foreach($rows as $row)
								$conn->Update('#__content', 'bodytext=\''.sql_encode(str_replace('src="images/', 'src="media/',$row['bodytext']))."', introtext='".sql_encode(str_replace('src="images/', 'src="media/',$row['introtext']))."'", ' WHERE id='.$row['id']);
							
							// insert the newsflash contents
							global $time;
							foreach($news as $row) {
								$conn->Insert('#__content', '(title,title_alias,introtext,sectionid,catid,created,modified,created_by_alias,access,published,mask)',
								"'".sql_encode($row['title'])."', '".sql_encode($row['title'])."', '".sql_encode($row['news'])."', '".
								$sectid."', $catid, $time, $time, 'admin', ".$row['access'].", 1, 255"
								);
							} $news = null;
							
							$conn->Execute('DROP TABLE #__newsflash');
						}
						$conn->Delete('#__components', ' WHERE option_link=\'com_newsflash\'');
						
						// insert the new drabots
						$conn->Insert('#__drabots', '(name,type,element,showon,access,ordering,iscore,params)',
							"'Language alternate links','core','draaltlinks','',0,9,1,''");
						$conn->Insert('#__drabots', '(name,type,element,showon,access,ordering,iscore,params)',
							"'Downloads rating','download','dradownvote','',0,10,0,''");
						$conn->Insert('#__drabots', '(name,type,element,showon,access,ordering,iscore,params)',
							"'Search downloads drabot','search','searchdownload','',0,11,0,''");
						$conn->Insert('#__drabots', '(name,type,element,showon,access,ordering,iscore,params)',
							"'Wrapper Drabot','content','drawrapper','',9,12,0,''");
						$conn->Insert('#__drabots', '(name,type,element,showon,access,ordering,iscore,params)',
								"'Search gallery drabot','search','searchgallery','',0,13,0,''");

						// minor optimizations
						$conn->Update('#__modules', 'showon=\'\'', ' WHERE showon=\'_0_\'');
						$conn->Delete('#__categories', ' WHERE section=\'com_forms\'');
						
						global $d_root;
						include $d_root.'components/gallery/gallery.functions.php';
						
						// recreate the gallery categories
						foreach($galleries as $row) {
							$conn->Insert('#__gallery_category', '(id,gallery_path,thumbs_path)', $row['id'].
							", '".sql_encode(_GALLERY_DEFAULT.$row['name'].'/')."', '".
							sql_encode(_GALLERY_DEFAULT_THUMBS.$row['name'].'/')."'");
						}
						
						// add the <link />s
						$conn->Execute("INSERT INTO #__links (rel,type,title,href,access) VALUES('alternate', 'application/rss+xml', 'Home', 'index2.php?option=syndicate&no_html=1',0)");

						$conn->Execute("INSERT INTO #__links (rel,type,title,href,access) VALUES('sitemap', 'application/xml', 'Sitemap', 'sitemap.xml', 0)");
						$conn->Execute("INSERT INTO #__links (rel,type,title,href,access) VALUES('EditURI', 'application/rsd+xml', 'RSD', 'index2.php?option=remoteblog&no_html=1', 0)");
						
						// fix the blank.png references of category images
						$conn->Update('#__categories', 'image=\'\'', ' WHERE image=\'blank.png\'');

						// fix (finally) the garbled topmenu bug and remove class_sfx
						$rsa = $conn->SelectArray('#__modules', 'id,params',
							" WHERE params LIKE '%class_sfx%' OR params LIKE '%-nav%' OR params LIKE '%menutype=topmenu%'");
						//echo '<pre>';var_dump($rsa);echo '</pre>';
						foreach($rsa as $row) {
							$lines = array_map('trim', explode("\n", $row['params']));
							if (!count($lines)) continue;
							$conn->Update('#__modules', 'params=\''.
								sql_encode(_fix_module_params($lines))."'", ' WHERE id='.$row['id']);
						}
		case '0.4.6':
			echo '<h3>'._DB_COMPAT_FIX.' v0.4.7</h3>';
						// fixed menutype field (from 25 to 255 in length)
						$dbbak->Rebase('menu');
					
						$row = $conn->SelectRow('#__modules', 'id', ' WHERE module=\'mod_wrapper\'');
						if (!$row)
						// should have been in 0.4.6...
						$conn->Execute("INSERT INTO #__modules (title,message,ordering,position,module,access,showtitle,showon,params,iscore) 
VALUES ('Wrapper module','',25,'left','mod_wrapper',9,0,'','',1)");

						// SAFETY check of frontpage content items
						_safety_frontpage($conn);
						
						_safety_forums($conn);
					// Lanius CMS v0.4.7 RC3 had no database changes
		case '0.4.7':
			echo '<h3>'._DB_COMPAT_FIX.' v0.4.8</h3>';
			$dbbak->Rebase('modules');
			//NOTE: core package insert was moved to v0.4.10 because of a previous bug in restores
			// this means that database from v0.4.7 to v0.4.10 is not consistent
			// we first delete all rows in case of subsequent restores of older versions
			$conn->Delete('#__packages');
						
						// fix bug 1816938 in gallery paths
						$rsa = $conn->SelectArray('#__gallery_category', '*', ' WHERE gallery_path LIKE \'%//%\'');
						foreach($rsa as $row) {
							$conn->Update('#__gallery_category', 'gallery_path=\''.sql_encode(preg_replace('/\\/+/', '/', $row['gallery_path'])).'\', thumbs_path=\''. sql_encode(preg_replace('/\\/+/', '/', $row['gallery_path'])).'\'');
						}
						
						// banners component was renamed into banner
						$conn->Update('#__components', 'admin_menu_link=\'com_option=banner\',option_link=\'com_banner\'', ' WHERE option_link=\'com_banners\'');
						$conn->Update('#__menu', 'link=\'index.php?option=banner\'', ' WHERE link=\'index.php?option=banners\'');
		case '0.4.8':
			echo '<h3>'._DB_COMPAT_FIX.' v0.4.9</h3>';
						$dbbak->Rebase('users', 'sessions');
						// apply again this optimization
						$conn->Update('#__modules', 'showon=\'\'', ' WHERE showon=\'_0_\'');
						
						// delete all the previous packages
						$conn->Delete('#__packages', ' WHERE name<>\'Lanius CMS\'');
						global $d_root;
						// match all the packages insertion lines
						preg_match_all('/INSERT INTO #__packages.*?;/', file_get_contents($d_root.'install/inserts.sql.php'), $m);
						// insert all the packages now
						foreach($m[0] as $pkg) {
							$conn->Execute($pkg);
						}
						echo count($m[0])." packages added\n";
						
						// fix the user posts count
						$col = $conn->SelectColumn('#__forum_users', 'id');
						foreach($col as $id) {
							$c = $conn->Count('SELECT COUNT(*) FROM #__forum_posts WHERE userid='.$id);
							$conn->Update('#__forum_users', 'posts='.$c, ' WHERE id='.$id);
						}
						
						// fix a weird bug in default database data (probably added in v0.3 series)
						$conn->Update('#__components', 'admin_menu_link=\'com_option=forum&option=sections\'', ' WHERE option_link=\'com_forum\' AND admin_menu_link=\'com_option=forum&option=section\'');
						
						// fix a bug with avatars custom path (introduced since media/ change)
						$rows = $conn->SelectArray('#__forum_users', 'id,image',
							' WHERE image LIKE \'ustom/%\'');
						foreach($rows as $row) {
							$conn->Update('#__forum_users', 'image=\'c'.sql_encode($row['image']).'\'', ' WHERE id='.$row['id']);
						}
						
						$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) 
VALUES ('PHP mailer','core','phpmail','',9,15,1,'')");
						$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) 
VALUES ('BSD mailer','core','bsdmail','',9,16,0,'')");

						$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) 
VALUES ('SMTP mailer','core','smtpmail','',9,17,0,'')");
						$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) 
VALUES ('Sendmail mailer','core','smmail','',9,18,0,'')");

			$conn->Execute("INSERT INTO #__components (name,link,menuid,parent,admin_menu_link,admin_menu_alt,option_link,ordering,iscore,admin_access) 
VALUES ('Service','',0,0,'','','com_service',0,1,9)");
			$id = $conn->Insert_ID();
			$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params) 
VALUES ('hiddenmenu','Lanius CMS services','','component',0,".$id.",0,3,0,0,'')");

			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) 
VALUES ('DB session','core','dbsession','',9,19,0,'')");

			// rename hiddenmenu to servicemenu
			$row = $conn->SelectRow('#__categories', 'id', ' WHERE section=\'com_menu\' AND name=\'hiddenmenu\'');
			if (isset($row['id']))
				$conn->Update('#__categories', 'name=\'servicemenu\' WHERE id='.$row['id']);
			$conn->Update('#__menu', 'menutype=\'servicemenu\' WHERE menutype=\'hiddenmenu\'');
			
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) 
VALUES ('TinyMCE2 HTML Editor','editor','tinymce2','',9,20,0,'')");
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) 
VALUES ('FCK Editor','editor','fckeditor','',0,21,0,'')");
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) 
VALUES ('Midas HTML editor','editor','midas','',9,22,0,'')");

			case '0.4.9':
			echo '<h3>'._DB_COMPAT_FIX.' v0.4.10</h3>';
			$dbbak->Rebase('banners');
			global $d_root, $d_private;
			include_once $d_root.'admin/classes/fs.php';
			$fs = new FS(true);
			// protected downloads now have a custom extension, attempt to rename them
			$rows = $conn->SelectArray('#__downloads', 'id,url', ' WHERE flags=1 OR flags=3');
			foreach($rows as $row) {
				if (!is_url($row['url'])
					//&& file_ext($row['url']) != 'bin'
					) {
					// we only manage upload under the private directory
					if (strpos($row['url'], $d_private)!==0)
						continue;
					$fname = $row['url'].'.bin';
					$r = $fs->rename($d_root.$row['url'], $d_root.$fname);
					echo 'Updating protection of <em>'.xhtml_safe($row['url']).'</em>...'.
						($r ? 'OK': 'failed, removing record')."<br />";
					if ($r)
						$conn->Update('#__downloads', 'url=\''.sql_encode($fname).'\'', ' WHERE id='.
									$row['id']);
					else
						$conn->Delete('#__downloads', ' WHERE id='.$row['id']);
				} else
					echo 'Warning: download "'.$row['url'].'" is protected but should not be!'."\n";
			}
			
			// remove a duplicate dradown
			$conn->Delete('#__packages', ' WHERE name=\'dradown\' AND version=\'1.0.4\'');
			
			// put the wrapper instances link in the parameters
			$rsa = $conn->SelectArray('#__menu', 'id,link,params', ' WHERE link_type=\'wrapper\'');
			foreach($rsa as $row) {
				$conn->Update('#__menu', 'link=\'index.php?option=wrapper\', params=\''.sql_encode('url='.$row['link']."\n".$row['params'])."'", ' WHERE id='.$row['id']);
			}
			
			// remove the jsmenu admin component
			$conn->Delete('#__components', ' WHERE option_link=\'com_jsmenu\'');
			
			// add the 2 new admin menu drabots
			$conn->Insert('#__drabots', '(name,type,element,showon,access,ordering,iscore,params)', "'Admin javascript menu','admin_menu','jsmenu','',3,23,0,''");
			$conn->Insert('#__drabots', '(name,type,element,showon,access,ordering,iscore,params)',
				"'Admin CSS menu','admin_menu','cssmenu','',9,24,0,''");
			
			// fix wrongly assigned componentid
			$row = $conn->SelectRow('#__menu', 'id', ' WHERE name=\'Lanius CMS services\' AND link_type=\'component\' AND componentid=65');

			if (!empty($row)) {
				$crow = $conn->SelectRow('#__components', 'id', ' WHERE option_link=\'com_service\'');
				$conn->Update('#__menu', 'componentid='.$crow['id'], ' WHERE id='.$row['id']);
			}
			
			// replace the midas drabot with the native editor drabot
			$row = $conn->SelectRow('#__drabots', 'id', ' WHERE element=\'midas\'');
			if ($row) {
				$conn->Update('#__drabots', 'name=\'Native Midas/MSHTML WYSIWYG editor\', element=\'native_editor\'', ' WHERE id='.$row['id']);
			}
			
			// add the getff module
			$conn->Execute("INSERT INTO #__modules (title,message,position,module,access,showtitle,showon,params,iscore) VALUES ('Get Firefox','','left','mod_getff',0,0,'','',1)");
			
			// at version 0.4.7 the core package was introduced
			// due to a bug in restore code from v0.4.7 to v0.4.9 we will reinsert the core row here
			// after having removed the core package duplicates
			$conn->Delete('#__packages', ' WHERE type=\'core\' AND name=\'Lanius CMS\'');
			$conn->Insert('#__packages', '(type,name,version)',
									"'core', 'Lanius CMS', '".sql_encode(cms_version(true))."'");
			echo 'Inserted Lanius CMS v'.cms_version(true).' package<br />';

			// adding forgotten packages
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'smmail', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'bsdmail', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'cssmenu', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'auth_ldap', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'fckeditor', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'phpmail', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'tinymce2', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'native_editor', '0.2')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'dbsession', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'smtpmail', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'jsmenu', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('module', 'mod_getff', '1.0')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('component', 'com_service', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'syslog', '0.1')");

			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'filelog', '0.1')");

			echo "Added 15 missing packages<br />";
			
			// add the logger drabots
			global $d_log;
			if ($d_log == 1) $acs = "0";
			else	$acs = "9";
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('Syslog logger','logger','syslog','', $acs,25,0,'')");
			if ($d_log == 2) $acs = "0";
			else	$acs = "9";
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('File logger','logger','filelog','', $acs,26,0,'')");
		case '0.4.10':
			echo '<h3>'._DB_COMPAT_FIX.' v0.4.11</h3>';
			//this database fix is undifferentiated between v0.4.10 RC6 and RC7
			//added because of menu (categories) access field that is unused
			$conn->Update('#__categories', 'access=0', ' WHERE section=\'com_menu\'');
			// this fixes a bug introduced in RC7 and also invert the ordering
			$rsa = $conn->SelectArray('#__content', 'id,ordering', ' ORDER BY ordering');
			$c = count($rsa);
			for($i=0;$i<$c;++$i) {
				$conn->Update('#__content', 'ordering='.($c-1-$i), ' WHERE id='.$rsa[$i]['id']);
			}
			// invert frontpage ordering
			$rsa = $conn->SelectArray('#__content_frontpage', 'id,ordering', ' ORDER BY ordering');
			$c = count($rsa);
			for($i=0;$i<$c;++$i) {
				$conn->Update('#__content_frontpage', 'ordering='.($c-1-$i), ' WHERE id='.$rsa[$i]['id']);
			}
			// add the new mailer drabots
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('DB mailer','mail','dbmail','',9,27,0,'')");
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('Debug mailer','mail','mailbox','',9,28,0,'')");
			// add the package entries
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'dbmail', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'mailbox', '0.1')");
			// add the new tables
			$dbbak->Rebase('mail_queue', 'view_filter', 'contacts', 'polls_votes', 'content', 'downloads', 'event', 'faq',
						'gallery', 'content_comment');
			// convert params to new serialized format
			echo "Serializing parameters<br />";
			_convert_params_se('#__drabots');
			_convert_params_se('#__menu');
			_convert_params_se('#__modules');
			
			// restore the 'created_by' field
			foreach($ccr as $row) {
				$conn->Update('#__content', 'userid='.$row['created_by'], ' WHERE id='.$row['id']);
			}
			
			// add the ctmailcloaker drabot
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('Content email cloaker','content','ctmailcloak','',9,29,0,'')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('content', 'ctmailcloak', '0.1')");
			// first build a lookup table of users
			$rsa = $conn->SelectArray('#__users', 'id,username');
			$lkp = array();
			foreach($rsa as $row) {
				$lkp[$row['username']] = (int)$row['id'];
			}
			// try to assign ownership of content items using the alias
			global $my;
			$rsa = $conn->SelectArray('#__content', 'id,created_by_alias', ' WHERE userid=0');
			foreach($rsa as $row) {
				$alias = $row['created_by_alias'];
				if (isset($lkp[$alias]))
					$conn->Update('#__content', 'userid='.$lkp[$alias], ' WHERE id='.$row['id']);
				else
					$conn->Update('#__content', 'userid='.$my->id, ' WHERE id='.$row['id']);
			}
			// set ownership of remaining items to current user (administrator)
			$rsa = array( 'downloads', 'event', 'faq', 'gallery');
			foreach($rsa as $s) {
				$conn->Update('#__'.$s, 'userid='.$my->id, ' WHERE userid=0');
			}
			// downgrade editors to normal registered users
			$conn->Update('#__users', 'gid=1', ' WHERE gid=2');
			echo $conn->Affected_Rows().' editor accounts have been downgraded to normal registered accounts<br />';
			// recount all items of all categories
			_fix_category_count($conn);
		case '0.4.11': // moved in below case because of a bug in 0.5.0
		case '0.5.0':
			// apply fixes to a hybrid version
			$row = $conn->SelectRow('#__components', 'id', ' WHERE iscore=1 AND option_link=\'com_syndicate\'');
			if (!empty($row))
				the_0411_fixes($conn, $dbbak);
	}

}

function the_0411_fixes(&$conn, &$dbbak) {
			_fix_category_count($conn);
			// remove some blanks
			$conn->Execute("UPDATE #__content SET bodytext='' WHERE bodytext='<p>&#160;</p>'");
			// --
			// minor modification
			$dbbak->Rebase('rating', 'forum_posts', 'content', 'messages', 'mail_queue');
			
			$conn->Update('#__packages', 'name=\'Lanius CMS\'', ' WHERE type=\'core\' AND name=\'Drake CMS\'');
			
			// remove duplicate instances of com_content
			$ids = $conn->SelectColumn('#__components', 'id', ' WHERE option_link=\'com_content\'');
			if (count($ids) > 1) {
				$first = array_shift($ids);
				foreach($ids as $id) {
					$conn->Update('#__menu', 'componentid='.$first, ' WHERE componentid='.$id);
				}
				$conn->Delete('#__components', ' WHERE '.each_id($ids));
			}
			
			// remoteblog is not core
			$conn->Update('#__components', 'iscore=0', ' WHERE option_link=\'com_remoteblog\'');
			$conn->Update('#__components', 'iscore=0', ' WHERE option_link=\'com_syndicate\'');
			
			// looks like this one was missing
			$row = $conn->SelectRow('#__drabots', 'id', ' WHERE type=\'core\' AND element=\'auth_ldap\'');
			if (!isset($row['id']))
				$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('LDAP authentication drabot','core','auth_ldap','',9,29,0,'')");
			// add the new reg_ldap drabot
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('LDAP registration drabot','core','reg_ldap','',9,30,0,'')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'reg_ldap', '0.1')");
			// add the new reg_filter drabot
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'reg_filter', '0.1')");
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('Account filtering drabot','core','reg_filter','',0,31,0,'')");
			// re-format all usernames (WARNING: will cause major havoc
			$rows = $conn->SelectArray('#__users', 'id,username');
			// normalize into key->value array
			$users = array();
			foreach($rows as $row) {
				$users[$row['id']] = $row['username'];
			}
			$done = array();
			$changed = array();
			foreach($users as $id => $username) {
				$un = strtolower(unix_name($username));
				if ($un !== $username) {
					$conn->Update('#__users', 'username=\''.$conn->Quote($un)."'", ' WHERE id='.$id);
					// update forum posts
					$conn->Update('#__forum_posts', 'name=\''.$conn->Quote($un)."'", ' WHERE userid='.$id);
					$em = $conn->SelectRow('#__users', 'email', ' WHERE id='.$id);
					$changed[] = array($username, $un, $em['email']);
				}
				if (isset($done[$un]))
					echo "ERROR: duplicate username $un ($id and ".$done[$un]."), not changed\n";
				$done[$un] = $id;
			}
			if (count($changed)) {
				echo "You are invited to notify the following users of their changed username<br/>";
				foreach($changed as $a) {
					list($orig_un, $new_un, $email) = $a;
					echo "* $orig_un -> $new_un ($email)<br/>";
				}
			}
			// update the registration package version
			$conn->Update('#__packages', 'version=\'0.2\'', ' WHERE type=\'component\' AND name=\'com_registration\'');
			// add the new table
			$dbbak->Rebase('auth_users');
			
			// tinyMCE editor is disabled
			$conn->Delete('#__drabots', ' WHERE element=\'tinymce2\'');
			$conn->Delete('#__drabots', ' WHERE name=\'tinymce2\' AND type=\'drabot\'');
			// fix package version
			$conn->Update('#__packages', 'version=\'1.1\'', ' WHERE type=\'module\' AND name=\'mod_getff\'');
			// fix broken logo.png icon reference
			$conn->Update('#__categories', 'image=\'laniuscms.png\'', ' WHERE image=\'logo.png\'');
			// fix a changed label
			$conn->Update('#__components', 'name=\'Google Sitemap\', admin_menu_alt=\'Google Sitemap\'', ' WHERE name=\'Create Google Sitemap\'');

}

?>
