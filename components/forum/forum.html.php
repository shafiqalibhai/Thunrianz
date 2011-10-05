<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Forum component for Lanius CMS
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#

## post a new reply in a topic - called after the actual POST
function post_new_reply($catid,$post_id,$thread_id,$notify) {
	global $conn, $access_sql, $Itemid, $my, $time, $d;

	if (!can_user_post_into_cat($catid)) {
		CMSResponse::Unauthorized();
		return null;
	}
	
	$row=$conn->SelectRow('#__forum_topics', 'post_count,post_list,locked', " WHERE id=$thread_id");
	if (!can_user_post(1, $row['locked'], $catid)) {
		CMSResponse::Unauthorized();
		return null;
	}
	
	$post_count=$row['post_count']+1;
	$post_list=$row['post_list'];
	
	// if the user is registered use its account name
	if($my->isuser()) {
		$post_name = $my->name;
		$post_userid = $my->id;
		// increase the number of posts
		change_val('forum_users', $post_userid, 'posts');
	} else {
		$post_name = in('post_name', __NOHTML,$_POST, '',100);
		$post_userid=0;
	}
	// insert the actual data into the database
	$post_subject = in('post_subject', __NOHTML,$_POST,'',255);
	$post_message = in('post_message', __NOHTML,$_POST);
	$conn->Insert('#__forum_posts', '(thread_id,parent_id,catid,name,userid,subject,message,time,ip)',
		"$thread_id, $post_id, $catid, '".sql_encode($post_name)."', $post_userid, '".sql_encode($post_subject)."', '".sql_encode($post_message)."', $time, '".$my->GetIP()."'");

	$post_dbid=$conn->Insert_ID();

	$row=$conn->SelectRow('#__forum_categories', 'id,post_count,name', " WHERE id=$catid");
	$ct_post_count=$row['post_count']+1;
	$this_forum=$row['name'];
	$conn->Update('#__forum_categories', "checked_out=$post_dbid,post_count=$ct_post_count", " WHERE id=$catid");

	$post_list .= '_'.$post_dbid.'_';

	$conn->Update('#__forum_topics', "time=$time,checked_out=$post_dbid,post_count=$post_count,post_list='$post_list'", " WHERE id=$thread_id");
	
	global $conn,$my,$d_root;
	notify_change($notify, $thread_id);

	//L: notify the users
	$users = $conn->SelectColumn('#__forum_notifies', 'user_id',
				' WHERE topic_id='.$thread_id.' AND user_id<>'.$my->id);
	if (isset($users[0])) {
		include $d_root.'classes/gelomail.php';
		$m = new GeloMail();
		global $d_title, $d_website, $d__req;
		$answ_url = $d_website.$d__req.'?option=forum&task=viewpost&catid='.$catid.'&post_id='.$thread_id.'&Itemid='.$Itemid;
		// create the message
		//TODO: check about '.' on single lines - somewhere
		$message = array('_FORUM_POST_NOTIFICATION_MSG',
					$d_title.' <'.$d_website.'>',
					safe_html_entity_decode(spam_filter($post_subject)), $this_forum,
					$post_name, bbcode_to_text(spam_filter($post_message)),
					$answ_url);
		$emails = $langs = array();
		$m->GetRecipients($users, $emails, $langs);
		$m->I18NSend($emails, $langs, array('_FORUM_POST_NOTIFICATION', $d_title), $message, 'forum');
	}
	
	return $post_dbid;
}

## creates a new topic
function post_new_topic($catid,$notify) {
	global $conn, $access_sql, $Itemid, $my, $time;

	if (!can_user_post_into_cat($catid))
		return;
		
	if($my->gid>0) {
		$post_name=$my->name;
		$post_userid=$my->id;
		// increase number of posts
		change_val('forum_users', $post_userid, 'posts');
	} else {
		$post_name= in('post_name', __SQL|__NOHTML,$_POST, '',100);
		$post_userid=0;
	}
	
	$post_subject = in('post_subject', __NOHTML,$_POST,'',255);
	$post_message = in('post_message', __NOHTML,$_POST, '');

	$conn->Insert('#__forum_posts', "(catid,name,userid,subject,message,time,ip)",
			"$catid, '" . $post_name ."', $post_userid, '"
			. sql_encode($post_subject) ."', '"
			. sql_encode($post_message) ."', '$time', '".$my->GetIP()."'");

	$post_dbid = $conn->Insert_ID();

	$conn->Insert('#__forum_topics', '(id,catid,name,userid,subject,time,post_list)',
				"$post_dbid, $catid, '".$post_name."', $post_userid, '"
			. in('post_subject', __SQL|__NOHTML,$_POST,'',255) ."', '$time', '_".$post_dbid."_'");

	$row=$conn->SelectRow('#__forum_categories', 'id,topic_count', " WHERE id=$catid");
	$topic_count=$row['topic_count']+1;
	$conn->Update('#__forum_categories', "checked_out=$post_dbid,topic_count=$topic_count", " WHERE id=$catid");

	// update notification flag
	if ($notify)
		$conn->Insert('#__forum_notifies', '(user_id, topic_id)', $my->id.','.$post_dbid);
	
	global $params, $d_root;
	// notify the moderators/administrators about this new topic
	if ($params->get('notify_new_topics', 1)) {
		// if there are no moderators set, email the admins
		$moderators = get_forum_moderators($catid);
		$emails = $langs = array();
		foreach($moderators as $username) {
			$row = $conn->SelectRow('#__users', 'email,lang', ' WHERE username=\''.sql_encode($username)."'");
			if (isset($row['email'])) {
				$langs[] = $my->ValidLang($row['lang']);
				$emails[] = $row['email'];
			}
		}
		include_once $d_root.'classes/gelomail.php';
		$m = new GeloMail();
		global $d_website;
		if (!count($langs)) {
			$recp = $m->notify_list();
			$emails = $recp[0];
			$langs = $recp[1];
			$recp = null;
		}
		global $d__req;
		$post_url = $d__req.'?option=forum&task=viewpost&catid='.$catid.'&post_id='.$post_dbid.'&Itemid='.$Itemid;
		if (!$my->id)
			$post_auth = '-';
		else
			$post_auth = '#'.$my->id;
		
		$crow = $conn->SelectRow('#__forum_categories', 'name', ' WHERE id='.$catid);
		
		$message = array('_FORUM_NEW_TOPIC_MESSAGE',
			$crow['name'],
			safe_html_entity_decode($post_subject),
			$my->username, $post_auth,
			bbcode_to_text(spam_filter($post_message)),
			'<'.$d_website.$post_url.'>');
		
		$m->I18NSend($emails, $langs, array('_FORUM_NEW_TOPIC_SUBJECT', safe_html_entity_decode($crow['name'])), $message, 'forum');
	}
}

function delete_thread($post_id, &$post_row, &$cat_row) {
	global $conn, $d, $my;
	
	$pdel = $conn->Count('SELECT post_count FROM #__forum_topics WHERE id='.$post_id);

	// if there is some discussion normal users cannot delete the topic & the thread
	if ($pdel && $my->gid<=3) {
		if (!is_forum_moderator($post_row['userid'], $post_row['catid'] /*, $cat_row['locked']*/)) {
			CMSResponse::Unauthorized();
			return null;
		}
	}
		
		// update the posts count for each user which has posted
		$users=$conn->GetColumn('SELECT userid FROM #__forum_posts WHERE userid<>0 AND thread_id='.$post_id);
		$deltas = array();
		foreach ($users as $uid) {
			if (!isset($deltas[$uid])) {
				$row = $conn->GetRow('SELECT posts FROM #__forum_users WHERE id='.$uid);
				$deltas[$uid] = $row['posts']-1;
			} else
				$deltas[$uid]--;
		}
		foreach ($deltas as $uid => $delta) {
			$conn->Update('#__forum_users', 'posts='.$delta, " WHERE id=$uid");
		}

		// delete the top post
		$conn->Delete('#__forum_posts', ' WHERE id='.$post_id);
		// delete the topic and the notifications
		$conn->Delete('#__forum_topics', ' WHERE id='.$post_id);
		// delete the associated notifications
		$conn->Delete('#__forum_notifies', ' WHERE topic_id='.$post_id);
		// delete the thread
		$conn->Delete('#__forum_posts', ' WHERE thread_id='.$post_id);
		// below line is commented because it is unnecessary if the database fields are up to date
//	$pdel=$conn->Affected_Rows();
	//TODO: optimize SQL below
	// update the count fields
	change_val("forum_categories",$post_row['catid'],"topic_count",-1);
	change_val("forum_categories",$post_row['catid'],"post_count",-$pdel);
	
	// if this post was the last reply of the category, update the category
	// must be executed AFTER the deletion of the posts
	if ($cat_row['checked_out']==$post_id) {
		$pid = find_last_post($post_row['catid']);
		$conn->Update('#__forum_categories', 'checked_out='.$pid, ' WHERE id='.$post_row['catid']);
	}

	return 0;
}

function _admin_fn($prop) {

	$post_id = in_num('post_id');
	if (!isset($post_id))
		return;
	$catid = in_num('catid');
	if (!isset($catid))
		return;


	global $conn,$my,$d,$access_sql;
	
	// get useful informations about this post
	$post_row = $conn->SelectRow('#__forum_posts', 'thread_id,catid,userid', ' WHERE id='.$post_id);
	if (!count($post_row)) {
		// if the post does not exist
		CMSResponse::NotFound();
		return;
	}
	// get useful informations about the forum category of this post
	$cat_row = $conn->SelectRow('#__forum_categories', 'editgroup,locked,checked_out', ' WHERE id='.$post_row['catid'].' '.$access_sql);
	if (!count($cat_row)) {
		// if the forum category does not exist or was not accessible
		CMSResponse::Unauthorized();
		return;
	}
	
	// if the user cannot edit the post, he can't delete too
	if (!can_user_edit($post_row['userid'], $post_row['catid'], $cat_row['locked'])) {
		CMSResponse::Unauthorized();
		return;
	}
	
	if ($post_row['thread_id']) {
		echo 'The specified post_id is not a thread';
		return;
	}
	// if this post has no thread_id, then it is the first post of a thread
	$conn->Update('#__forum_topics', $prop, ' WHERE id='.$post_id);
	
	global $Itemid, $d__req;
	CMSResponse::ResponseRedir("option=forum&task=viewpost&catid=$catid&post_id=$post_id&Itemid=$Itemid");
}

function delete_post($post_id) {
	global $conn,$my,$d,$access_sql;
	
	// get useful informations about this post
	$post_row = $conn->SelectRow('#__forum_posts', 'thread_id,catid,userid',  ' WHERE id='.$post_id);
	if (!count($post_row)) {
		// if the post does not exist
		CMSResponse::NotFound();
		return null;
	}
	// get useful informations about the forum category of this post
	$cat_row = $conn->SelectRow('#__forum_categories', 'editgroup,locked,checked_out',
							' WHERE id='.$post_row['catid'].' '.$access_sql);
	if (!count($cat_row)) {
		// if the forum category does not exist or was not accessible
		CMSResponse::Unauthorized();
		return null;
	}
	
	// if the user cannot edit the post, he can't delete too
	if (!can_user_edit($post_row['userid'], $post_row['catid'], $cat_row['locked'])) {
		CMSResponse::Unauthorized();
		return null;
	}
	
	if (!$post_row['thread_id']) {
		//CHECK: should not arrive from this part, or can?
		// if this post has no thread_id, then it is the first post of a thread
		return delete_thread($post_id, $post_row, $cat_row);
	}
	
	// it's a simple post

	// check if we are deleting the last post of a thread
	// we cannot check post_id==thread_id because the thread can exist also without head
	$crow=$conn->SelectRow('#__forum_topics', 'post_count,post_list,checked_out', " WHERE id=".$post_row['thread_id']);
/*		// if the thread_id matches the post_id, then this is the first reply in a post
		if ($post_row['thread_id']==$post_id)	*/
		if ($crow['post_count']==0)
			//CHECK: shouldn't be two delete_thread() entry points...
			return delete_thread($post_row['thread_id'], $post_row, $cat_row);

		// update the posts list
		$post_list=str_replace('_'.$post_id.'_','',$crow['post_list']);
		
		// delete the post for this user
		if ($post_row['userid'])
			change_val('forum_users', $post_row['userid'], 'posts', -1);
		
		change_val("forum_categories",$post_row['catid'],"post_count", -1);
		$conn->Delete('#__forum_posts', ' WHERE id='.$post_id);
		
		$post_count=$crow['post_count']-1;
		
		// now update the checked_out field of the current topic
		if ($crow['checked_out']==$post_id) {
			// find the last post for this thread
			if (!preg_match('/(\\d+)_$/', $post_list, $m))
				$pid = 0;
			else
				$pid=(int)$m[1];
			$co=",checked_out=$pid ";
		} else {$co='';$pid=null;}
		$conn->Execute("UPDATE #__forum_topics SET post_list='$post_list',post_count=$post_count".
						$co." WHERE id=".$post_row['thread_id']);

		// if this post was the last reply of the category, update the category
		if ($cat_row['checked_out']==$post_id) {
			if (!isset($pid)) {
				if (!preg_match('/(\\d+)_$/', $post_list, $m))
					$pid = 0;
				else
					$pid=(int)$m[1];
			}
			$conn->Execute('UPDATE #__forum_categories SET checked_out='.$pid.' WHERE id='.$post_row['catid']);
		}

	return $post_row['thread_id'];
}

function forum_search($parent_id = 0) {
global $Itemid,$conn,$searchword, $d, $d__req;

$d->add_raw_js('
function val_search()
{
 if(document.searchform.searchword.value == "")
	{
	alert(\''.js_enc(_FORM_NC).'\');
	return false;
	}
	return true;
}');
?><div align="right"><form name="searchform" method="get" action="<?php echo $d__req; ?>" onsubmit="return val_search();">
			<input type="hidden" name="option" value="forum" />
			<input type="hidden" name="task" value="search" />
			<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
			<input name="searchword" type="text" class="dk_inputbox" size="10" value="<?php if(isset($searchword))echo xhtml_safe($searchword); ?>" />&nbsp;<input type="submit" value="<?php echo _FORUM_SEARCH;?>" class="dk_button" />&nbsp;<select class="dk_inputbox" name="select" onchange="if(this.value=='')return;window.location='<?php echo $d__req; ?>?option=forum&amp;task=category&amp;Itemid=<?php echo $Itemid;?>&amp;catid='+this.value;">
				<option value="" selected="selected"><?php echo _FORUM_JUMP;?></option>
				<?php
				_write_forum_categories(-1);
				?>
			  </select>
			</form>
		</div>
<?php
}

function show_forum_categories($rsa) {
	global $d, $Itemid, $access_sql, $d_subpath, $conn, $d__req;
	$topic_total=0;
	$post_total=0;
	$titles = '';
	foreach($rsa as $row) {
		$titles.=' '.$row['name'];
		?>
	  <tr class="dkcom_tablerow<?php
	  if(!isset($row_color) || $row_color==1)$row_color=0;
	   else $row_color=1;
	   echo ($row_color)?"1":"2";
	   
		if ($row['locked'])
			$catpic = 'category_locked.png';
		else {
			$catpic = 'category.png';
		}
	  ?>">
	<td><img src="<?php echo $d_subpath; ?>components/forum/images/<?php echo $catpic; ?>" hspace="5" vspace="5" alt=""/></td>
		<td><span class="l"><a href="<?php echo $d__req; ?>?option=forum&amp;task=category&amp;catid=<?php echo $row['id']; ?>&amp;Itemid=<?php echo $Itemid.content_sef($row['name']); ?>" class="l"><strong><?php echo $row['name']; ?></strong></a></span><br/>
		  <?php echo $row['description']; ?></td>
		<td align="center"><?php echo $row['topic_count'];
	$topic_total+=$row['topic_count'];
	?>
		</td>
		<td align="center"><?php echo $row['post_count'];
	$post_total+=$row['post_count'];
	?></td><?php
		show_last_post($row['id'], $row['checked_out']);
		?>
	  </tr>
	  <?php
	  }?>
	  <tr class="dkcom_tablerow<?php
	  if(!isset($row_color) || $row_color==1)$row_color=0;
	   else $row_color=1;
	   echo ($row_color)?"1":"2";
	  ?>">
	<td>&nbsp;</td>
		<td><?php echo _FORUM_TOTAL; ?></td>
		<td align="center"><?php echo $topic_total;?></td>
		<td align="center"><?php echo $post_total;?></td>
		<td>&nbsp;</td>
	  </tr>
<?php
	$d->add_meta('', content_keywords(sanitize($titles)));
}

function view_categories($sec_id = null) {
	global $conn,$Itemid,$access_sql, $params, $pathway;
	if (isset($sec_id)) {
		$row = $conn->SelectRow('#__categories', 'id,name,description', ' WHERE section=\'com_forum\' '.$access_sql.' and id='.$sec_id);
		if (!count($row)) {
			global $d;
			CMSResponse::Unauthorized();
			return;
		}
		$srsa = array($row);
		$title = $row['name'];
		$pathway->add($title);
	} else $title = _FORUM_TITLE;	
	global $d__req;
	?>
	<div class="dk_header"><h2><?php echo $title;?></h2></div>
	<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" class="dk_content">
	  <tr>
		<td colspan="5"><?php echo $params->get('desc');?></td>
	  </tr>
	  <tr>
		<td colspan="5"><table width="100%"  border="0" cellpadding="0" cellspacing="0">
		  <tr>
			<td>&nbsp;</td>
			<td nowrap="nowrap"><div align="right">
				<?php forum_search((int)$sec_id);?>
			</div></td>
		  </tr>
		</table></td>
	  </tr>
	  <tr class="dkcom_tableheader">
	<td class="dkcom_tableheader">&nbsp;</td>
		<td class="dkcom_tableheader" width="60%"><?php echo _FORUM_TITLE;?></td>
		<td class="dkcom_tableheader" width="10%" align="center"><?php echo _FORUM_TOPICS;?></td>
		<td class="dkcom_tableheader" width="10%" align="center"><?php echo _FORUM_REPLIES;?></td>
		<td class="dkcom_tableheader" width="20%" align="center"><?php echo _FORUM_LAST;?></td>
	  </tr>
		<?php
		global $access_sql;
		if (!isset($sec_id)) {
			$rsa=$conn->SelectArray('#__forum_categories', '*', " WHERE parent_id=0 $access_sql ORDER BY ordering");
			if (count($rsa))
				show_forum_categories($rsa);

			$srsa = $conn->SelectArray('#__categories', 'id,name,description', ' WHERE section=\'com_forum\' '.$access_sql.' ORDER BY ordering');
			
			global $d_subpath;
			foreach ($srsa as $row) { ?>
				<tr><td><img src="<?php echo $d_subpath; ?>components/forum/images/section.png" hspace="5" vspace="5" alt="[ ]"/></td><td colspan="4"><h3><a href="<?php echo $d__req; ?>?option=forum&amp;task=section&amp;sec_id=<?php echo $row['id'].'&amp;Itemid='.$Itemid.content_sef($row['name']); ?>"><?php echo $row['name']; ?></a></h3></td></tr>
			<?php
				// show the description (if any)
				if (strlen($row['description'])) {?>
				<tr><td colspan="5"><?php echo $row['description']; ?></td></tr>
				<?php
				}
				// expand the sections inline

				if ($params->get('expanded_sections')) {
					$rsa = $conn->SelectArray('#__forum_categories', '*', ' WHERE parent_id='.$row['id'].' '.$access_sql.' ORDER BY ordering');
					show_forum_categories($rsa);
				}
			}
		} else { // display categories of selected section
			$rsa = $conn->SelectArray('#__forum_categories', '*' ,' WHERE parent_id='.$sec_id.' '.$access_sql.' ORDER BY ordering');
			show_forum_categories($rsa);
		}

	  ?>
	  <tr>
		<?php if (!isset($sec_id)) { ?>
		<td colspan="5">&nbsp;</td>
		<?php } else { ?>
		  <td><input name="button" type="button" class="dk_button" onclick="window.location='<?php echo $d__req; ?>?option=forum&amp;Itemid=<?php echo $Itemid;?>'" value="<?php echo _FORUM_BACK;?>" />
			</td><td colspan="4">&nbsp;</td>
		<?php } ?>
	  </tr>
	</table>
	<?php
	if ($params->get('feeds', 1)) {
		global $d;
		$d->InlineModule('syndicate', 'option=forum&task=feed');
	}
}

function show_last_post($id, $co) {
	global $conn, $Itemid, $d_subpath; ?>
		<td align="center" nowrap="nowrap"><?php if ($co!=0) {
	 	$prow=$conn->SelectRow('#__forum_posts', 'id,name,time,userid', " WHERE id=".$co);
		if (!$prow) { // for debugging purposes
			global $d;
			$d->log(8, 'Invalid post id: '.$co);
			echo 'Invalid post id: '.$co;
		} else {
		global $d__req;
		echo html_forum_date($prow['time'])."<br />"._FORUM_BY." ";
		if ($prow['userid']) echo '<a href="'.$d__req.'?option=user&amp;task=details&amp;id='.encode_userid($prow['userid']).'">';
		echo $prow['name'];
		if ($prow['userid']) echo '</a>';
?>&nbsp;<a href="<?php echo $d__req; ?>?option=forum&amp;task=viewpost&amp;catid=<?php echo $id; ?>&amp;post_id=<?php echo $co.'&amp;Itemid='.$Itemid.'#p'.$co; ?>"><img src="<?php echo $d_subpath; ?>components/forum/images/newest_reply.png" border="0" alt="<?php echo _FORUM_LAST; ?>"/></a><?php
		}
		//select * from _topics
		}else echo _FORUM_POST_NONE;
		?></td>
	<?php
}

function show_forum_topics($rsa) {
	global $conn, $d_subpath, $Itemid, $d__req;
	$row_color=1;
	$titles='';
	foreach($rsa as $row) {
		$titles.=' '.$row['subject'];
	?>
	  <tr class="dkcom_tablerow<?php
		if($row_color==1) $row_color=2;
		else $row_color=1;
		echo $row_color;
		
		if ($row['sticked'])
			$toppic = 'sticked.png';
		else if ($row['locked'])
			$toppic = 'topic_locked.png';
		else {
			//TODO: use newposts.png
			$toppic = 'oldposts.png';
		}
		
	  ?>">
	<td><img src="<?php echo $d_subpath; ?>components/forum/images/<?php echo $toppic; ?>" hspace="5" vspace="5" alt=""/></td>
		<td><a href="<?php echo $d__req; ?>?option=forum&amp;task=viewpost&amp;catid=<?php echo $row['catid']; ?>&amp;post_id=<?php echo $row['id']; ?>&amp;Itemid=<?php echo $Itemid.content_sef(sanitize($row['subject'])); ?>"><?php echo sanitize($row['subject']); ?></a></td>
		<td align="center"><?php echo sanitize($row['name']); ?></td>
		<td  align="center"><?php echo $row['post_count']; ?></td>
		<td align="center"><?php echo $row['hits']; ?></td>
<?php	show_last_post($row['catid'], $row['checked_out']);	?>
		</tr>
	  <?php
	}
	global $d;
	$d->add_meta('', content_keywords(sanitize($titles)));
}

function show_category($catid) {
	global $conn,$Itemid,$access_sql,$d_title,$d_subpath,$Itemid,$params, $d, $my, $pathway;

	$row=$conn->SelectRow('#__forum_categories', 'name,description,editgroup,locked,parent_id', " WHERE id=$catid $access_sql");
	if(!count($row)) return;
	$cat_name=$row['name'];

	forum_section_pathway($row['parent_id']);

	$pathway->add($cat_name, "option=forum&catid=$catid&Itemid=$Itemid");

	global $d__req;
?>
<div class="dk_header"><h2><?php echo $row['name']; ?></h2></div>
<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" class="dk_content">
   <tr>
	<td colspan="6"><?php echo $row['description']; ?></td>
  </tr>
  <tr><td>
  <input name="button" type="button" class="dk_button" onclick="window.location='<?php echo $d__req; ?>?option=forum&amp;Itemid=<?php echo $Itemid;?>'" value="<?php echo _FORUM_BACK;?>" />
  </td>
		<td colspan="5" nowrap="nowrap" align="right">
		  <?php forum_search($row['parent_id']);?>
</td>
  </tr>
  <tr class="dkcom_tableheader">
	<td width="20" class="dkcom_tableheader">&nbsp;</td>
	<td width="50%" class="dkcom_tableheader"  ><?php echo _FORUM_TOPICS;?></td>
	<td width="10%" class="dkcom_tableheader" align="center"><?php echo _AUTHOR;?>	  </td>
	<td width="10%" class="dkcom_tableheader" align="center"><?php echo _FORUM_REPLIES;?></td>
	<td width="10%" class="dkcom_tableheader" align="center"><?php echo _FORUM_VIEWS;?></td>
	<td width="20%" class="dkcom_tableheader" align="center"><?php echo _FORUM_LAST;?></td>
  </tr>
<?php

$show = $params->get('show_count', 10);

global $d_root;
include_once $d_root.'classes/pagenav.php';
$pn = new PageNav($show);

$rsa = $pn->Slice('#__forum_topics', '*', "WHERE catid=$catid", 'ORDER BY sticked DESC, time DESC');

show_forum_topics($rsa, $row['name']);

global $d__req;

?><tr>
		<td colspan="6" nowrap="nowrap"><?php 
		if (can_user_post($row['editgroup'], $row['locked'], $catid))
			$edit_attrs = 'onclick="window.location=\''.$d__req.'?option=forum&amp;task=newtopic&amp;catid='.$catid.'&amp;Itemid='.$Itemid.'\'"';
		else
			$edit_attrs = 'disabled="disabled"';
			
		?><input name="button" type="button" class="dk_button" <?php echo $edit_attrs; ?> value="<?php echo _FORUM_NEW_TOPIC;?>" />
</td>
	  </tr>
	<tr><td colspan="6" align="center">&nbsp;<?php echo $pn->NavBar("option=forum&amp;catid=$catid&amp;task=category&amp;Itemid=$Itemid");	?></td></tr>
	<tr>
<td></td>
<td>&nbsp;</td>
<td colspan="4"></td>
  </tr>
</table>
<?php

	if ($params->get('feeds', 1)) {
		global $d;
		$d->InlineModule('syndicate', 'option=forum&task=feed&catid='.$catid);
	}


}

function forum_results($head_cb, $param, $show_count, $posts_arr, $nav_url) {
global $conn,$Itemid,$access_sql,$d_title,$d_subpath,$Itemid,$params, $d,$pathway, $d_root;
?>
<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" class="dk_content">
  <tr>
	<td colspan="6"><table width="100%"  border="0" cellpadding="0" cellspacing="0">
	  <tr>
		<td>&nbsp;</td>
		<td nowrap="nowrap"><?php $head_cb($param); ?></td>
	  </tr>
	</table></td>
  </tr>
  <tr class="dkcom_tableheader">
	<td width="20" class="dkcom_tableheader">&nbsp;</td>
	<td width="50%" class="dkcom_tableheader"><?php echo _FORUM_TOPICS;?></td>
	<td width="10%" class="dkcom_tableheader" align="center"><?php echo _AUTHOR;?>	  </td>
	<td width="10%" class="dkcom_tableheader" align="center"><?php echo _FORUM_REPLIES;?></td>
	<td width="10%" class="dkcom_tableheader" align="center"><?php echo _FORUM_VIEWS;?></td>
	<td width="20%" class="dkcom_tableheader" align="center"><?php echo _FORUM_LAST;?></td>
  </tr><?php
  
$topics = array();
foreach($posts_arr as $row) {
	if ($row['thread_id'])
		$thid = $row['thread_id'];
	else
		$thid = $row['id'];
		
	if (!in_array($thid, $topics))
		$topics[] = $thid;
} unset($posts_arr);

	include_once $d_root.'classes/pagenav.php';
	$pn = new PageNav($show_count);


foreach($topics as $thid) {
	$row = $conn->GetRow('SELECT * FROM #__forum_topics WHERE id='.$thid);
	if (count($row) && forum_category_access($row['catid'])) {
		if (!$pn->QueryAdd($row))
			break;
	}
}

$searcharr = $pn->QueryArray();

if (isset($searcharr[0])) {
	show_forum_topics($searcharr, _FORUM_SEARCH);
?>
<tr><td colspan="6" align="center">&nbsp;<?php echo $pn->NavBar($nav_url);?></td></tr>
  <?php
  
  }
?>
	<tr>
<td></td>
<td>&nbsp;</td>
<td colspan="4"></td>

  </tr>
</table>
<?php
}

function search_forum($searchword) {
	global $pathway, $conn, $params, $Itemid;

	$pathway->add(_FORUM_SEARCH, "option=forum&task=search&Itemid=$Itemid");

	$stricken_words = $common_words = array();
	$posts_arr=$conn->SelectArray('#__forum_posts', 'id,thread_id', ' WHERE '.
							search_query(array('subject', 'message'), $searchword,"any", $common_words,$stricken_words).
							' ORDER BY time DESC');

	?><div class="dk_header"><h2><?php echo _FORUM_SEARCH;?></h2></div><?php
	forum_results('forum_search', null, $params->get('show_count',10), $posts_arr, 'option=forum&amp;searchword='.rawurlencode($searchword).'&amp;task=search&amp;Itemid='.$Itemid);
}

function unread_posts_head() {
	?><h2><?php echo _FORUM_NEW_POSTS;?></h2><?php
}

function user_posts_head($name) {
	?><h2><?php echo sprintf(_FORUM_VIEW_USER_POSTS_LIST, $name);?></h2><?php
}


function forum_section_pathway($parent_id) {
	if ($parent_id) {
		global $conn, $access_sql, $Itemid, $pathway;
		$srow = $conn->SelectRow('#__categories', 'name', ' WHERE id='.$parent_id.' '.$access_sql);
		$pathway->add($srow['name'], 'option=forum&task=section&sec_id='.$parent_id.'&Itemid='.$Itemid);
	}
}

function view_post($catid,$post_id,$review=false) {
	global $conn,$Itemid,$access_sql,$d_title,$my,$d, $d_subpath, $pathway;
	$mod=false;

	$row = get_post_cat($post_id, ',editgroup,locked,parent_id');
	if (!$row) {
		CMSResponse::NotFound();
		return;
	}
	if (!isset($catid) || ($row['id']!=$catid)) {
		CMSResponse::Move('Itemid='.$Itemid.'&option=forum&task=viewpost&catid='.
					$row['id'].'&post_id='.$post_id);
		return;
	}
	$row=$conn->GetRow("SELECT name,editgroup,locked,parent_id FROM #__forum_categories WHERE id=$catid $access_sql");
	if (!count($row)) {
		CMSResponse::Unauthorized();
		return;
	}
	$cat_name=$row['name'];

	forum_section_pathway($row['parent_id']);
	
	$pathway->add($cat_name, "option=forum&task=category&catid=$catid&Itemid=$Itemid");

	$topic_cache=$conn->GetRow("SELECT * FROM #__forum_topics WHERE id=$post_id");
	if (!count($topic_cache)) {
		$crow=$conn->GetRow("SELECT thread_id FROM #__forum_posts WHERE id=".$post_id);
	//	$hl = $post_id;
		$orig_post_id = $post_id;
		$post_id = $crow['thread_id'];
		$topic_cache = $conn->GetRow("SELECT * FROM #__forum_topics WHERE id=$post_id");
	} // else $hl = 0;
	 else $orig_post_id = $post_id;
	$hl = -1;

	if(!$review) {
		$hits=$topic_cache['hits']+1;
		$conn->Execute("UPDATE #__forum_topics SET hits=$hits WHERE id=$post_id");
	}
	
	$pathway->add(sanitize($topic_cache['subject']), "option=forum&task=viewpost&catid=$catid&post_id=$post_id&Itemid=$Itemid");

	$first = true;
	global $d__req;

	$d->add_css('components/forum/bbcstyle.css');	?>
	<a name="top"></a>
	<table width="100%" border="0" align="center" cellpadding="3" cellspacing="1" class="dk_content">
	  <?php if(!$review) { ?>
	  <tr>
		<td colspan="2">&nbsp;</td>
	  </tr>
	  <tr>
			<td nowrap="nowrap">
			  <input type="button" class="dk_button" onclick="window.location='<?php echo $d__req; ?>?option=forum&amp;task=category&amp;catid=<?php echo $catid;?>&amp;Itemid=<?php echo $Itemid;?>'" value="<?php echo _FORUM_BACK;?>" /></td>
			  <td nowrap="nowrap" align="right">
	<?php forum_search($row['parent_id']);?>
			  </td>
	  </tr>
	  <?php }
	  
	// start buffering the viewpost slice
	ob_start();
	
	//L: get the paging configuration
	global $params, $d_root;
	include_once $d_root.'classes/pagenav.php';
	$show_count = $params->get('show_count',10);
	$pn = new PageNav($show_count);

	$row_color=1;
	if (preg_match_all('/\\d+/', $topic_cache['post_list'], $list)) {
	
		// exploit the position of the selected post
		$pn->SetItemIndex($orig_post_id);

		//L: pick currently selected part of the matches (all post ids)
		$list = $pn->ArraySlice($list[0]);

		foreach($list as $pid) {
			if ($pid == $hl)
				$row_color = 3;
			else {
				if ($row_color == 1)
					$row_color = 2;
				else
					$row_color = 1;
			}

			$topic_reply = $conn->SelectRow('#__forum_posts', '*',' WHERE id='.$pid);
			if (empty($topic_reply)) {
				//trigger_error('Cannot select topic reply for post id '.$pid);
				continue;
			}
			
			if ($first) {	// add the keywords using the first post
				$first = false;
				$d->add_meta(xhtml_safe(html_to_text(remove_bb($topic_reply['message']))));
			}
			
		$mod = moderator_rights($catid, $topic_reply['userid'], $row['locked'] );
	?><tr class="dkcom_tablerow<?php echo $row_color;?>">
		<td class="dkcom_tablerow<?php echo $row_color;?>" align="center"><a name="p<?php echo $topic_reply['id'];?>"></a><?php if ($topic_reply['userid']) { ?><a href="<?php echo $d__req; ?>?option=user&amp;task=details&amp;id=<?php echo encode_userid($topic_reply['userid']); ?>"><?php } echo sanitize($topic_reply['name']); ?><?php if ($topic_reply['userid']) { ?></a><?php } ?></td>
		<td class="dkcom_tablerow<?php echo $row_color;?>"><table width="100%"  border="0" cellpadding="0" cellspacing="0">
			<tr>
			  <td><strong><?php echo sanitize($topic_reply['subject']); ?></strong></td>
			  <td><div align="right"><?php echo html_forum_date($topic_reply['time']); ?></div></td>
			</tr>
		</table></td>
	  </tr>
	  <tr class="dkcom_tablerow<?php echo $row_color;?>">
		<td align="left" valign="top" class="dkcom_tablerow<?php echo $row_color;?>">
	<?php
	
		$is_top = ($topic_cache['id']==$topic_reply['id']);
		//L: show some general statistics about the user
		if ($topic_reply['userid']) {
			$user_info=get_user_info($topic_reply['userid']);
			if (!strlen($user_info['image']))
				$pic = 'default.png';
			else
				$pic = $user_info['image'];
			?><img src="media/forum/avatars/<?php echo $pic; ?>" border="1" alt="<?php echo xhtml_safe(basename($pic)); ?>" /><?php
			
			echo '<br />'._FORUM_POSTS.' '.$user_info['posts'].'<br />';
			echo _FORUM_MEMBER_SINCE.' '.html_forum_date($user_info['registerDate']);
			echo "<br />";
			$signature = $user_info['signature'];
			$author_gid = $user_info['gid'];
		} else { echo _ANONYMOUS; $signature = ''; $author_gid = 0; }
	?></td>
		<td valign="top" class="dkcom_tablerow<?php echo $row_color;?>"><?php
		// here it is the main output code
		echo sanitize(bbdecode(layout_wrap($topic_reply['message']), true, true,
						$params->get('enable_bb_img', 1)) );
		
		if (strlen($signature)) echo '<hr />'.bbdecode(layout_wrap($signature), true, true, $params->get('enable_bb_img', 1));
		?></td>
	  </tr>
	<?php if(!$review) { ?>
	  <tr class="dkcom_tablerow<?php echo $row_color;?>">
		<td class="dkcom_tablerow<?php echo $row_color;?>" align="center"><?php
		$can_report = (!$mod && can_user_post($row['editgroup'], $row['locked'], $catid));
		if ($can_report && $topic_reply['userid'] && $user_info['gid'])
			// should have used $topic_reply['name'] but this method is safer for eventual nickname changes
			if ($author_gid>=4 || is_forum_moderator($catid, $user_info['username']))
				$can_report = false;
		if ($can_report) {?>
			<a href="<?php echo $d__req; ?>?option=forum&amp;catid=<?php echo $catid;?>&amp;post_id=<?php echo $topic_reply['id'];?>&amp;task=report&amp;Itemid=<?php echo $Itemid;?>"><?php echo _FORUM_REPORT_TO_MODERATORS;?></a>		<?php } else echo '&nbsp;'; ?></td>
		<td align="left" class="dkcom_tablerow<?php echo $row_color;?>"><div align="right"><?php
		if($mod) {
			$d->add_unique_js('post_delete', 'function post_delete(catid, postid) {
				if (confirm("'.js_enc(_FORUM_DELETE_CONFIRM).'"))
				document.location = "'.$d__req.'?option=forum&catid="+catid+"&post_id="+postid+"&task=delete&Itemid='.$Itemid.'";
				return false;
				}');
			if ($is_top && ($my->gid>=$author_gid)) { ?>
	<a href="<?php echo $d__req; ?>?option=forum&amp;catid=<?php echo $catid;?>&amp;post_id=<?php echo $topic_reply['id'];?>&amp;task=move&amp;Itemid=<?php echo $Itemid;?>"><?php echo _FORUM_MOVE; ?></a>&nbsp;&nbsp;
	<a href="<?php echo $d__req; ?>?option=forum&amp;catid=<?php echo $catid;?>&amp;post_id=<?php echo $topic_reply['id'];?>&amp;task=<?php
	if ($topic_cache['locked']) echo 'un'; ?>lock&amp;Itemid=<?php echo $Itemid;?>"><?php echo $topic_cache['locked'] ? _FORUM_UNLOCK:_FORUM_LOCK; ?></a>&nbsp;&nbsp;
	<a href="<?php echo $d__req; ?>?option=forum&amp;catid=<?php echo $catid;?>&amp;post_id=<?php echo $topic_reply['id'];?>&amp;task=<?php
	if ($topic_cache['sticked']) echo 'un'; ?>stick&amp;Itemid=<?php echo $Itemid;?>"><?php echo $topic_cache['sticked'] ? _FORUM_UNSTICK:_FORUM_STICK; ?></a>&nbsp;&nbsp;<?php
	}
	if (can_user_post(max($row['editgroup'],$author_gid), $topic_cache['locked'], $catid)) { ?>
	<a href="<?php echo $d__req; ?>?option=forum&amp;catid=<?php echo $catid;?>&amp;post_id=<?php echo $topic_reply['id'];?>&amp;task=edit&amp;Itemid=<?php echo $Itemid;?>"><?php echo _FORUM_EDIT;?></a>&nbsp;&nbsp;
	<a href="#p<?php echo $topic_reply['id']; ?>" onclick="post_delete(<?php echo $catid.', '.$topic_reply['id']; ?>)"><?php echo $is_top?_FORUM_DELETE_THREAD:_DELETE;?></a>&nbsp;&nbsp;<?php
	}
	} // end of moderator IF
	if (can_user_post($row['editgroup'], $row['locked'], $catid)) {	?>
			<a href="<?php echo $d__req; ?>?option=forum&amp;catid=<?php echo $catid;?>&amp;post_id=<?php echo $topic_reply['id'];?>&amp;task=postreply&amp;Itemid=<?php echo $Itemid;?>"><?php echo _FORUM_REPLY;?></a>&nbsp;&nbsp;
		   <a href="<?php echo $d__req; ?>?option=forum&amp;catid=<?php echo $catid;?>&amp;post_id=<?php echo $topic_reply['id'];?>&amp;quote=1&amp;task=postreply&amp;Itemid=<?php echo $Itemid;?>"><?php echo _FORUM_QUOTE;?></a> 
	<?php } ?></div></td></tr><?php
	} // not $review

		} // iteration for each two posts
	} // post_list != ''
	
	$ct = ob_get_clean();
	  if ($pn->TotalPages()>1) { ?>
	  <tr><td colspan="2" align="center">&nbsp;<?php echo $pn->NavBar("option=forum&task=viewpost&catid=$catid&post_id=$post_id&Itemid=$Itemid&review=".($review ? 1 : 0));?></td></tr>
	<?php }	  ?>
	  <tr class="dkcom_tableheader">
		<td width="120" class="dkcom_tableheader">
		  <?php echo _AUTHOR;?>
		</td>
		<td  align="left" class="dkcom_tableheader"><?php echo _FORUM_MESSAGE;?></td>
	  </tr>
<?php
	// dump out slice of viewpost
	echo $ct; $ct = null;
	?>
	<tr>
		<td align="center"><a href="#top">
			<?php echo _FORUM_TOP;?>
		</a></td><td align="center">
	<?php echo $pn->NavBar("option=forum&task=viewpost&catid=$catid&post_id=$post_id&Itemid=$Itemid&review=".($review ? 1 : 0));?>
		</td>
	  </tr>
	  <tr>
		  <td nowrap="nowrap" colspan="2"><?php
			  if (can_user_post($row['editgroup'], $row['locked'], $catid))
				$edit_attrs = 'onclick="window.location=\''.$d__req.'?option=forum&amp;catid='.$catid.'&amp;post_id='.$post_id.'&amp;task=postreply&amp;Itemid='.$Itemid.'\'"';
			else
				$edit_attrs='disabled="disabled"';
	?><input name="button2" type="button" class="dk_button" <?php echo $edit_attrs; ?> value="<?php echo _FORUM_POST_REPLY;?>" />&nbsp;
			  <?php
			  
				if (!$review && $my->id!=0) {
					if (get_db_notify($post_id))
						echo '<a href="'.$d__req.'?option=forum&amp;task=notify&amp;catid='.$catid.'&amp;post_id='.$post_id.'&amp;notify=0"><img src="'.
								$d_subpath.'components/forum/images/mbopen.png" alt="Yes" border="0" />&nbsp;'.
								_FORUM_NOTIFY_DISABLE.'</a>';
					else if (!$row['locked'])
						echo '<a href="'.$d__req.'?option=forum&amp;task=notify&amp;catid='.$catid.'&amp;post_id='.$post_id.'&amp;notify=1"><img src="'.
								$d_subpath.'components/forum/images/mbclosed.png" alt="Yes" border="0" />&nbsp;'.
								_FORUM_NOTIFY_ENABLE.'</a>';
					
				}
			  
			  ?></td>
		</tr>
	</table>
	<?php
}

// general-purpose function used to render a post form
function post_form($cat_name,$edit_gid,$ptask,$preview=false,$notify=false,$post_id=null) {
	global $d,$my, $conn,$catid,$Itemid,$d_title,$task,$time,$d_subpath,$params;

	// check if the user is allowed to post here
	if ($my->gid<$edit_gid) {
		CMSResponse::Unauthorized();
		return;
	}
	
	// quoting flag
	$quote = (in_raw('quote') != 0);
	
	$sub=$msg=$nam='';
	$thread_id=0;
	// if we are replying to a certain previous post
	if (isset($post_id)) {
		if ($quote)
			$usid = ',name';
		else $usid = '';
		$crow=$conn->SelectRow('#__forum_posts',
				'thread_id,subject,message,catid'.$usid, ' WHERE id='.$post_id);
		global $params;
		// the "Re:" prefix is not internationalized, but parametrized
		$forum_re = $params->get('re_prefix', 'Re:');
		if (strpos($crow['subject'],$forum_re)!==0)
			$sub=$forum_re.' '.$crow['subject'];
		else
			$sub=$crow['subject'];
		$thread_id=$crow['thread_id'];
		if($thread_id==0) $thread_id=$post_id;
		if($quote) {
			//TODO: some escaping of user name?
			$msg="[quote=".$crow['name']."]\n".$crow['message']."\n[/quote]";
		}
		if($task=="edit"){
			$msg=$crow['message'];
			$sub=$crow['subject'];
		}
	}
	
	// if we are previewing, get again the posted message
	if ($preview)  {
		$msg = in('post_message', __NOHTML, $_POST, '');
		$sub = in('post_subject', __NOHTML, $_POST, '', 255);
		if ('' === ($nam = in('post_name', __NOHTML, $_POST, '', 100)))
			$nam = $my->name;
	}

	global $d, $d__req;
	$d->add_css('components/forum/bbcstyle.css');
?><form name="postForm" method="post" action="<?php echo $d__req; ?>?option=forum" id="postForm" onsubmit="return post_validate()">
  <table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" class="dk_content">
	<?php if($preview) { ?>
	<tr class="dkcom_tableheader">
	  <td class="dkcom_tableheader">
<?php echo _FORUM_PREVIEW;?></td></tr>
	<tr>
	  <td><table width="100%"  border="0">
  <tr class="dkcom_tablerow1">
	<td width="120" class="dkcom_tablerow1" align="center"><strong><?php echo xhtml_safe($nam);?></strong>&nbsp;</td>
	<td class="dkcom_tablerow1"><table width="100%"  border="0" cellpadding="0" cellspacing="0">
	  <tr>
		<td><strong><?php echo sanitize($sub);?></strong></td>
		<td><div align="right"><?php echo html_forum_date($time); ?></div></td>
	  </tr>
	</table></td>
  </tr>
  <tr class="dkcom_tablerow1">
	<td class="dkcom_tablerow1">&nbsp;</td>
	<td class="dkcom_tablerow1"><?php echo sanitize(bbdecode(layout_wrap($msg), true, true,
								$params->get('enable_bb_img', 1))); ?></td>
  </tr>
</table>
</td></tr>
	<?php }	?>
	<tr class="dkcom_tableheader">
	  <td class="dkcom_tableheader">
		<input name="catid" type="hidden" value="<?php echo $catid;?>" />
		<input name="task" type="hidden" value="<?php echo $ptask;?>" />
		<input name="thread_id" type="hidden" value="<?php echo $thread_id;?>" />
		<input name="preview" type="hidden" value="0" />
		<input name="post_id" type="hidden" value="<?php echo $post_id;?>" />
		<input name="Itemid" type="hidden"  value="<?php echo $Itemid;?>" />
<script language="javascript" type="text/javascript">
function post_validate() {
	var f = document.postForm;
	// do field validation
	<?php if($my->gid==0){?>
	if (f.post_name.value == ""){
		alert( '<?php echo js_enc(_FORUM_JS_ENTER_NAME); ?>' );
		f.post_name.focus();
	} else <?php }?> if (f.post_subject.value == "") {
		alert( '<?php echo js_enc(_FORUM_JS_ENTER_SUBJECT); ?>' );
		f.post_subject.focus();
	} else if (f.post_message.value == ""){
		alert( '<?php echo js_enc(_FORUM_JS_ENTER_MESSAGE); ?>' );
		f.post_message.focus();
	} else {
		return true;
	}
	return false;
}
</script>

<strong><?php
if(isset($post_id)){
if($task=="edit")echo _FORUM_POST_EDIT;
else echo sprintf(_FORUM_POST_REP,$crow['subject']);
}
else echo sprintf(_FORUM_POST_NEW,$cat_name);

?></strong></td>
	</tr>
	<tr>
	  <td>		<table  border="0" cellpadding="3" cellspacing="1">
	<?php 	if(!$my->gid){?>
		  <tr>
			<td><?php echo _FORUM_NAME;?></td>
			<td><input name="post_name" type="text" class="dk_inputbox" size="60" maxlength="100" value="<?php echo $nam;?>" /></td>
		  </tr>
<?php }?>
		  <tr>
			<td><?php echo _FORUM_SUBJECT;?></td>
			<td><input name="post_subject" type="text" class="dk_inputbox" id="post_subject" value="<?php echo sanitize($sub);?>"  size="60" maxlength="255" /></td>
		  </tr>
		  <?php bbcode_editor(_FORUM_MESSAGE, 'post_message', $msg, $params->get('enable_bb_img', 0)); ?>
				<tr>
					<td>&nbsp;</td><td><?php if ($my->gid==0) echo _FORUM_NOTIFY_NOT_LOGGED_IN; else { ?><label for="notify"><input type="checkbox" id="notify" name="notify"<?php if ($notify) echo ' checked="checked"'; ?> value="1" /><?php echo _FORUM_NOTIFY_SEND; ?></label><?php } ?></td>
				</tr>
		  <tr>
			<td>&nbsp;</td>
			<td><div align="right">
			  <table width="100%"  border="0" cellpadding="0" cellspacing="0">
				<tr>
				  <td><input type="submit" class="dk_button" name="b1" value="<?php echo _SUBMIT;?>" />
					  <input type="button" class="dk_button" name="b2" value="<?php echo _FORUM_PREVIEW;?>" onclick="javascript:document.postForm.preview.value=1;document.postForm.submit();" /></td>
				   <td><div align="right">
					  <input name="button4" type="button" class="dk_button" onclick="javascript:window.location='<?php echo $d__req; ?>?option=forum&amp;catid=<?php echo $catid;?>&amp;task=category&amp;Itemid=<?php echo $Itemid;?>';" value="<?php echo _CANCEL;?>" />
				  </div></td>
				</tr>
			  </table>
			</div></td>
		  </tr>
		</table>
	  </td>
	</tr>
<?php if(isset($post_id)) { ?>
	<tr class="dkcom_tableheader">
	  <td class="dkcom_tableheader"><?php echo _FORUM_REVIEW;?></td>
	</tr>
	<tr>
	  <td><iframe frameborder="0" width="100%" height="300" src="<?php echo $d_subpath;?>index2.php?option=forum&amp;task=viewpost&amp;catid=<?php echo $catid;?>&amp;post_id=<?php echo $post_id;?>&amp;Itemid=<?php echo $Itemid;?>&amp;review=1">
</iframe>&nbsp;</td>
	</tr>
<?php } ?>
  </table>
</form>
<?php
}

function new_topic($catid,$preview,$notify){
	global $Itemid,$conn,$access_sql,$my,$d,$pathway;
	if ($row=$conn->SelectRow('#__forum_categories',
		'name,editgroup', " WHERE id=$catid $access_sql")) {
		$cat_name=$row['name'];

		$pathway->add($cat_name, "option=forum&task=category&catid=$catid&Itemid=$Itemid");
		$pathway->add(_FORUM_POST_TOPIC, "option=forum&task=newtopic&catid=$catid&Itemid=$Itemid");
		post_form($cat_name,$row['editgroup'],"postnewtopic",$preview,$notify);
	}
}

function post_reply($catid,$id,$preview,$notify){
	global $Itemid,$conn,$access_sql,$my,$d,$pathway;
	// retrieve the forum cateory
	$row=$conn->SelectRow('#__forum_categories', 'name,editgroup', " WHERE id=$catid $access_sql");
	if (!count($row)) {
		CMSResponse::Unauthorized();
		return;
	}
	
	// add the permalink of this page
	$pathway->add($row['name'],"option=forum&task=category&catid=$catid&Itemid=$Itemid");
	$pathway->add(_FORUM_POST_REPLY);
	// render the correct post form
	post_form($row['name'],$row['editgroup'],"postnewreply",$preview,$notify,$id);
}

function edit_reply($catid, $postid, $cat_name, $cat_editgroup, $preview = false, $notify = false){
	global $Itemid,$conn,$access_sql,$d,$my,$pathway;
	
	$pathway->add($cat_name,"option=forum&task=category&catid=$catid&Itemid=$Itemid");
	$pathway->add(_FORUM_POST_EDIT);
	post_form($cat_name,$cat_editgroup, "editreply", $preview,$notify,$postid);
}

function report_post($post_id) {
	global $Itemid,$conn,$access_sql,$d,$my,$pathway;
	
	$crow = get_post_cat($post_id, ',editgroup,locked');
	if (!$crow)
		return;
	$catid = $crow['id'];
	
	if (!can_user_post($crow['editgroup'], $crow['locked'], $catid)) {
		CMSResponse::Unauthorized();
		return;
	}
	
	$pathway->add($crow['name'],"option=forum&task=category&catid=$catid&Itemid=$Itemid");
	$pathway->add(_FORUM_REPORT_TO_MODERATORS);

	global $d__req;
	// now render the report form
	?><form name="report_form" method="post" action="<?php echo $d__req; ?>?option=forum&amp;task=report_post&amp;post_id=<?php echo $post_id.'&amp;Itemid='.$Itemid; ?>">
	<div class="dk_header"><h2><?php echo _FORUM_REPORT_TO_MODERATORS;?></h2></div>
					<div style="margin-top: 1ex; margin-bottom: 3ex;" align="center"><?php echo _FORUM_REPORT_TO_MODERATORS_DESC; ?></div>
					<div align="center">
					<?php echo _FORUM_REPORT_COMMENT; ?>: <input name="report_comment" size="50" type="text" maxlength="256">
					<input value="<?php echo _SUBMIT; ?>" style="margin-left: 1ex;" type="submit">
				</div>
	</form>
	<?php
}

function move_thread($post_id) {
	global $Itemid,$conn,$access_sql,$d,$my,$pathway;
	
	$crow = get_post_cat($post_id, ',editgroup,locked',',userid,subject');
	if (!$crow)
		return;
	$catid = $crow[1]['id'];
	$post_uid = $crow[0]['userid'];
	$subject = $crow[0]['subject'];
	$ma = ' (MOVED)';
	if (strpos($subject, $ma)!==strlen($subject)-strlen($ma))
		$subject .= $ma;
	$crow = $crow[1];
	
	if (!moderator_rights($catid, $post_uid, $crow['locked'])) {
		CMSResponse::Unauthorized();
		return;
	}
	
	$pathway->add($crow['name'],"option=forum&task=category&catid=$catid&Itemid=$Itemid");
	$pathway->add(_FORUM_MOVE_THREAD);
	
	global $d__req;
	// now render the report form
	?><form name="movet_form" method="post" action="<?php echo $d__req; ?>?option=forum&amp;post_id=<?php echo $post_id.'&amp;Itemid='.$Itemid; ?>">
	<input type="hidden" name="task" value="move_thread" />
	<input type="hidden" name="move_source" value="<?php echo $post_id; ?>" />
	<div class="dk_header"><h2><?php echo _FORUM_MOVE_THREAD;?></h2></div>
		<p style="margin-top: 1ex; margin-bottom: 3ex;"><?php echo _FORUM_MOVE_THREAD_DESC; ?></p>
		<div align="center">
			<?php echo _FORUM_MOVE_INTO; ?>: <select name="move_destination">
			<?php
			_write_forum_categories($catid, false);
			?>
			</select><br />
			<?php echo _FORUM_MOVE_NEW_SUBJECT; ?>: <input size="50" name="move_subject" type="text" class="dk_input" value="<?php echo xhtml_safe($subject); ?>" /><br />
			<input value="<?php echo _SUBMIT; ?>" style="margin-left: 1ex;" type="submit" />
			<input value="<?php echo _CANCEL; ?>" style="margin-left: 1ex;" type="button" onclick="javascript:history.back(-1)" />
		</div>
	</form>
	<?php
}

?>
