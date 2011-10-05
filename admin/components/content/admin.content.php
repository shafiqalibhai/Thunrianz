<?php if (!defined('_VALID')) {header('Status: 404 Not Found');die;}

require usr_com_path('common.php');
require com_path("html");

$easydb->rev_order = true;

$sec_id = in_num('sec_id');
$cid = in_arr('cid', __NUM);

function content_deletion($cid, $sec_id) {
	global $easydb, $conn;
	//FIXME: category should be checked
	$easydb->data_table($cid, 'delete', "content", "", "", true);
        foreach($cid as $var)
		$conn->Delete('#__content_frontpage', " WHERE id = $var");
        CMSResponse::Redir("admin.php?com_option=content&option=items&sec_id=$sec_id");
}

switch ($option) {
    /* the items function handling part */
    case "archive":
        switch ($task) {
		case "unarchive":
			$cid = in('cid', __ARR|__NUM, $_REQUEST);
			//FIXME: category should be checked
			$easydb->data_table($task, "content", "admin.php?com_option=content&option=items&sec_id=$sec_id", "sectionid=$sec_id", true);
			break;
		case "delete":
			$cid = in('cid', __ARR|__NUM, $_REQUEST);
			if (!isset($cid))
				CMSResponse::Back(_FORM_NC);
			content_deletion($cid, $sec_id);
			break;
		default:
			archive_items_table();
			break;
        }
        break;

	case "items" :
	switch ($task) {
		case "orderup":
		case "orderdown":
		case "reorder":
		case "publish":
		case "unpublish":
		case "archive":
		case "unarchive":
		$category_id = in_num('catid', $_POST);
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		//FIXME: category should be checked
                $easydb->data_table($cid, $task, "content", "admin.php?com_option=content&option=items&sec_id=$sec_id".(isset($category_id)?'&cid[]='.$category_id:''), "sectionid=$sec_id", true);
                break;
		case 'massop':
			$catid = in_num('catid', $_POST);
			$easydb->MassOp('content','admin.php?com_option=content&option=items&sec_id='.$sec_id.
			(isset($catid) ? '&catid='.$catid : ''));
		break;
		case 'content_delete':
		$cid = in_num('content_id', $_POST);
		if (!isset($cid))
			break;
		$cid = array($cid);
		content_deletion($cid, $sec_id);
		break;

            case "delete":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		if (!isset($cid))
			CMSResponse::Back(_FORM_NC);
		content_deletion($cid, $sec_id);
                break;
            case "create":
//                if ($content_bodytext=="<br />[CR][NL]") $content_bodytext="";
                $content_created = format_date(in_raw('content_created', $_POST));
                $content_modified = format_date(in_raw('content_modified', $_POST));
                if (null === ($sec_id = in_num('sec_id', $_GET)))
					break;

		// properly save item if archived
                $content_published = in_num('content_published', $_POST);
//		if ($content_published == 2)		$content_published = 0;
                $content_frontpage = in_num('content_frontpage', $_POST, 0);
		
		$content_catid = in_num('content_catid', $_POST);
		
		$flags = in_prefix('content_opts_', __NUM, $_POST);
		
		$content_title = in('content_title', __SQL|__NOHTML,$_POST);
		$content_title_alias = in('content_title_alias', __SQL|__NOHTML,$_POST, '');
		if (!strlen($content_title_alias))
			$content_title_alias = $content_title;
                $order = $easydb->neworder("content", "sectionid='$sec_id'");
                $conn->Insert('#__content', '(title,title_alias,introtext,bodytext,sectionid,mask,catid,created,modified,userid,'.
			'created_by_alias,published, frontpage,ordering,metakey,metadesc,access)',
			"'" . $content_title."', '" . $content_title_alias ."', '"
                        . sql_encode(in_area('content_introtext', $_POST))."', '" . sql_encode(in_area('content_bodytext', $_POST))."', ".$sec_id .", "
                        . mk_content_flags($flags).", "
                        . $content_catid." , '"
                        . $content_created."', '"
                        . $content_modified."', "
			. $my->GetID().', \''
                        . in('content_created_by_alias', __SQL|__NOHTML,$_POST)."', "
                        . $content_published.", $content_frontpage , $order, '"
                        . in_sql('content_metakey', $_POST, '', 1024)."', '"
                        . in_sql('content_metadesc', $_POST, '', 1024)."', "
                        . in_num('content_access', $_POST));
		if ($content_frontpage) {
			// instantly retrieve the content item ID before any other database operation
			$last_id = $conn->Insert_ID();
			$order = $easydb->neworder("content_frontpage");
			$conn->Insert('#__content_frontpage', '(id,ordering)', $last_id.','.$order);
                }
		
		if ($content_published)
			// add 1 if the content item is published
			change_val('categories', $content_catid, 'count');
		
		$_DRABOTS->loadBotGroup( 'content', $sec_id );

		$_DRABOTS->trigger('onContentSubmission', array($conn->Insert_ID()));
		
		$catid = in_num('catid', $_REQUEST);
		CMSResponse::Redir("admin.php?com_option=content&option=items&sec_id=$sec_id"
						.(isset($catid) ? '&cid[]='.$catid : ''));
                break;
            case "save":
                    $content_created = format_date(in_raw('content_created', $_POST));
                    $content_modified = format_date(in_raw('content_modified', $_POST));
                    $content_id = in_num('content_id', $_POST);
                    $content_catid = in_num('content_catid', $_POST);
                    $content_ocatid = in_num('content_ocatid', $_POST);
                    $content_frontpage = in_num('content_frontpage', $_POST, 0);
					
		$flags = in_prefix('content_opts_', __NUM, $_POST);

			$content_title = in('content_title', __SQL|__NOHTML,$_POST);
			$row = $conn->SelectRow('#__content', 'title', ' WHERE id='.$content_id);
                    // if ($content_bodytext=="<br />[CR][NL]") $content_bodytext="";
                    $conn->Update('#__content',
						"title = '" .     $content_title
                    . "' , title_alias = '" . in('content_title_alias', __SQL|__NOHTML,$_POST)
                    . "' , introtext = '" .   sql_encode(in_area('content_introtext', $_POST))
                    . "' , bodytext = '" .    sql_encode(in_area('content_bodytext', $_POST))
                    . "' , mask = ".mk_content_flags($flags)
                    . " , catid = $content_catid"
                    . " , created = '" . $content_created ."'"
                    . " , modified = '". $content_modified."' "
                    . " , created_by_alias = '" . in('content_created_by_alias', __SQL|__NOHTML,$_POST)
                    . "' , published = " .   in_num('content_published', $_POST)
                    . " , frontpage = $content_frontpage "
                    . " , metakey = '" .     in('content_metakey', __SQL|__NOHTML,$_POST, '', 1024)
                    . "' , metadesc = '" .    in('content_metadesc', __SQL|__NOHTML,$_POST, '', 1024)
                    . "' , access = " .      in_num('content_access', $_POST),
                    " WHERE id = $content_id" );
			
			// re-count the number of published content items
			$easydb->check_category('content', $content_id, $content_catid, $content_ocatid);
			// remove from frontpage if necessary
			if (!$content_frontpage)
				$conn->Delete('#__content_frontpage', " WHERE id = $content_id");
			if ($content_frontpage) {
				$rs = $conn->Execute("SELECT id FROM #__content_frontpage WHERE id = $content_id");
				if ($rs->RecordCount() == 0) {
					$order = $easydb->neworder("content_frontpage");
					$conn->Execute("INSERT INTO #__content_frontpage (id,ordering) VALUES ($content_id,$order)");
				}
			}
			update_menu_content($content_id, $content_title, $row['title']);
			$catid = in_num('catid', $_REQUEST);
			CMSResponse::Redir("admin.php?com_option=content&option=items&sec_id=$sec_id"
							.(isset($catid) ? '&cid[]='.$catid : ''));
                    break;

            case 'edit':
		$cid = in('cid', __ARR0|__NUM, $_REQUEST);
		$catid = in_num('catid', $_REQUEST);
		if (isset($sec_id) && isset($cid))
			edit_items($sec_id, $cid, $catid);
		break;
            case 'new':
		if (isset($sec_id))
			edit_items($sec_id);
		break;
            default:
		if (isset($cid))
			$catid = $cid[0];
		else $catid = null;
		items_table($sec_id, $catid);
                break;
        }
        break;

    /* the categories function handling part */
    case "categories" :
        switch ($task) {
            case "orderup":
            case "orderdown":
            case "reorder":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
                $easydb->data_table($cid, $task, "categories", "admin.php?com_option=content&option=categories&sec_id=$sec_id", '', "section=$sec_id");
		break;
	case 'massop':
		$easydb->MassOp('categories','admin.php?com_option=content&option=categories&sec_id='.$sec_id);
		break;
            case "delete":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		if (!isset($cid))
			CMSResponse::Back(_FORM_NC);
                change_val("sections", $sec_id, "count", -1);
				foreach ($cid as $id) {
					$ids = $conn->GetColumn('SELECT id FROM #__content WHERE catid='.$id);
					foreach($ids as $c_id)
						$conn->Delete('#__content_frontpage', ' WHERE id='.$c_id);
					if (isset($ids[0]))
						$conn->Delete('#__content', ' WHERE catid='.$id);
				}
                $easydb->delete_np("categories", $cid, "section=$sec_id");
				CMSResponse::Redir("admin.php?com_option=content&option=categories&sec_id=$sec_id");
                break;

		case "create":
			if (!content_custom_icon_handle('section_image_upload', 'section_image'))
				break;
				$easydb->Insert('categories', 'section',
				array('name', 'image', 'image_position', 'description', 'access', 'editgroup'),
				array(
					__NOHTML, __RAW, __RAW, __RAW, __NUM, __NUM
					), null, 'ordering', "section=$sec_id", array('section' => $sec_id));
				change_val("sections", $sec_id, "count", 1);
				CMSResponse::Redir("admin.php?com_option=content&option=categories&sec_id=$sec_id");
			break;
		case "save":
			if (!content_custom_icon_handle('section_image_upload', 'section_image'))
				break;
			$id = in_num('section_id', $_REQUEST);
			$row = $conn->SelectRow('#__categories', 'name', ' WHERE id='.$id);
			$easydb->Update('categories', 'section',
				array('name', 'image', 'image_position', 'description', 'access', 'editgroup'),
				array(
					__NOHTML, __RAW, __RAW, __RAW, __NUM, __NUM
				), null, 'id');
			update_menu_category($id, in('section_name', __NOHTML, $_POST), $row['name']);
			CMSResponse::Redir("admin.php?com_option=content&option=categories&sec_id=$sec_id");	
			break;
            case "edit":
		edit_categories($sec_id, $cid[0]);
		break;
            case "new":
		edit_categories($sec_id);
		break;
            default:
		categories_table($sec_id);
                break;
        }
        break;

    /* the section function handling part */
    default :
	// only managers can edit sections
	if ($my->gid<4) {
		CMSResponse::BackendUnauthorized();
		break;
	}
        switch ($task) {
		case "delete":
		$cid = in('cid', __ARR|__NUM, $_REQUEST);
		if (!isset($cid))
			CMSResponse::Back(_FORM_NC);
			//L: remove content items of each section
			foreach ($cid as $id) {
				$ids = $conn->SelectColumn('#__content', 'id', ' WHERE sectionid='.$id);
				foreach($ids as $content_id) {
					$conn->Delete('#__content_frontpage', ' WHERE id='.$content_id);
				}
				$conn->Execute('DELETE FROM #__content WHERE sectionid='.$id);
			}
		case "orderup":
		case "orderdown":
		case "reorder":
		case "publish":
		case "unpublish":
			$cid = in('cid', __ARR|__NUM, $_REQUEST);
			$easydb->data_table($cid, $task, "sections", "admin.php?com_option=content");	//L: removed $extra =  "id<>1"
                break;
		case "create":
			if (!content_custom_icon_handle('section_image_upload', 'section_image'))
				break;
			$order = $easydb->neworder("sections");
					$section_title = in_sql('section_title', $_POST);
					$section_name = in_sql('section_name', $_POST);
					$section_image = in_sql('section_image', $_POST);
					$section_image_position = in_sql('section_image_position', $_POST);
					$section_description = in_sql('section_description', $_POST);
					$section_access = in_num('section_access', $_POST);
			$conn->Execute("INSERT INTO #__sections " . "\n(title,name,image,image_position,description,ordering,access) " . "\nVALUES ('$section_title','$section_name','$section_image','$section_image_position','$section_description',$order,$section_access)");
			CMSResponse::Redir("admin.php?com_option=content");
			break;

            case "save":
			if (!content_custom_icon_handle('section_image_upload', 'section_image'))
				break;
				$section_title = in_sql('section_title', $_POST);
				$section_name = in_sql('section_name', $_POST);
				$section_image = in_sql('section_image', $_POST);
				$section_image_position = in_sql('section_image_position', $_POST);
				$section_description = in_sql('section_description', $_POST);
				$section_access = in_num('section_access', $_POST);
				$section_id = in_num('section_id', $_POST);
				$row = $conn->SelectRow('#__sections', 'title', ' WHERE id='.$section_id);
				$conn->Execute("UPDATE #__sections SET title = '$section_title' , name = '$section_name' ,image = '$section_image' ,image_position = '$section_image_position' ,description = '$section_description' ,access = $section_access WHERE id = $section_id");
				update_menu_section($section_id, $section_title, $row['title']);
				CMSResponse::Redir("admin.php?com_option=content");
			break;
			case 'massop':
				$easydb->MassOp('sections','admin.php?com_option=content');
				break;
            case "edit" : edit_section($cid[0]);
                break;
            case "new" : edit_section(null);
                break;
            default: sections_table();
                break;
        }
        break;
}

function content_custom_icon_handle($upload_field, $dest_field) {
	global $d_root, $d_pic_extensions, $d;
	// get the uploaded icon, if any
	include $d_root.'includes/upload.php';
	$upload = in_upload($upload_field, $d_root.$d->SubsitePath().'media/icons/', 0,
	$d_pic_extensions, false);
	if (is_array($upload)) {
		$upload = $upload[0];
		$p = strrpos($upload, '/');
		$upload = substr($upload, $p+1);
		// now set the uploaded image as the selected one
		$_POST[$dest_field] = $upload;
	} else {
		if (strlen($upload)) {
			CMSResponse::Back($upload);
			return false;
		}
	}
	return true;
}

?>