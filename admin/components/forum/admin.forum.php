<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

function delete_cat_posts($cid) {
	global $conn;
	$conn->Execute("DELETE FROM #__forum_posts WHERE catid=".$cid);
	$conn->Execute("DELETE FROM #__forum_topics WHERE catid=".$cid);
}

switch($option) {
	case 'sections':
		switch ($task) {
			case "orderup":
			case "orderdown":
			case "orderchange":
				$cid = in('cid', __ARR|__NUM, $_REQUEST);
				$easydb->data_table($cid, $task, 'categories',
					'admin.php?com_option=forum&option=sections', "section='com_forum'");
			break;
			case 'massop':
				$easydb->MassOp('categories','admin.php?com_option=forum&option=sections', "section='com_forum'");
			break;
			case 'categories':
				CMSResponse::Redir('admin.php?com_option=forum&option=categories&sec_id='.in('cid', __ARR0|__NUM, $_POST));
			break;
			default:
				sections_table();
			break;
			case 'new':
				edit_section(null);
				break;
			case 'create':
				if (!$easydb->Insert('categories', 'section',
						array('name', 'description', 'access', 'editgroup'),
						array(__NOHTML, __XHTML, __NUM, __NUM),
						null,
						'ordering',
						"section='com_forum'",
						array('section' => "'com_forum'")
						) )
					CMSResponse::Back(_FORM_NC);
				CMSResponse::Redir('admin.php?com_option=forum&option=sections');
				break;				
			case 'save':
				if (!$easydb->Update('categories', 'section',
						array('name', 'description', 'access', 'editgroup'),
						array(__NOHTML, __XHTML, __NUM, __NUM),
						null,
						'id'
						))
					CMSResponse::Back(_FORM_NC);
				CMSResponse::Redir('admin.php?com_option=forum&option=sections');
				break;
			case 'edit':
				$sec_id = in('cid', __ARR0|__NUM, $_REQUEST);
				if (isset($sec_id))
					edit_section($sec_id);
				break;
			case 'delete':
				$cid = in('cid', __ARR|__NUM, $_POST);
				// loop through all section ids
				foreach($cid as $id) {
					// get all categories in this section
					$cats = $conn->GetColumn('SELECT id FROM #__forum_categories WHERE parent_id='.$id);
					// delete all the posts (topics and posts) of this category
					foreach($cats as $cat_id) {
						delete_cat_posts($cat_id);
					}
					// delete the categories themselves
					$easydb->delete_np('forum_categories', $cats);
				}
				// delete the sections
				$easydb->delete_np('categories', $cid /*, "section='com_forum'"*/ );
				CMSResponse::Redir('admin.php?com_option=forum&option=sections');
			break;
		}
	break;
	case "categories" : 
	switch($task) {
	case 'massop':
		$sec_id = in_num('sec_id', $_GET, 0);
		$easydb->MassOp('forum_categories','admin.php?com_option=forum&option=categories&sec_id='.$sec_id);
	break;
	case "orderup":
	case "orderdown":
	case "orderchange":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		$easydb->data_table($cid, $task, "forum_categories","admin.php?com_option=forum&option=categories&sec_id=".$sec_id, 'parent_id='.$sec_id);
		break;
	case "delete":
		$cid = in('cid', __ARR|__NUM,$_POST);
		$sec_id = in_num('sec_id', $_POST);
		if (isset($cid)) {
			foreach($cid as $d_cid) {
				delete_cat_posts($d_cid);
			}
		}
		$easydb->delete_np('forum_categories', $cid );
		CMSResponse::Redir("admin.php?com_option=forum&option=categories&sec_id=".$sec_id);
		break;
	case "create":
		$order=$easydb->neworder("forum_categories");
		$sec_id = $easydb->auto_section('forum', 'category', 0, 'forum_');
		$name = in('category_name', __SQL|__NOHTML,$_POST,'');
		if (!strlen($name))
			CMSResponse::Back(_FORM_NC);
		$conn->Insert('#__forum_categories', "(name,description,moderators,ordering,access,editgroup,parent_id)",
			"'". $name
			. "', '" . in('category_description',__SQL|__XHTML, $_POST) 
			. "', '" . in_sql('category_moderators', $_POST) ."', $order, ".
			in_num('category_access', $_POST).','.
			in_num('category_editgroup', $_POST).", $sec_id");
			CMSResponse::Redir("admin.php?com_option=forum&option=categories&sec_id=".$sec_id);
		break;
	case "save":
		$id = in_num('category_id', $_POST);
		$name = in('category_name', __SQL|__NOHTML,$_POST,'');
		if (!strlen($name))
			CMSResponse::Back(_FORM_NC);
	
		$sec_id = $easydb->auto_section('forum', 'category', $id, 'forum_');
	    $conn->Execute("UPDATE #__forum_categories "
		    . " SET name = '" . $name 
		    . "', description = '" .in('category_description', __SQL|__XHTML,$_POST) 
		    . "', moderators= '" .in_sql('category_moderators', $_POST)
		    . "', access = " .in_num('category_access', $_POST) 
		    . ", locked = " .in_num('category_locked', $_POST) 
		    . ", editgroup = " .in_num('category_editgroup', $_POST) 
			. ", parent_id = ".$sec_id
		    . " WHERE id = ".$id );
			
	    CMSResponse::Redir("admin.php?com_option=forum&option=categories&sec_id=".$sec_id);
	break;

	case "edit":
		// must use $_REQUEST here
		$cid = in('cid', __ARR0|__NUM,$_REQUEST);
		if (isset($cid))
			edit_categories($cid);
		break;
	case "new":
		$sec_id = in_num('sec_id', $_POST);
		edit_categories(null, $sec_id);
		break;
	default:
	case 'categories':
		$sec_id = in_num('sec_id', $_GET);
		if (isset($sec_id))
			categories_table($sec_id);
		break;
	} break;
}

?>