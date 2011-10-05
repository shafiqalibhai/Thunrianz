<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Lanius CMS database upgrades
# @author legolas558
#
#

function lc_upgrade(&$conn, &$dbbak, $oldver) {
	switch ($oldver) {
		default: // to apply patches also to older versions
		case '0.5.0':
			echo '<h3>'._DB_COMPAT_FIX.' v0.5.0</h3>';
			if ($dbbak->IsRebased('modules'))
				$mc = $dbbak->Pop();
			else
				$mc = $conn->SelectArray('#__modules', 'id,message,params', ' WHERE module=\'content\'');
			// restore the category names
			$rows = $dbbak->Pop();
			foreach($rows as $row) {
				$conn->Update('#__categories', 'name=\''.sql_encode($row['title'])."'", ' WHERE id='.$row['id']);
			}

			// access level for single events
			$dbbak->Rebase('messages', 'receipts', 'faq', 'users', 'event', 'modules');
			// now put back the mod_content instances
			foreach($mc as $row) {
				if (strlen($row['params'])) {
					// unserialize if necessary
					if (strpos($row['params'], 'a:1:')===0)
						$p = unserialize($row['params']);
					else
						$p = _convert_old_params($row['params']);
				} else $p = array();
				$p['content'] = $row['message'];
				$conn->Update('#__modules', 'params=\''.sql_encode(serialize($p)).
					'\', iscore=1, module=\'mod_content\'', ' WHERE id='.$row['id']); $p = null;
			}
			// enable administrator users for contacts
			$ids = $conn->SelectColumn('#__users', 'id', ' WHERE gid=5');
			foreach($ids as $id) {
				$row = $conn->SelectRow('#__contacts', 'userid', ' WHERE userid='.$id);
				if (empty($row))
					$conn->Insert('#__contacts', '(userid,flags)', $id.',3');
			}
			// pick the first administrator user
			$admin_id = $ids[0];
			// give all messages to current user
			$ids = $conn->SelectColumn('#__messages', 'id');
			foreach($ids as $id) {
				$conn->Insert('#__receipts', '(userid,message_id)', $admin_id.','.$id);
			}
			// convert all 'com_contact' and 'com_messages' component instances to the new 'com_message'
			$conn->Update('#__components',
				'name=\'Message\',link=\'option=message\',option_link=\'com_message\'',
				' WHERE option_link=\'com_contact\' OR option_link=\'com_messages\'');
			// get the ids of com_message instances
			$ids = $conn->SelectColumn('#__components', 'id', ' WHERE option_link=\'com_message\'');
			// reduce all component records to 1
			if (count($ids)>1) {
				$first = array_shift($ids);
				$conn->Update('#__menu', 'link=\'index.php?option=message\', componentid='.$first, ' WHERE link_type=\'component\' AND ('.
						each_id($ids, 'componentid').')');
				$conn->Delete('#__components', ' WHERE '.each_id($ids));
			} else $fist = $ids[0];
			$conn->Update('#__menu', 'link=\'index.php?option=message\'', ' WHERE link_type=\'component\' AND componentid='.$first);

			// add the new menu item to usermenu
			$conn->Execute("INSERT INTO #__menu (menutype,name,link,link_type,parent,componentid,sublevel,ordering,browsernav,access,params)
VALUES ('usermenu','My inbox','index.php?option=message&task=inbox','url',0,0,0,1,0,1,'')");
			// add the new msg_options drabot
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'msg_options', '0.1')");
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('Message user options','core','msg_options','',0,32,0,'')");

			// enable FCKeditor if loosing tinyMCE2
			$row = $conn->SelectRow('#__drabots', 'access', ' WHERE element=\'tinymce2\'');
			if (!empty($row) && ($row['access'] <= 1)) {
				$row = $conn->SelectRow('#__drabots', 'access', ' WHERE element=\'fckeditor\'');
				if ($row['access'] == 9)
					$conn->Update('#__drabots', 'access=0', ' WHERE element=\'fckeditor\'');
			}
			// tinyMCE2 was wrongly added in new installations
			$conn->Delete('#__drabots', ' WHERE element=\'tinymce2\'');
			$conn->Delete('#__packages', ' WHERE name=\'tinymce2\' AND type=\'drabot\'');

			// add new drabots
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'faq_news', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'downloads_news', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'gallery_news', '0.1')");
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'weblinks_news', '0.1')");
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('FAQ admin news','admin_news','faq_news','',0,33,0,'')");
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('Downloads admin news','admin_news','downloads_news','',0,34,0,'')");
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('Gallery admin news','admin_news','gallery_news','',0,35,0,'')");
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('Weblinks admin news','admin_news','weblinks_news','',0,36,0,'')");
			//TODO: set ALL remoteblog instances to level 9

			// convert 'Europe/London' to '' and $lang == $d_deflang to ''
			global $d_deflang;
			// wildly assume that when default language is set to English then users were created with its settings by default
			if ($d_deflang == 'en') {
				$conn->Update('#__users', 'lang=\'\'', ' WHERE lang=\''.$d_deflang.'\'');
				$conn->Update('#__users', 'timezone=\'\'', ' WHERE timezone=\'Europe/London\'');
			}
			// fix a bug in option_link of com_about
			$conn->Update('#__menu', 'link=\'index.php?option=about\'',
					' WHERE link=\'index.php?index.php?option=about\'');
			$conn->Update('#__components', 'link=\'option=about\'', ' WHERE option_link=\'com_about\'');
			// create a hash for poster ids and display names
			$posters = array();
			foreach($conn->SelectArray('#__forum_posts', 'id,name,userid', ' WHERE id<>0') as $prow) {
				$uid = $prow['userid'];
				if (!isset($posters[$uid]))
					$posters[$uid] = current($conn->SelectRow('#__users', 'name', ' WHERE id='.$uid));
				if ($prow['name'] != $posters[$uid])
					$conn->Update('#__forum_posts', 'name=\''.sql_encode($posters[$uid]).'\'', ' WHERE id='.$prow['id']);
			}
			foreach($conn->SelectArray('#__forum_topics', 'id,name,userid', ' WHERE id<>0') as $prow) {
				$uid = $prow['userid'];
				if (!isset($posters[$uid]))
					$posters[$uid] = current($conn->SelectRow('#__users', 'name', ' WHERE id='.$uid));
				if ($prow['name'] != $posters[$uid])
					$conn->Update('#__forum_topics', 'name=\''.sql_encode($posters[$uid]).'\'', ' WHERE id='.$prow['id']);
			}
			echo "Fixed forum posters display names<br/>";
			// fix URLs in content items
			$rs = $conn->Select('#__content', 'id,introtext,bodytext', ' WHERE introtext LIKE \'%src=%\' OR bodytext LIKE \'%href=%\' OR introtext LIKE \'%href=%\' OR bodytext LIKE \'%src=%\' OR introtext LIKE \'%SRC=%\' OR bodytext LIKE \'%HREF=%\' OR introtext LIKE \'%HREF=%\' OR bodytext LIKE \'%SRC=%\'');
			do {
				$row = $rs->GetArray();
				if ($rs->EOF)
					break;
				$conn->Update('#__content', 'introtext=\''.sql_encode(_fix_urls($row['introtext'])).
					'\', bodytext=\''.sql_encode(_fix_urls($row['bodytext'])).'\'', ' WHERE id='.$row['id']);
			} while (!$rs->EOF);
			echo "Fixed content items absolute URLs<br/>";
			// remove remainings of dummy com_rss
			$ids = $conn->SelectColumn('#__components', 'id', ' WHERE link=\'option=rss\'');
			foreach($ids as $id) {
				$conn->Delete('#__menu', ' WHERE componentid='.$id);
				$conn->Delete('#__components', ' WHERE id='.$id);
			}
			// new drastream drabot
			$conn->Update('#__packages', 'version=\'0.2\'', ' WHERE type=\'module\' AND name=\'mod_stream\'');
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'drastream', '0.1')");
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('Stream drabot','content','drastream','',9,37,0,'')");
			// change label of default user profile link
			$ids = $conn->SelectColumn('#__menu', 'id', ' WHERE menutype=\'usermenu\' and name=\'Edit user profile\' AND link=\'index.php?option=user\'');
			if (count($ids))
				$conn->Update('#__menu', 'name=\'My user profile\'', ' WHERE '.each_id($ids));
			// add new drabot
			$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'forum_profile', '0.1')");
			$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES ('Forum profile options','core','forum_profile','',0,38,0,'')");
			// remove unwanted topmenu news
			$unwanted = array( array('index.php?option=content&task=section&id=2', 'cs', 'com_content'),
							array('index.php?option=message', 'component', 'com_message'),
							array('index.php?option=weblinks', 'component', 'com_weblinks')
						);
			foreach($unwanted as $row) {
				list($url, $lt, $com) = $row;
				$row = $conn->SelectRow('#__menu', 'id', ' WHERE menutype=\'topmenu\' AND link=\''.$url.'\' AND link_type=\''.$lt.'\'');
				if (!empty($row)) {
					$id = $row['id'];
					$row = $conn->SelectRow('#__components', 'id', ' WHERE option_link=\''.$com.'\'');
					$row = $conn->SelectRow('#__menu', 'id', ' WHERE link_type=\''.$lt.'\' AND componentid='.$row['id']);
					$conn->Update('#__menu', 'link_type=\'cl\', componentid=0, link=\''.$row['id'].'\'', ' WHERE id='.$id);
				}
			}

		// fallback always wanted
		case '0.5.1':
			echo '<h3>'._DB_COMPAT_FIX.' v0.5.1</h3>';
			// remove dead forum profiles
			$f_ids = $conn->SelectColumn('#__forum_users', 'id');
			$ids = $conn->SelectColumn('#__users', 'id');
			$ids = array_diff($f_ids, $ids); $f_ids = null;
			if (count($ids)) {
				$ids = each_id(array_values($ids));
				$conn->Delete('#__forum_users', ' WHERE '.$ids);
				$conn->Update('#__forum_topics', 'userid=0', ' WHERE '.$ids);
				$conn->Update('#__forum_posts', 'userid=0', ' WHERE '.$ids);
			}
		case '0.5.2':
			echo '<h3>'._DB_COMPAT_FIX.' v0.5.2</h3>';
			// this is repeated for v0.5.2 because of a bug affecting Gladius DB
			$row = $conn->SelectRow('#__packages', 'name', ' WHERE name=\'captchasi\'');
			if (empty($row)) {
				// retrieve all currently active poll categories
				$ids = $conn->SelectColumn('#__categories', 'id', ' WHERE section=\'com_polls\'');
				if (count($ids)) {
					$wsql = ' WHERE pollid NOT IN ('.implode(',', $ids).')';
					// delete dead polls data (there shouldn't be any)
					$conn->Delete('#__polls_data', $wsql);
					// delete dead polls votes
					$conn->Delete('#__polls_votes', $wsql); $wsql = null;
				} $ids = null;
				//TODO: fixes for new [list] in forums bbcode
				$conn->Update('#__packages', 'version=\'0.3\'', ' WHERE type=\'module\' AND name=\'mod_newsfeed\'');
				$dbbak->Rebase('auth_users', 'content_comment');
				// fix bad published status in comments
				$rows = $conn->SelectArray('#__content_comment', 'id,published', ' WHERE published>2');
				foreach($rows as $row) {
					$op = (int)$row['published'];
					switch ($op) {
						case 3:
							$npriv = '0';
							$npub = '2';
						break;
						case 4:
							$npriv = '1';
							$npub = '0';
						break;
						case 5:
							$npriv = '1';
							$npub = '2';
						break;
					}
					$conn->Update('#__content_comment', 'private='.$npriv.', published='.$npub, ' WHERE id='.$row['id']);
				}
				// set these modules as non-core
				$nc = array('mod_polls', 'mod_eventcal', 'mod_stream', 'mod_downloads', 'mod_validate', 'mod_getff');
				foreach($nc as $m) {
					$row = $conn->SelectRow('#__modules', 'id', ' WHERE module=\''.$m.'\'');
					if (!empty($row))
						$conn->Update('#__modules', 'iscore=0', ' WHERE id='.$row['id']);
				}
				// add captchasi plugin
				$conn->Execute("INSERT INTO #__drabots (name,type,element,showon,access,ordering,iscore,params) VALUES('CAPTCHA Security Images','captcha','captchasi','',0,39,0,'')");
				$conn->Execute("INSERT INTO #__packages (type, name, version) VALUES('drabot', 'captchasi', '0.1')");
				// update filelog version
				$conn->Update('#__packages', 'version=\'0.2\'', ' WHERE type=\'drabot\' AND name=\'filelog\'');
			}
			// fix backported from 0.6.0 and repeated in 0.5.2 restores
			// fix all the serialized params for bad CRLF sequences
			// no CRLF check was performed on submitted data before 0.6.0
			CRLF_serial_fix('menu', $conn->SelectArray('#__menu', 'id,params', ' WHERE params<>\'\''));
			CRLF_serial_fix('modules', $conn->SelectArray('#__modules', 'id,params', ' WHERE params<>\'\''));
			CRLF_serial_fix('drabots', $conn->SelectArray('#__drabots', 'id,params', ' WHERE params<>\'\''));

			// nothing to do, this is the last version
	}
	return true;
}

function CRLF_serial_fix($table, $rows) {
	global $conn, $d__utf8_unsafe;
	$d__utf8_unsafe = true;
	$i = 0;
	foreach($rows as $row) {
		$p = @unserialize($row['params']);
		// unserialization failed, let's check for bad CRLF sequences
		if ($p === false) {
				$row['params'] = str_replace("\r\n", "\n", $row['params']);
				$p = @unserialize($row['params']);
				if ($p === false) {
					echo "Could not fix parameters serialization for row $i of table $table, data reset<br>";
					$row['params'] = '';
				}
				$conn->Update('#__'.$table, 'params=\''.sql_encode($row['params']).'\'', ' WHERE id='.$row['id']);
				echo "Fixed parameters serialization for row $i of table $table<br />";
		}
		++$i;
	}
	$d__utf8_unsafe = false;
}

?>
