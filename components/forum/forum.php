<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Forum component for Lanius CMS
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#

include com_path('functions');

include(com_path("html"));

$pathway->add_head(_FORUM_TITLE);

$task = in_raw('task', $_REQUEST, 'view');

$catid = in_num('catid'); //L: needs more checking

include $d_root.'includes/bbcode.php';

switch ( $task ){
	case "category":
		if (isset($catid))
			show_category($catid);
        break;
	case "newtopic":
		new_topic($catid,false,true);
        break;
	case "postnewtopic":
		$preview = in_num('preview', $_POST);
		$notify = get_notify();
		if ($preview) {
				new_topic($catid,$preview, $notify);
		} else {
			post_new_topic($catid,$notify);
			CMSResponse::Redir("index.php?option=forum&task=category&catid=$catid&Itemid=$Itemid");
		}
		break;
	case "search":
		$searchword = in_raw('searchword', $_GET, '');
		if (strlen($searchword))
			search_forum($searchword);
        break;

    case "notify":
		if (!$my->gid) {
			CMSResponse::Unauthorized();
			break;
		}
		$post_id = in_num('post_id');
		$notify = in_num('notify', $_GET);
		if (isset($post_id) && isset($notify)) {
			notify_change($notify, $post_id);
			$catid = in_num('catid');
			CMSResponse::Redir("index.php?option=forum&task=viewpost&catid=$catid&post_id=$post_id&Itemid=$Itemid");
		}
        break;
		
    case "viewpost":
		$post_id = in_num('post_id');
		if (isset($post_id))
			view_post($catid,$post_id, (in_num('review', $_GET) != 0));
        break;
    case "postreply":
		if (null !== ($post_id = in_num('post_id', $_GET)))
			post_reply($catid,$post_id,false, get_notify(get_thread_id($post_id)));
        break;
    case "postnewreply":
		if (null === ($post_id = in_num('post_id')))
			break;
		$preview = in_num('preview', $_POST);
		$thread_id = in_num('thread_id', $_POST);
		$notify = get_notify(get_thread_id($thread_id));
        if ($preview)
			post_reply($catid,$post_id,$preview, $notify);
		else {
			$npost_id = post_new_reply($catid,$post_id,$thread_id,$notify);
			if (isset($npost_id))
				CMSResponse::Redir("index.php?option=forum&task=viewpost&catid=$catid&post_id=$npost_id&Itemid=$Itemid#p".$npost_id);
		}
        break;

    //basic admin functions
    case "delete":
		$post_id = in_num('post_id');
		if (!isset($post_id))
			break;
		
		$thread = delete_post($post_id);
		if (!isset($thread))
			return;		
		
		if ($thread)
			CMSResponse::Redir("index.php?option=forum&task=viewpost&catid=$catid&post_id=$thread&Itemid=$Itemid");
		else
			CMSResponse::Redir("index.php?option=forum&task=category&catid=$catid&Itemid=$Itemid");
        break;
	case 'lock':
		$prop = 'locked=1';
		_admin_fn($prop);
		break;
	case 'unlock':
		$prop = 'locked=0';
		_admin_fn($prop);
		break;
	case 'stick':
		$prop = 'sticked=1';
		_admin_fn($prop);
		break;
	case 'unstick':
		$prop = 'sticked=0';
		_admin_fn($prop);
		break;
	case 'move':
		$post_id = in_num('post_id');
		if (!isset($post_id))
			break;
		move_thread($post_id);
		break;
	case 'move_thread':
		$move_subject = in('move_subject', __NOHTML|__SQL, $_POST, null);
		if (!isset($move_subject))
			CMSResponse::Back(_FORM_NC);
		$move_source = in_num('move_source', $_POST);
		$move_destination = in_num('move_destination', $_POST);
		if (!isset($move_source) || !isset($move_destination)) {
			CMSResponse::Back(_FORM_NC);
			return;
		}
		
		$crow = get_post_cat($move_source, ',editgroup,locked',',userid');
		if (!$crow)
			return;
		$catid = $crow[1]['id'];
		if ($catid==$move_destination) {
			CMSResponse::Back(_FORUM_CANNOT_MOVE_SELF);
			return;
		}
		$post_uid = $crow[0]['userid'];
		$crow = $crow[1];
		
		if (!moderator_rights($catid, $post_uid, $crow['locked'])) {
			CMSResponse::Unauthorized();
			return;
		}
		
		$row = $conn->SelectRow('#__forum_categories', 'id,checked_out', 
				' WHERE id='.$catid.' '.$access_sql.' AND editgroup<'.($my->gid+1));
		if (!$row) {
			CMSResponse::Unauthorized();
			return;
		}
		
		// move the top post
		$conn->Update('#__forum_posts', 'subject=\''.$move_subject.'\', catid='.$move_destination, ' WHERE id='.$move_source);
		// move the thread posts
		$conn->Update('#__forum_posts', 'catid='.$move_destination, ' WHERE thread_id='.$move_source);
		$amt = $conn->Affected_Rows();
		// move the topic itself
		$conn->Update('#__forum_topics', 'subject=\''.$move_subject.'\', catid='.$move_destination, ' WHERE id='.$move_source);
		
		// update the count of original category and destination category
		$srow = $conn->SelectRow('#__forum_categories', 'post_count,topic_count', ' WHERE id='.$catid);
		$pc = $srow['post_count'];
		$tc = $srow['topic_count'];
		$conn->Update('#__forum_categories', 'post_count='.($pc-$amt).', topic_count='.($tc-1),
			' WHERE id='.$catid);
		$srow = $conn->SelectRow('#__forum_categories', 'post_count,topic_count', ' WHERE id='.$move_destination);
		$pc = $srow['post_count'];
		$tc = $srow['topic_count'];
		$conn->Update('#__forum_categories', 'post_count='.($pc+$amt).', topic_count='.($tc+1),
			' WHERE id='.$move_destination);
			
		// if this post was the last reply of the category, update the category
		if ($row['checked_out']==$move_source) {
			$pid = find_last_post($catid);
			$conn->Update('#__forum_categories', 'checked_out='.$pid, ' WHERE id='.$catid);
		}

		// update the last post of destination category too
		$pid = find_last_post($move_destination);
		$conn->Update('#__forum_categories', 'checked_out='.$pid, ' WHERE id='.$move_destination);

		CMSResponse::Redir("index.php?option=forum&task=category&catid=".$move_destination."&Itemid=$Itemid");
		break;
	case "edit":
		$post_id = in_num('post_id');
		if (!isset($post_id))
			break;
			
		$crow = get_post_cat($post_id, ',locked,editgroup', ',userid');
		if (!$crow)
			return;
		$row = $crow[0];
		$crow = $crow[1];
		$catid = $row['catid'];
		if (!can_user_edit($row['userid'], $catid, $crow['locked'])) {
			CMSResponse::Unauthorized();
			return;
		}
		edit_reply($catid, $post_id, $crow['name'], $crow['editgroup'], false, get_notify());
        break;
		
	case 'report':
		$post_id = in_num('post_id');
		if (isset($post_id))
			report_post($post_id);
	break;
	
	case 'report_post':
		if (null === ($post_id = in_num('post_id')))
			break;
			
		$cat = get_post_cat($post_id, ',editgroup,locked,name', ',subject,name,userid');
		if (!$cat)
			break;
			
		$crow = $cat[1];
		$catid = $crow['id'];
		$postrow = $cat[0];
		unset($cat);
			
		if (!can_user_post($crow['editgroup'], $crow['locked'], $catid)) {
			CMSResponse::Unauthorized();
			return;
		}

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
		if (!count($langs)) {
			$recp = $m->notify_list();
			$emails = $recp[0];
			$langs = $recp[1];
			$recp = null;
		}
		
		$post_url = 'index.php?option=forum&task=viewpost&catid='.$catid.'&post_id='.$post_id.'&Itemid='.$Itemid;
		$post_auth = $postrow['userid'];
		if (!$post_auth)
			$post_auth = '-';
		else
			$post_auth = '#p'.$post_auth;
		
		$report_comment = in_nospam('report_comment', $_POST, '', 256);
		$report_comment = str_replace(array("\n", "\r"), " ", $report_comment);
		
		$message = array('_FORUM_REPORTED_POST_MSG',
			$crow['name'],
			$post_id,
			'<'.$d_website.$post_url.'>',
			html_to_text($postrow['subject']),
			$postrow['name'],
			$post_auth,
			$my->username,
			$report_comment);
		
		$m->I18NSend($emails, $langs, array('_FORUM_REPORTED_POST', $crow['name']), $message, 'forum');
		CMSResponse::Redir($post_url, _FORUM_MSG_REPORTED);	
	break;

    case "editreply":
		if ( (null === ($post_id = in_num('post_id'))) ||
			(null === ($post_subject = in('post_subject', __NOHTML, $_POST, 255))) ||
			(null === ($post_message = in('post_message', __NOHTML, $_POST)))
		) {
			CMSResponse::Back(_FORM_NC);
			break;
		}
		
		$crow = $conn->SelectRow('#__forum_posts', 'catid,userid', ' WHERE id='.$post_id);
		if (!$crow) {
			CMSResponse::NotFound();
			break;
		}
		
		$cat = $conn->SelectRow('#__forum_categories', 'name,editgroup,locked', ' WHERE id='.$crow['catid'].
						' '.$access_sql);
		if (!$cat) {
			CMSResponse::Unauthorized();
			break;
		}
		
		$catid = $crow['catid'];
		if (can_user_edit($crow['userid'],$catid,$cat['locked'])) {
			$preview = in_num('preview', $_POST);
			if ($preview) {
				edit_reply($catid, $post_id, $cat['name'], $cat['editgroup'], true, get_notify());
				break;
			}
			$row=$conn->SelectRow('#__forum_posts', 'id,thread_id,catid', ' WHERE id='.$post_id);
			$post_subject = sql_encode($post_subject);
			if (!$row['thread_id'])
				$conn->Update('#__forum_topics', "subject='$post_subject'", " WHERE id=".$post_id);
			$post_message = sql_encode($post_message);
			$conn->Update('#__forum_posts', "subject='$post_subject',message='$post_message'", " WHERE id=$post_id");
			CMSResponse::Redir("index.php?option=forum&task=viewpost&catid=$catid&post_id=$post_id&Itemid=$Itemid");
		} else
			CMSResponse::Unauthorized();
        break;
	case 'unreadposts':
		$id = in_userid('id', $_GET);
		if (!isset($id)) {
			CMSResponse::BadRequest();
			return;
		}
		if ($id!=$my->id || !$my->id) {
			CMSResponse::Unauthorized();
			return;
		}
		$row = $conn->SelectRow('#__forum_posts', 'time', ' WHERE userid='.$id.' ORDER BY time DESC');
		if (!isset($row['time']))
			$stime = 0;
		else
			$stime = (int)$row['time'];
		$rsa = $conn->SelectArray('#__forum_posts', 'id,thread_id', ' WHERE userid<>'.$my->id.' AND time >= '.$stime.
					' ORDER BY time DESC, thread_id');
		forum_results('unread_posts_head', null, $params->get('show_count',10), $rsa, 'option=forum&amp;task=unreadposts&amp;Itemid='.$Itemid.'&amp;id='.$id);
		break;		
	case 'userposts':
		if ($my->gid < $params->get('user_posts', 1)) {
			CMSResponse::Unauthorized();
			return;
		}
		$id = in_userid('id', $_GET);
		if (!isset($id) || !$id) {
			CMSResponse::BadRequest();
			return;
		}
		$urow = $conn->SelectRow('#__users', 'name', ' WHERE id='.$id);
		if (empty($urow)) {
			CMSResponse::BadRequest();
			return;
		}
		$rsa = $conn->SelectArray('#__forum_posts', 'id,thread_id', ' WHERE userid='.$id.
					' ORDER BY time DESC, thread_id');
		forum_results('user_posts_head', $urow['name'], $params->get('show_count',10), $rsa, 'option=forum&amp;task=userposts&amp;Itemid='.$Itemid.'&amp;id='.$id);
		break;		
	case 'section':
		$sec_id = in_num('sec_id', $_GET);
		if (isset($sec_id))
			view_categories($sec_id);
		break;
	case 'feed':
		if (!$params->get('feeds', 1)) {
			CMSResponse::Forbidden();
			return;
		}
		include com_path('feed');
	break;
	default:
	$task = 'view';
	case "view":
		view_categories();
}

?>