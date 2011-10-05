<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

//RFC
$d->add_raw_js("\n".'function getSelectedValue( srcList ) {
	i = srcList.selectedIndex;
	if (i != null && i > -1) {
		return srcList.options[i].value;
	} else {
		return null;
	}
}'."\n");

## build up an array containing categories
#NOTE: in frontend users can submit to all categories they can access to
function section_submission_categories($srs) {
	global $conn, $access_sql;

	$line = 0;
	$cats = array();
	foreach($srs as $sect) {
		$rsa=$conn->SelectArray('#__categories', 'id,name', ' WHERE section='.$sect['id'].' '.
							$access_sql.' ORDER BY name');
		foreach($rsa as $rowc) {
			++$line;
			$cats[$line] = $rowc;
			$cats[$line]['title'] = $sect['title']." -&gt; ".$rowc['name'];
		}
		++$line;
	}
	return $cats;
}

//FIXME
if ($d_type=='html') {
	require_once(com_path($d_type));
}
require_once(com_path('common'));

// these do not need any more specific initialization
$id = in_num('id');
$task = in_raw('task');

switch($task) {

case 'export':
	if (!isset($id)) {
		CMSResponse::BadRequest();
		return;
	}
	$crow = $conn->SelectRow('#__content', 'title,modified',' WHERE id='.$id.' '.$access_sql.' AND created<'.$time);
	if (!$crow) {
		CMSResponse::Unauthorized();
		return;
	}
	$format = in_raw('format', $_GET);
	switch ($format) {
		case 'pdf':
			require com_path($format);
		break;
		default:
			CMSResponse::BadRequest();
			return;
	}
	
break;

case "showblog" :
	if (isset($id))
		showblog($id);
	break;
case "category" :
	if (isset($id))
		showcategory($id);
	break;
case "section" :
	if (isset($id))
		showsection($id);
	break;
case "archive" :
	if (null === ($month = in_num('month')))
		$month = lc_date("m",$time);
	if ($month == 0)
		$month = null;
	if (null === ($year = in_num('year')))
		$year = lc_date("Y",$time);
	showarchive($year, $month);
	break;
case 'insert':
	if (isset($id))
		showcontent($id,"content",true);
	break;
case 'view':
default:
	if (!isset($id)) {
		CMSResponse::BadRequest();
		return;
	}
	change_val('content',$id,'hits',1);
	
	showcontent($id,"content",true);
break;
case "new" :
	new_content(null, in('alias', __NOHTML, $_GET, '', 100));
	break;
case "edit" :
	if (isset($id))
		edit_content($id);
	break;
case "new_content" :
if ( (null === ($content_catid = in_num('content_catid', $_POST))) ||
	(null === ($content_secid = in_num('content_secid', $_POST))) ||
	(null === ($content_catid = in_num('content_catid', $_POST))) ||
	('' === ($content_title = in('content_title', __SQL | __NOHTML, $_POST, '', 100)))
	)
	CMSResponse::Redir('index.php?option=content&task=new', _FORM_NC);
	
	if (!can_edit_category($content_catid, $my->gid))
		break;

	$content_published = in_checkbox('content_published', $_POST);
	$content_frontpage = in_checkbox('content_frontpage', $_POST, 0);
	// disallow publish setting if user is not at least a publisher
	if ($content_published) {
		if (!$my->can_publish())
			$content_published = 2;
	} else
		$content_published = 2;
	if ($content_frontpage) {
		if (!$my->can_publish())
			$content_frontpage = 0;
	}
	
	$content_alias = in('content_alias', __SQL|__NOHTML, $_POST, '');
	if (!strlen($content_alias)) $content_alias = $content_title;
	$content_introtext = in_area('content_introtext', $_POST, '');
	$content_bodytext = in_area('content_bodytext', $_POST, '');
	$content_metakey = in('content_metakey', __SQL|__NOHTML,$_POST, '', 1024);
	$content_metadesc = in('content_metadesc', __SQL|__NOHTML,$_POST, '', 1024);

include $d_root.'admin/classes/easydb.php';
// check that user can put items in that category
$rows=$conn->SelectRow('#__categories', 'name,section', " WHERE id=".$content_catid.' '.$edit_sql);
if (!count($rows)) {
	CMSResponse::Unauthorized();
	break;
}

$content_secid=$rows['section'];
$easydb = new EasyDB();
$easydb->rev_order = true;
$order=$easydb->neworder("content","sectionid=$content_secid");

$uid = $my->GetID();
$uname = $my->name;

$_DRABOTS->loadCoreBotGroup('editor');

$_DRABOTS->trigger('OnContentSave', array(&$content_introtext, &$content_bodytext));

$conn->Insert('#__content',
'(title,title_alias,introtext,bodytext,sectionid,catid,created,modified,userid,created_by_alias,published,ordering,metakey,metadesc,frontpage)',
	"'$content_title','$content_alias','".sql_encode($content_introtext)."','".sql_encode($content_bodytext)."',".
	"$content_secid,$content_catid,$time,$time,$uid,'$uname',$content_published,$order,".
	"'$content_metakey','$content_metadesc', $content_frontpage");
$last_id = $conn->Insert_ID();
if ($content_published==1)
	change_val('categories', $content_catid, 'count', 1);
	if ($content_frontpage) {
		$order = $easydb->neworder("content_frontpage");
		$conn->Insert('#__content_frontpage', '(id,ordering)', "$last_id,$order");
	}

$_DRABOTS->loadBotGroup( 'content', $content_secid );

$_DRABOTS->trigger('onContentSubmission', array($conn->Insert_ID()));

if($d_event) {
	include $d_root.'classes/gelomail.php';

	$raw_content = $content_introtext;
	if (!empty($content_bodytext))
		$raw_content.="<hr />". $content_bodytext;
	$message = array('_CONTENT_NOTIFY_MSG',
		'<pre>',
		'<a href="'.$d_website.'">'.$d_title.'</a>',
		'<a href="mailto:'.$my->email.'">'.$my->name.'</a>', $my->id,
		$content_title, $content_secid, $rows['name'],		
		'</pre><br /><br />',
		'<hr />'.$raw_content);

	$raw_content = null;
	
	$m = new GeloMail(true);
	$m->I18NSendNotify( array('_CONTENT_NOTIFY_SUBJ', $content_title), $message, 'content');
}

CMSResponse::Redir("index.php?option=content&task=success&catid=".$content_catid);
break;
case "edit_content" :
/*	if ( trim($_POST['content_bodytext'])=="<br />")
		$_POST['content_bodytext']="";	*/
	if ( (null === ($content_id = in_num('content_id', $_POST))) ||
		(null ===($content_catid = in_num('content_catid', $_POST))) ||
		('' === ($content_title = in('content_title', __SQL | __NOHTML, $_POST, '', 100)))
		)
			CMSResponse::Redir('index.php?option=content&task=edit&Itemid='.$Itemid, _FORM_NC);
	
	if (!can_edit_item('content', $content_id))
		break;
	
	// get the previous title and the owner id, no access checks because already done by can_edit_item
	$row = $conn->SelectRow('#__content', 'title,userid', ' WHERE id='.$content_id.' AND created<'.$time);
	if (empty($row)) {
		CMSResponse::ContentUnauthorized();
		break;
	}
	if (!$my->can_edit($row['userid'])) {
		CMSResponse::ContentUnauthorized();
		break;
	}
	
	$content_ocatid = in_num('content_ocatid', $_POST);
	$content_published = in_checkbox('content_published', $_POST);
	$content_frontpage = in_checkbox('content_frontpage', $_POST, 0);
	if ($content_published) {
		if (!$my->can_publish())
			$content_published = 2;
	} else
		$content_published = 2;
	if (!$my->can_publish())
		$fp_sql = '';
	else {
		$trow = $conn->SelectRow('#__content', 'frontpage', ' WHERE id='.$content_id);
		if ($trow['frontpage'] != $content_frontpage)
			$fp_sql = ', frontpage='.$content_frontpage;
		else
			$fp_sql = '';
	}

	$content_alias = in('content_alias', __SQL|__NOHTML, $_POST, '');
	if (!strlen($content_alias)) $content_alias = $content_title;
	$content_introtext = in_area('content_introtext', $_POST, '');
	$content_bodytext = in_area('content_bodytext', $_POST, '');
	$content_metakey = in('content_metakey', __SQL|__NOHTML,$_POST, '', 1024);
	$content_metadesc = in('content_metadesc', __SQL|__NOHTML,$_POST, '', 1024);
	$conn->Update('#__content', "title='$content_title',title_alias='$content_alias',introtext='".sql_encode($content_introtext)."',bodytext='".sql_encode($content_bodytext)."',catid=$content_catid, modified='$time', published = $content_published,".
	"metakey='$content_metakey', metadesc='$content_metadesc'$fp_sql", " WHERE id=$content_id");
	include $d_root.'admin/classes/easydb.php';
	$easydb = new EasyDB();
	// fix totals in case of changed category
	$easydb->check_category('content',$content_id,$content_catid,$content_ocatid);
	if (strlen($fp_sql)) {
		if (!$content_frontpage)
			$conn->Delete('#__content_frontpage', ' WHERE id='.$content_id);
		else {
			$order = $easydb->neworder("content_frontpage");
			$conn->Insert('#__content_frontpage', '(id,ordering)', "$content_id,$order");
		}
	}
	
	update_menu_content($content_id, $content_title, $row['title']);
	CMSResponse::Redir("index.php?option=content&task=insert&id=$content_id");
break;
case 'email':
	if (isset($id))
		email_form($id);
	break;
case 'send_email':
	if (isset($id))
		email_sent($id);
	break;
case 'success':
	$catid = in_num('catid', $_GET);
	if (isset($catid))
		confirm_submission($catid);
	break;

}
?>
