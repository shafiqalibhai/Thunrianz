<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Forum component for Lanius CMS
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# This file contains the functions used by other renderer functions
#

define('__FORUM_MAX_WORD_LENGTH', 100);

function __wrap_single_word($word) {
	$l=strlen($word);
	$in_ent = 0;
	$real_l = 0;
	for($i=0;$i<$l;++$i) {
		// we are not in entity
		if (!$in_ent) {
			// increment once even for entities
			++$real_l;
			// check for entity start
			if ($word[$i] == '&') {
				++$in_ent;
				continue;
			}
			// check if real length has reached maximum allowed
			if ($real_l >= __FORUM_MAX_WORD_LENGTH) {
				// we are not inside entity, chop here
				return substr($word, 0, $i)."\n".__wrap_single_word(substr($word, $i));
			}
			// maximum word length not yet reached
			continue;
		}
		// we are inside an entity, check for entity end
		if ($word[$i] == ';') {
			$in_ent = 0;
			continue;
		}
		++$in_ent;
	}
	return $word;
}

function _layout_wrap_cb($m) {
	return __wrap_single_word($m[0]);
}

## splits the message so that does not break the layout with scrollbars
function layout_wrap($s, $columns = __FORUM_MAX_WORD_LENGTH) {
	return preg_replace_callback('/[^\s\\/]{'.($columns+1).',}/', '_layout_wrap_cb',
							wordwrap($s, $columns));
}

function html_forum_date($ts) {
	return lc_strftime(_FORUM_DATE_FORMAT_LC, $ts);
}

function remove_bb($s) {
	return preg_replace('/\\[.+?\\]/','',$s);
}

global $forum_moderators;
$forum_moderators = array();

//global $master_moderators;
//$master_moderators = array_map('trim', explode(',',$params->get('moderators'));

function get_thread_id($post_id) {
	global $conn;
	$crow=$conn->SelectRow('#__forum_posts', 'thread_id', ' WHERE id='.$post_id);
	if (!count($crow)) return 0;
	$thread_id=$crow['thread_id'];
	if($thread_id==0) return $post_id;
	return $thread_id;
}

## estabilish if an user can post into a topic
function can_user_post($edit_gid, $locked, $catid) {
	global $my;
	// managers and above can post anywhere
	if ($my->gid>3)
		return true;
	// if the forum is locked, moderator rules apply
	if ($locked) return is_forum_moderator($catid, $my->username);
	
	// otherwise the edit group id is respected
	return ($my->gid>=$edit_gid);
}

function can_user_post_into_cat($catid) {
	global $conn, $access_sql;
	// retrieve topic data
	$row = $conn->SelectRow('#__forum_categories', 'editgroup,locked', ' WHERE id='.$catid.' '.$access_sql);
	if (!count($row)) {
		global $d;
		CMSResponse::Unauthorized();
		return false;
	}

	// check if user has enough priviledges to post
	if (!can_user_post($row['editgroup'], $row['locked'], $catid)) {
		global $d;
		CMSResponse::Unauthorized();
		return false;
	}
	
	return true;
}

function notify_change($user_notify, $topic_id) {
	global $conn,$my;
	//L: update notification flag
	if ($user_notify) {
		if (!get_db_notify($topic_id))
			$conn->Insert('#__forum_notifies', '(user_id, topic_id)', $my->id.', '.$topic_id);
	} else
		$conn->Delete('#__forum_notifies', ' WHERE user_id='.$my->id.' AND topic_id='.$topic_id);
}

function &get_forum_moderators($catid) {
	global $forum_moderators;
	if (!isset($forum_moderators[$catid])) {
		global $conn;
		$row=$conn->SelectRow('#__forum_categories', 'moderators', ' WHERE id='.$catid.' AND moderators<>\'\'');
		if (!$row) {
			$forum_moderators[$catid] = array();
			return $forum_moderators[$catid];
		}
		$forum_moderators[$catid] = array_map('trim', explode(',',$row['moderators']));
	}
	//return array_merge($GLOBALS['master_moderators'], $forum_moderators[$catid]);
	return $forum_moderators[$catid];
}

// returns true if $name is a moderator/admin
function is_forum_moderator($catid,$name) {
	if (!strlen($name))
		return false;
	// query the global array of forum moderators for that category id
	$catid=(int)$catid;
	return in_array($name, get_forum_moderators($catid));
}

## estabilish if an user can edit a post, given the post category id ($catid) and the category locked setting
function can_user_edit($author_id, $catid, $locked) {
	global $my;

	// anonymous users cannot edit any post
	if (!$my->gid) return false;
	if ($author_id==0) {
		// managers and admins are always moderators
		if ($my->gid>=4) return true;
		// anonymous posts are subject to moderators rules
		return is_forum_moderator($catid, $my->username);
	}
	global $conn;
	// get user group id (GID)
	$row = $conn->SelectRow('#__users', 'gid',
			' WHERE id='.$author_id);
	if (count($row))
		$gid = $row['gid'];
	else
	// if the user no more exists, its group id is one below manager (Publisher) so that only moderators can handle it
		$gid = 3;
	// if the group id is greater, edit is not possible
	if ($gid>$my->gid)
		return false;
	// if the group is equal and the author id is different, or if the topic is locked, allow edit only to moderators
	else if ((($gid==$my->gid) && ($author_id!=$my->id)) || $locked) {
		if ($my->gid>=4)
			return true;
		return is_forum_moderator($catid, $my->username);
	}
	return true;
}

function find_last_post($catid) {
	global $conn;
	// find the latest post for the $catid category
	$row = $conn->SelectRow('#__forum_posts', 'id', ' WHERE catid='.$catid.' ORDER BY time DESC');
	if (!count($row))
		$pid = 0;
	else
		$pid = $row['id'];
	return $pid;
}

## get the POSTed notify setting
function get_notify($thread_id=0) {
	// retrieve the flag
	$notify = in_num('notify', $_POST);
	// if found, return its boolean representation
	if (isset($notify)) return ($notify!=0);
	global $my;
	// if the user is not logged in, he cannot get notifications
	if (!$my->id)	return false;
	// if this was not a POST
	if (empty($_POST)) {
		// ?
		if (!$thread_id) return true;
		// retrieve the setting from database
		return get_db_notify($thread_id);
	}
	// this is a POST and we couldn't get the flag
	return false;
}

global $user_db_notify;
function get_db_notify($topic_id) {
	global $conn,$my,$user_db_notify;
	if (!isset($user_db_notify)) {
		$usr = $conn->SelectRow('#__forum_notifies', 'user_id', ' WHERE user_id='.$my->id.' AND topic_id='.$topic_id);
		$user_db_notify = (count($usr)!=0);
	}
	return $user_db_notify;
}

function get_user_info($id) {
	global $conn, $my;
	$row1=$conn->SelectRow('#__users', '*',
		" WHERE id=".$id);
	if (!count($row1))
		return array('posts' => 0, 'image' => 'default.png', 'registerDate' => 0, 'signature' => '', 'gid' => 0);
	$row2=$conn->SelectRow('#__forum_users', '*',
		" WHERE id=".$id);
	
	if(!count($row2)){
		$conn->Insert('#__forum_users', '(id,information,url,posts)', "$id,'','',1");
		$row2=$conn->SelectRow('#__forum_users', '*', " WHERE id=".$id);
	}
	return array_merge($row1,$row2);
}

function sanitize($text) {
	global $params;
	//replace bad words
	$arr=array_map('trim', explode(",",$params->get('bad_words')));
	$c=count($arr);
	if ($c) return $text;
	$filtered = array();
	foreach($arr as $word) {
		$filtered[] = '/'.str_replace('/', '\\/', preg_quote($word)).'/i';
	}
	return preg_replace($filtered, $params->get('rep_word'), $text);
}

function forum_category_access($catid) {
	global $conn, $my, $access_sql;
	$row = $conn->GetRow("SELECT id FROM #__forum_categories WHERE locked=0 AND id=$catid $access_sql");
	return (count($row)!=0);
}

// does the user have moderator rights over this post?
function moderator_rights($catid, $post_uid, $row_locked) {
	global $my;
	return ($my->gid>=4 || is_forum_moderator($catid, $my->username) || can_user_edit($post_uid,$catid,$row_locked));
}

## returns an array of categories where a topic can be moved into
function get_move_targets() {
	global $conn, $my;
	$rows = $conn->SelectArray('#__forum_categories', 'id,name', ' WHERE access<='.$my->gid.' AND editgroup<='.$my->gid);
	$targets = array();
	foreach ($rows as $row) {
		$targets[(int)$row['id']] = $row['name'];
	}
	return $targets;
}

/*
	if catid == -1 then no category is pre-selected
	if parent_id == -1 then include also sections
	if parent_id == 0 include categories of invisible section 0 (forum categories without parent section)
	if ro == false then show only unlocked/writable categories
*/
function _write_forum_categories ($catid, $ro = true, $parent_id = -1) {
	global $access_sql, $edit_sql;
	$cat_access = $ro ? $access_sql : $edit_sql;
	if ($parent_id != -1) {
		__write_forum_subcategories('', $catid, $cat_access, $parent_id);
	} else {
		global $conn;
		$rsa = $conn->SelectArray('#__forum_categories', 'id,name'," WHERE id<>".$catid.' '.$cat_access);
		foreach($rsa as $sect) {
			echo '<option value="'.$sect['id'].'">'.$sect['name']."</option>";
			__write_forum_subcategories('&nbsp;&nbsp;&nbsp;&nbsp;', $catid, $cat_access, $sect['id']);
		}
	}
}

function __write_forum_subcategories($tab, $catid, $cat_access, $parent_id) {
	global $conn;
	$rsa=$conn->SelectArray('#__forum_categories', 'id,name', ' WHERE parent_id='.$parent_id.' '.$cat_access);
	foreach($rsa as $row) {
		// the category name is already encoded
		echo '<option value="'.$row['id'].'"';
		if ($row['id'] == $catid)
			echo ' selected="selected"';
		echo '>'.$tab.$row['name']."</option>";
	}
}

function get_post_cat($post_id, $cat_extra = '', $post_extra = '') {
	global $conn;
	$crow = $conn->SelectRow('#__forum_posts', 'catid'.$post_extra, ' WHERE id='.$post_id);
	if (!$crow) {
		global $d;
		CMSResponse::NotFound();
		return false;
	}
	global $access_sql;
	$row = $conn->SelectRow('#__forum_categories', 'id,name'.$cat_extra, ' WHERE id='.$crow['catid'].
						' '.$access_sql);
	if (!$row) {
		global $d;
		CMSResponse::Unauthorized();
		return false;
	}
	if (strlen($post_extra))
		return array($crow, $row);
	return $row;
}

global $has_forum_js;
$has_forum_js = false;
function bbcode_editor($label, $form_item_name, &$content, $enable_bb_img = false, $compact = false, $cols=64, $rows=16 ) {
	global $d_subpath, $has_forum_js;
	if (!$has_forum_js) {
		global $d;
		$d->add_js('components/forum/forum.js');
		$has_forum_js = true;
	}
?>
		  <tr>
			<td valign="top">&nbsp;</td>
			<td><a href='javascript:DoPrompt("<?php echo $form_item_name; ?>","url");'><img src="<?php echo $d_subpath; ?>components/forum/images/bburl.png" alt="Web Address" border="0" hspace="1"/></a> <a href='javascript:DoPrompt("<?php echo $form_item_name; ?>","email");'><img src="<?php echo $d_subpath; ?>components/forum/images/bbemail.png" alt="Email Address" hspace="1" border="0"/></a> <a href='javascript:DoPrompt("<?php echo $form_item_name; ?>","bold");'><img src="<?php echo $d_subpath; ?>components/forum/images/bbbold.png" alt="Bold Text" border="0" hspace="1" /></a> <a href='javascript:DoPrompt("<?php echo $form_item_name; ?>","italic");'><img src="<?php echo $d_subpath; ?>components/forum/images/bbitalic.png" alt="Italic Text" border="0" hspace="1"/></a> <a href='javascript:DoPrompt("<?php echo $form_item_name; ?>","underline");'><img src="<?php echo $d_subpath; ?>components/forum/images/bbunderline.png" alt="Underlined Text" border="0" hspace="1"/></a> <a href='javascript:DoPrompt("<?php echo $form_item_name; ?>","quote");'><img src="<?php echo $d_subpath; ?>components/forum/images/bbquote.png" alt="Quote" border="0" hspace="1"/></a> <a href='javascript:DoPrompt("<?php echo $form_item_name; ?>","code");'><img src="<?php echo $d_subpath; ?>components/forum/images/bbcode.png" alt="Code" border="0" hspace="1"/></a>
			<?php
			if ($enable_bb_img) {?>
			<a href='javascript:DoPrompt("<?php echo $form_item_name; ?>","image");'><img src="<?php echo $d_subpath; ?>components/forum/images/image.png" alt="Image" border="0" hspace="1"/></a>			<?php }
			if (!$compact) {
				global $d;
				$d->add_raw_js('function smile(type) {
var f=document.forms.postForm;
f.post_message.value = f.post_message.value + " " + type + " ";
f.post_message.focus();
}');
				echo '<hr />';
				global $d_root;
				include $d_root.'includes/smileys.php';
			} ?></td>
		  </tr>
		  <tr>
			<td valign="top"><?php echo $label;?></td>
			<td><textarea name="<?php echo $form_item_name; ?>" cols="<?php echo $cols; ?>" rows="<?php echo $rows; ?>" class="dk_inputbox" id="<?php echo $form_item_name; ?>"><?php echo $content; ?></textarea></td>
		  </tr><?php
}

?>