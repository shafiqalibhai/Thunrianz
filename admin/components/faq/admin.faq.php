<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
require_once(com_path("html"));

switch($option) {
	case "questions":{

	switch($task) {
	case "orderup":
	case "orderdown":
	case "reorder":
	case "publish":
	case "unpublish":
	case "delete":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		$easydb->data_table($cid, $task, "faq","admin.php?com_option=faq&option=questions","",true);
		break;
	case "create":
		if (!$easydb->Insert('faq', 'faq',
					array('catid', 'question', 'answer', 'published'),
					array(__NUM, __NOHTML, __HTMLAREA, __CHECKBOX),
					null,
					'ordering', '',
					array('userid' => $my->id, 'created' => $time)
					))
			CMSResponse::Back(_FORM_NC);
		// increment records count, if item is published
		if (in_num('faq_published', $_POST))
			change_val('categories', in_num('faq_catid', $_POST), 'count');
		CMSResponse::Redir("admin.php?com_option=faq&option=questions");
	case "save":
		if (!$easydb->Update('faq', 'faq',
					array('catid', 'question', 'answer', 'published'),
					array(__NUM, __NOHTML, __HTMLAREA, __CHECKBOX),
					null,
					'id' ))
			CMSResponse::Back(_FORM_NC);
		else {
			$faq_id = in_num('faq_id', $_POST);
			$faq_catid = in_num('faq_catid', $_POST);
			$faq_ocatid = in_num('faq_ocatid', $_POST);
			$easydb->check_category('faq',$faq_id,$faq_catid,$faq_ocatid);	
			CMSResponse::Redir("admin.php?com_option=faq&option=questions");
		}
		break;
	case "edit":
		$id = in('cid', __ARR0|__NUM, $_REQUEST);
		if (isset($id))
			edit_items($id);
		break;
	case "new":
		edit_items(null); break;
		default:
			items_table(in_num('catid', $_REQUEST)); break;
	}

	}break;

	/* the categories function handling part */
	case "categories" : 
	switch($task)
	{

	case "orderup":
	case "orderdown":
	case "reorder":
	case "publish":
	case "unpublish":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		$easydb->data_table($cid, $task, "categories","admin.php?com_option=faq&option=categories","section='com_faq'");
		break;
	case "delete":
		$cid = in('cid', __ARR|__NUM, $_POST);
		foreach($cid as $id) {
			$conn->Execute("DELETE FROM #__faq WHERE catid=".$id);
		}
		$easydb->delete_np('categories', $cid, "section='com_faq'");
		CMSResponse::Redir("admin.php?com_option=faq&option=categories");
		break;		
			
	case "create":
		if (!$easydb->Insert('categories', 'category',
					array('name', 'image', 'image_position', 'description', 'access'),
					array(__NOHTML, __RAW, __RAW, __NOHTML, __NUM),
					null,
					'ordering',
					"section='com_faq'",
					array('section' => "'com_faq'")
					))
			CMSResponse::Back(_FORM_NC);

		CMSResponse::Redir("admin.php?com_option=faq&option=categories");
		break;
	case "save":
		if (!$easydb->Update('categories', 'category',
					array('name', 'image', 'image_position', 'description', 'access'),
					array(__NOHTML, __RAW, __RAW, __NOHTML, __NUM),
					null,
					'id'
					))
			CMSResponse::Back(_FORM_NC);

		CMSResponse::Redir("admin.php?com_option=faq&option=categories");
		break;
	case "edit" :
		$id = in('cid', __ARR0|__NUM, $_REQUEST);
		if (isset($id))
			edit_categories($id);
		break;
	case "new" :
		edit_categories();
		break;
	default: categories_table(); break;

} break;

}

?>