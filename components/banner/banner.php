<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

global $d_root;

//L: initialization need more checks here
$task = in_raw('task');
$id = in_num('id');

if (!isset($id)) {
	CMSResponse::NotFound();
	return;
}

if ($task=='go') {
	$row=$conn->SelectRow('#__banners', 'clickurl', " WHERE published=1 AND id=$id");
	if (empty($row)) {
		//NotFound or Unauthorized?
		CMSResponse::Unauthorized();
		return;
	}
	change_val('banners',$id,'hits');
/*	if (!strlen($row['clickurl']))
		CMSResponse::Back();
	else */
	CMSResponse::SeeOther($row['clickurl']);
} else {
	//L: is this OK??
	include $d_root.'modules/mod_banner.php';
}
?>