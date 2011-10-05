<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

$weblink_catid = in_num('weblink_catid');

switch($option) {
    case "items":
    
        switch($task) {
            case "orderup":
            case "orderdown":
            case "reorder":
            case "publish":
            case "unpublish":
            case "delete":
		// can use $_POST only?
		$cid = in_arr('cid', __NUM, $_REQUEST, null);
		$easydb->data_table($cid, $task, "weblinks","admin.php?com_option=weblinks&option=items","",true);
		break;
            case "create":
                $order=$easydb->neworder("weblinks");
				
				if (null === ($weblink_catid = in_num('weblink_catid', $_POST)))
					CMSResponse::Back(_FORM_NC);
                
                $conn->Insert('#__weblinks','(catid,title,url,description,date,hits,ordering,userid)',
                	"$weblink_catid, '"
                	. in_sql('weblink_title', $_POST) ."', '"
                	. in_sql('weblink_url', $_POST) ."', '"
                	. in_sql('weblink_description', $_POST) . "', '$time', "
                	. in_num('weblink_hits', $_POST) .", ". in_num('order', $_POST, 0)
			. ','.$my->id);
                CMSResponse::Redir("admin.php?com_option=weblinks&option=items");
            break;
		case "save":
			$weblink_id = in_num('weblink_id', $_POST);
			$weblink_catid = in_num('weblink_catid', $_POST);
			$weblink_ocatid = in_num('weblink_ocatid', $_POST);
			$conn->Update('#__weblinks', "catid = $weblink_catid, title = '".in_sql('weblink_title', $_POST).
			"', url = '".in_sql('weblink_url', $_POST)."', description = '".in_sql('weblink_description', $_POST)."', date = $time, hits = ".in_num('weblink_hits', $_POST), " WHERE id = ".in_num('weblink_id', $_POST));
			// fix totals in case of changed category
			$easydb->check_category('weblinks',$weblink_id,$weblink_catid,$weblink_ocatid);	
            CMSResponse::Redir("admin.php?com_option=weblinks&option=items");
            break;
            case "edit":
		$id = in('cid',__NUM|__ARR0, $_REQUEST);
		if (isset($id))
			edit_items($id);
		break;
            case "new":
		edit_items();
		break;
            default:
		$id = in_num('id', $_REQUEST);
		items_table($id);
		break;
    }
    
    break;
    
    /* the categories function handling part */
    case "categories" : 
    switch($task) {
    case "orderup":
    case "orderdown":
    case "reorder":
    case "publish":
    case "unpublish":
	$cid = in('cid', __ARR|__NUM, $_REQUEST);
	$easydb->data_table($cid, $task, "categories","admin.php?com_option=weblinks&option=categories","section='com_weblinks' $edit_sql");
    		break;
	case 'massop':
		$easydb->MassOp('categories','admin.php?com_option=weblinks&option=categories', 'section=\'com_weblinks\'');
	break;

    case "delete":
		$cid = in_arr('cid', __NUM, $_POST);
		// delete weblinks only from deletable categories
		$r = $easydb->delete_np('categories', $cid, substr($edit_sql, 4));
		foreach ($r as $id) {
			$conn->Delete('#__weblinks', ' WHERE catid='.$id);
		}
		CMSResponse::Redir('admin.php?com_option=weblinks&option=categories');
	break;
    case "create":
    $order=$easydb->neworder("categories","section='com_weblinks'");
	
	$conn->Insert('#__categories', '(name,image,image_position,section,description,ordering,access,editgroup)',
	"'".in_sql('category_name', $_POST)."','".in_sql('category_image', $_POST)."','".in_sql('category_image_position', $_POST)."','com_weblinks','".in_sql('category_description', $_POST)."',$order,".in_num('category_access', $_POST).', '.in_num('category_editgroup', $_POST));
	
    CMSResponse::Redir("admin.php?com_option=weblinks&option=categories");
	break;
    
    case "save":
	$conn->Update('#__categories', "name='".in_sql('category_name', $_POST)."', image='".in_sql('category_image', $_POST)."',image_position='".in_sql('category_image_position', $_POST)."',description='".in_sql('category_description', $_POST)."',access=".in_num('category_access', $_POST).
	', editgroup='.in_num('category_editgroup', $_POST), ' WHERE id='.in_num('category_id', $_POST)." $edit_sql");
    CMSResponse::Redir("admin.php?com_option=weblinks&option=categories");
	break;
    
    case "edit":
	$id = in('cid', __ARR0|__NUM, $_REQUEST);
	edit_categories($id);
	break;
    case "new":
	edit_categories();
	break;
    default: categories_table(); break;
    
    } break;

}

?>