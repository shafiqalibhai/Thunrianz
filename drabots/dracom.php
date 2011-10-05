<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Comment drabot
# @author legolas558
# original @author Ver Pangolilo
#

$_DRABOTS->registerFunction( 'onAfterDisplayContent', 'botShowComment' );
$_DRABOTS->registerFunction( 'onBeforeDisplayContent', 'botRecordComment' );

global $_dracom_added_comment;
$_dracom_added_comment = 0;

function botRecordComment($row) {
	global $viewtype;
	if (($viewtype == "frontpage") || ($viewtype == "archive"))
		return;
		
	// get the drabot parameters
	global $_DRABOTS, $my;
	$params = $_DRABOTS->GetBotParameters('content', 'dracom');

//	$task = in_raw('comment_task', $_POST);
	$poster_name = in('poster_name', __SQL | __NOHTML, $_POST);
	$comment = in('comment', __SQL | __NOHTML, $_POST, '', 255);
	// no comment POST
	if (!strlen($comment))
		return;
	$cl = $params->get('captcha', 1);
	if ( ($cl != 9) && ($my->gid < $cl)) {
		if (!$my->valid_captcha('dracom'))
			return;
	}

	$comment_id = in_num('comment_id', $_POST, $row['id']); 
	$comment_private = in_num('comment_private', $_POST, 0);

	// prepare the submitter name
	if ($my->id)
		$poster_name = sql_encode($my->name);
	else {
		if (trim($poster_name)==="")
			$poster_name = sql_encode(_ANONYMOUS);
	}
	// see if this comment needs approval
	if (($params->get('approval', 1)) && ($my->gid < 4))
		$published = 2;
	else
		$published = 1;
	// check whether this comment is marked as private or not
	if (!$params->get('private', 0))
		$comment_private = '0';
	global $conn, $time;
	// published = (0: unpublished, 1:published to selected (by param) access level, 2: pending review,
	$conn->Insert('#__content_comment', '(comment_id, title, name, comment, date, published, private)',
				$comment_id.", '".sql_encode($row['title'])."', '".$poster_name."', '".$comment.
				"', '".$time."', $published, $comment_private");
	global $_dracom_added_comment;
	$_dracom_added_comment = $published;
}

function botShowComment($row) {
	global $_dracom_added_comment;
	if ($_dracom_added_comment) {
		if ($_dracom_added_comment == 2)
			$msg = _DRACOM_COMMENT_PENDING;
		else
			$msg = _DRACOM_COMMENT_OK;
		return '<div class="commentfront">'.$msg.'<br /></div>';
	}

	global $conn, $d_type,$pop,$viewtype,$my,$d_website,$time,$d_subpath,$d_root;
	
	// get the drabot parameters
	global $_DRABOTS;
	$params = $_DRABOTS->GetBotParameters('content', 'dracom');
	
	$comment_id = in_num('comment_id', $_POST, $row['id']);
	
	$access = $params->get('visibility', 0);
	if ($my->gid<$access)
		// if comments are not accessible at this level, show none
		$rsa = array();
	else {
		// show private comments only to manager and above
		if ($my->gid>=4)
			$fsql = ' OR published=2';
		else $fsql = ' AND private=0';
		$rsa=$conn->SelectArray('#__content_comment', '*', ' WHERE ( published=1'.$fsql.' ) AND comment_id='.$row['id']." ORDER BY date DESC"); 
	}
	$row_total = count($rsa);
	$_hits=$conn->SelectRow('#__content', 'hits', ' WHERE id='.$row['id']);

	if (($viewtype == "frontpage") || ($viewtype == "archive")) {
//		$row_total = count($rsa);
		$_comment = "<a href=\"index.php?option=content&amp;task=view&amp;id=".$row['id'] ."#comment\">";
		$_comment .= ($row_total==1 ? _DRACOM_COMMENT : _DRACOM_COMMENTS);
		$_comment .= "</a>";

		$view = $_hits['hits'].' '. ($_hits['hits']==1 ? _DRACOM_VIEW : _DRACOM_VIEWS);

//		if($d_type=="html")			return "<tr><td><hr style='width: 100px' align='left' /><span class='commentfront'>$view $row_total $_comment</span></td></tr>";

//		if($d_type=="xhtml")
			return "<div class='commentfront'>$view $row_total $_comment</div>";
	} else {
		$com = '<hr/><div class="dk_small" style="font-weight:bold"><a name="comment">'._DRACOM_COMMENTS_LIST.':</a></div>';
		//FIXME!
		if ($my->gid<$access)
			// if comments are not accessible at this level, show none
			$rsa = array();
		else
			$rsa=$conn->SelectArray('#__content_comment', '*', ' WHERE ( published=1'.$fsql.' ) AND comment_id='.$row['id'].' ORDER BY date DESC');
		$row_total = count($rsa);
		global $d;
		if ($row_total) {
			include_once $d_root.'includes/bbcode.php';
			foreach($rsa as $i => $crw) {
				$com .= '<span class="comments">'.bbdecode($crw['comment'], false).'</span><br/>';
				$com .= '<span class="postedby">'._DRACOM_POSTED_BY.' '.$crw['name'].' '._DRACOM_POSTED_ON.' '.$d->DateFormat($crw['date']);
				if ($crw['published'] == 2)
					$com .= '&nbsp;&nbsp;<img src="'.$d_subpath.'admin/templates/default/images/publish_r.png" alt="Not Published" />';
				if ($crw['private'])
					$com .= '&nbsp;&nbsp;<img src="'.$d_subpath.'admin/templates/default/images/private.png" alt="Private" />';
				$com .= '</span><br/><hr style="width: 100px" align="left" />';
				if ($i == 10)
					break;
			}
		}
		if ($row_total==1)
			$com .= '<span class="commentfooter">'.$row_total.' '._DRACOM_COMMENT.'</span>';
		else {
			if ($row_total<10)
				$com .= '<span class="commentfooter">'.$row_total.' '._DRACOM_COMMENTS.'</span>';
			else
				$com .= '<span class="commentfooter">'.$row_total.' '._DRACOM_COMMENTS.' '._DRACOM_LAST.'</span>';
		}
	}

	if (($viewtype!="archive") && ($viewtype!= "frontpage")) {
			$html = '<br/>'.$com.'<br/>';
			$html .= '<form id="comment_form" name="comment_form" method="post" action="" onsubmit="return check_msg_form();">';
			$html .= _DRACOM_YOUR_NAME.':<br />';
			$html .= '<input name="poster_name" value="'.$my->name.'" type="text" size="25" maxlength="20" class="dk_inputbox" /><br/>';
//			$html .= '<input name="comment_task" type="hidden" id="comment_task" value="insert" />';
global $d;
$d->add_unique_js('dracom_js', 'function textCounter(field, countfield, maxdrait){
	if (field.value.length > maxdrait)
		field.value = field.value.substring(0, maxdrait);
	else
		countfield.value = maxdrait - field.value.length;
	}

	function smile(type) {
	var f=document.forms.comment_form;
	f.comment.value = f.comment.value + " " + type + " ";
	textCounter(f.comment,f.descriptionleft,255);
	f.comment.focus();
}

function check_msg_form() {
	var f=document.getElementById("comment_form");
	if (!f.poster_name.value.length || !f.comment.value.length) {
		alert(\''.js_enc(_FORM_NC).'\');
		return false;
	}
	return true;
}
');
			// get the smileys table
			ob_start();
			include_once $d_root.'includes/bbcode.php';
			include $d_root.'includes/smileys.php';
			$html.=ob_get_clean();

			$html .= _DRACOM_YOUR_COMMENT.':<br/>';
			$html .= '<textarea name="comment" rows="3" cols="35" onkeydown="textCounter(this.form.comment,this.form.descriptionleft,255);" onkeyup="textCounter(this.form.comment,this.form.descriptionleft,255);">';
			$html .= '</textarea>';
			$html .= '<br />'._DRACOM_TEXT_REMAINING.': ';
			$html .= '<input maxlength="3" name="descriptionleft" readonly="readonly" size="3" tabindex="255" value="255" class="dk_inputbox" />';
			if ($params->get('private', 0))
				$html .= '<input type="checkbox" name="comment_private" value="2" /> '._DRACOM_PRIVATE;
			$cl = $params->get('captcha', 1);
			if ( ($cl != 9) && ($my->gid < $cl)) {
				$_DRABOTS->loadBotGroup('captcha');
				$r = $_DRABOTS->trigger_ob('OnCaptchaRender', array('dracom'));
				$html .= $r[0];
			}
			$html .= '<br/><input type="submit" class="dk_button" value="'._DRACOM_ADD_COMMENT.'" />';
			$html .= '&nbsp;&nbsp;&nbsp;<input type="button" class="dk_button" value="'._CANCEL.'" onclick="history.go(-1)" />';
			$html .= '<input type="hidden" name="comment_id" value="'.$comment_id.'" />';
			$html .= '</form>';
//			if($d_type=="html")return "<tr><td class=\"dk_small\">$html</td></tr>";
//			if($d_type=="xhtml")
		return '<div class="dkbot_com dk_small">'.$html.'</div>';
	}
}

?>