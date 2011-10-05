<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

include(com_path($d_type));

$task = in_raw('task', $_REQUEST, 'view');

$pathway->add_head(_FAQ_FAQH);

switch ($task) {
	case 'new':
		if (!$my->can_submit()) {
			CMSResponse::Unauthorized('', false);
			break;
		}
		new_question();
	break;
	case 'newfaq':
		if ((null === ($faq_catid = in_num('faq_catid', $_POST)))
			|| (!$my->gid && (
			('' === ($faq_email = in('faq_email', __NOHTML, $_POST, '', 200)))
			|| ('' === ($faq_name = in('faq_name', __NOHTML, $_POST, '', 200)))
			)
			|| ('' === ($faq_question = in('faq_question', __NOHTML, $_POST, '', 200)))
			)) {
			CMSResponse::Redir('index.php?option=faq&task=new', _FORM_NC);
			break;
		}
		if (!$my->gid && !is_email($faq_email)) {
			CMSResponse::Redir('index.php?option=faq&task=new', _EMAIL_NOT_VALID);
			break;
		}
			
		if (!can_submit_into_category($faq_catid))
			break;		

		include($d_root.'admin/classes/easydb.php');
		$easydb = new EasyDB();
		$order=$easydb->neworder("faq");
		//TODO: add name field to database table
		$conn->Insert('#__faq', '(catid,question,published,ordering,userid,created)', "$faq_catid,'".sql_encode($faq_question)."',2,$order, ".$my->id.','.$time);
		if ($my->gid>0) {
			$faq_name = $my->name;
			$faq_email = $my->email;
		}
		if($d_event) {
			include_once $d_root.'classes/gelomail.php';
			$m = new GeloMail();
			$m->I18NSendNotify(array('_FAQ_ADDED_SUBJECT', $d_title),
						array('_FAQ_ADDED_BODY', $d_website, $my->username, $my->id,
						html_to_text($faq_question), $faq_name, $faq_email),
						'faq'); $m = null;
		}
		CMSResponse::Redir('index.php?option=faq&catid='.$faq_catid, _E_ITEM_SAVED);
	break;
	case 'view':
		$catid = in_num('catid'); //L: needs more checking
		view_items($catid);
}
?>