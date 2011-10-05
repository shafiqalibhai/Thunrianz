<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Global Lanius CMS functions requiring valid user context
#
# these functions require a valid user context or are strictly related to user contexts

//TODO: should be d__access_level
$access_level=array(
	array('name'=>_USERS_GRP_0,'value'=>'0'),
	array('name'=>_USERS_GRP_1,'value'=>'1'),
	array('name'=>_USERS_GRP_2,'value'=>'2'),
	array('name'=>_USERS_GRP_3,'value'=>'3'),
	array('name'=>_USERS_GRP_4,'value'=>'4'),
	array('name'=>_USERS_GRP_5,'value'=>'5'),
	array('name'=>_USERS_GRP_9,'value'=>'9')
	);

$d__module_positions = array('left' => _MODULES_POS_LEFT,
					'right' => _MODULES_POS_RIGHT,
					'top' => _MODULES_POS_TOP,
					'bottom' => _MODULES_POS_BOTTOM,
					'inset' => _MODULES_POS_INSET,
					'banner' => _MODULES_POS_BANNER,
					'debug' => _MODULES_POS_DEBUG,
					'head' => _MODULES_POS_HEAD, //TODO: should be removed?
					'user1' => _MODULES_POS_USER.' 1',
					'user2' => _MODULES_POS_USER.' 2',
					'user3' => _MODULES_POS_USER.' 3',
					'user4' => _MODULES_POS_USER.' 4',
					'user5' => _MODULES_POS_USER.' 5',
					'user6' => _MODULES_POS_USER.' 6',
);

// return access group string by its GID
function access_bygid($gid) {
	global $access_level;
	if (empty($gid)) return $access_level[0]['name'];
	foreach($access_level as $acl) {
		if ($acl['value']==$gid)
			return $acl['name'];
	}
	return $gid;
}

// (1) checks that the element is accessible by 'access'
// (2) checks that element exists following all criteria
// (3) checks that parent category is editable
//TODO: enforce usage
//FIXME: should not use ::Unauthorized() method calls, but return the value only
function can_edit_item($table, $cid, $gid=null, $extra = '') {
	global $conn, $access_sql;
	if (!isset($gid)) {
		global $my;
		$gid = $my->gid;
	}
	$crow = $conn->SelectRow('#__'.$table, 'catid', " WHERE id=$cid $access_sql $extra");
	if (!count($crow)) {
		CMSResponse::Unauthorized('', false);
		return false;
	}
	$catid = current($crow); //unset($crow);
	return can_edit_category($catid, $gid);
}

//TODO: enforce usage
function can_edit_category($catid, $gid) {
	global $conn;
	$row = $conn->SelectRow('#__categories', 'id', " WHERE id=$catid AND access<=$gid AND editgroup<=$gid");
	if (!isset($row['id'])) {
		CMSResponse::Unauthorized('', false);
		return false;
	}
	return true;
}

//NOTE: in frontend users can submit to all categories they can access to
function can_submit_into_category($catid) {
	global $conn, $my;
	if (!$my->can_submit()) {
		CMSResponse::Unauthorized('', false);
		return false;
	}
	$row = $conn->SelectRow('#__categories', 'id', " WHERE id=$catid AND access<=".$my->gid);
	if (!isset($row['id'])) {
		CMSResponse::Unauthorized('', false);
		return false;
	}
	return true;
}

## frontend publication depends from editgroup
function can_publish_into_category($catid) {
	global $conn, $my;
	$row = $conn->SelectRow('#__categories', 'id', " WHERE id=$catid AND editgroup<=".$my->gid);
	return !empty($row);
}


function submission_categories($section, $extra = '') {
	global $conn, $access_sql;
	//NOTE: in frontend users can submit to all categories they can access to
	return $conn->SelectArray('#__categories', 'id,name'.$extra, " WHERE section='$section' $access_sql ORDER BY ordering");
}

?>