<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

require_once(com_path("html"));

$easydb->rev_order = true;

switch($task) {
case 'massop':
	$easydb->MassOp('event','admin.php?com_option=event');
	break;
case 'new':
	edit_items(null);
	break;
case "publish":
	$event_id = in('cid', __ARR | __NUM , $_REQUEST);
	if ($event_id === null)
		CMSResponse::Redir('admin.php?com_option=event', _INVALID_ID);

	$conn->Update('#__event', 'published=1', ' WHERE '.each_id($event_id));
	items_table();
	break;
case "unpublish":
	$event_id = in('cid', __ARR | __NUM , $_REQUEST);
	if ($event_id === null)
		CMSResponse::Redir('admin.php?com_option=event', _INVALID_ID);

	$conn->Update('#__event', 'published=0', ' WHERE '.each_id($event_id));
	items_table();
	break;
case "delete":
	$event_id = in('cid', __ARR | __NUM , $_REQUEST);
	if (!isset($event_id))
		CMSResponse::Redir('admin.php?com_option=event', _INVALID_ID);

	$conn->Execute('DELETE FROM #__event WHERE '.each_id($event_id));
	items_table();
	break;
case "create":
	if (('' === ($event_title = in_sql('event_title', $_POST, '')))
		|| (null === ($event_sdate = in_raw('event_sdate', $_POST)))
		|| (null === ($event_edate = in_raw('event_edate', $_POST)) )
		)
		CMSResponse::Redir('admin.php?com_option=event', _INVALID_ID);
	$event_description = in_sql('event_description', $_POST);
	
	if (!_validate_event_dates($event_sdate, $event_edate))
		break;
	
	$event_published = in_num('event_published', $_POST);
	$event_access = in_num('event_access', $_POST);

	$conn->Insert('#__event', '(title,description,sdate,edate,userid,published,access)', "'$event_title','$event_description',$event_sdate,$event_edate, ".$my->id.','.$event_published.','.$event_access);
	CMSResponse::Redir("admin.php?com_option=event");
break;
case "save":
	if ( (null === ($event_id = in('event_id', __NUM , $_POST))) ||
		(null === ($event_sdate = in_raw('event_sdate', $_POST))) ||
		(null === ($event_edate = in_raw('event_edate', $_POST)))
		)
		CMSResponse::Redir('admin.php?com_option=event', _FORM_NC);
		
	$event_title = in_sql('event_title', $_POST);
	$event_description = in_sql('event_description', $_POST);

	if (!_validate_event_dates($event_sdate, $event_edate))
		break;
	$event_published = in_num('event_published', $_POST);
	$event_access = in_num('event_access', $_POST);

	$conn->Update('#__event', "title='$event_title',description='$event_description',sdate=$event_sdate,edate=$event_edate,published=$event_published,access=$event_access", " WHERE id=$event_id");
	items_table();
break;
case "edit" :
	$event_id = in('cid', __NUM | __ARR0, $_REQUEST);
	if ($event_id === null)
		CMSResponse::Redir('admin.php?com_option=event', _INVALID_ID);

	edit_items($event_id);
break;
default:
	items_table();
}

function _validate_event_dates(&$event_sdate, &$event_edate) {
	$event_sdate=format_date($event_sdate);
	// if the end date is not specified, take in the start date
	if (!$event_edate) {
		if (!$event_sdate) {
			CMSResponse::Back(_EVENT_INVALID_SDATE);
			return false;
		}
		$event_edate=$event_sdate + 60*60;
	} else {
		$event_edate=format_date($event_edate);
		if (!$event_edate) {
			CMSResponse::Back(_EVENT_INVALID_EDATE);
			return false;
		}
		if (!$event_sdate || ($event_edate<$event_sdate)) {
			CMSResponse::Back(_EVENT_INVALID_DATE);
			return false;
		}
	}
	return true;
}

?>