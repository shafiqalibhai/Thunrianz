<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Forum profile options for user profile
# @author legolas558
#

$_DRABOTS->registerFunction( 'OnViewPrivateProfile', 'botForumProfilePrivate' );
$_DRABOTS->registerFunction( 'OnViewPublicProfile', 'botForumProfilePublic' );
$_DRABOTS->registerFunction( 'OnEditUserProfile', 'botForumProfileEdit' );
$_DRABOTS->registerFunction( 'OnEditUserProfileHasFileUpload', 'botForumFormHasFileUpload' );
$_DRABOTS->registerFunction( 'BeforeModifyUser', 'botForumProfileSave' );
$_DRABOTS->registerFunction( 'AfterRemoveUser', 'botForumProfileRemove' );

function botForumProfilePrivate(&$urow) {
	global $_DRABOTS;
	$params = $_DRABOTS->GetBotParameters('core', 'forum_profile');

	global $d__req, $my, $d_root;
	include_once $d_root.'lang/'.$my->lang.'/components/forum.php';
	// show unread posts to profile owner only
	if ($my->id==$urow['id']) {?>
	<div class="dk_content"><a href="<?php echo $d__req; ?>?option=forum&amp;task=unreadposts<?php echo component_instance($params->get('com_id', 0), 'forum').'&amp;id='.encode_userid($urow['id']); ?>"><?php echo _FORUM_UNREAD_POSTS; ?></a></div>
	<?php
	}
}

function botForumProfilePublic(&$urow) {
	list($params, $inst, $com_params) = _botForum_get_params();
	if (!isset($com_params))
		return;
	
	global $d__req, $d_root, $my;
	include_once $d_root.'components/forum/forum.functions.php';
	include_once $d_root.'includes/bbcode.php';
	include_once $d_root.'lang/'.$my->lang.'/components/forum.php';
	$user_info = get_user_info($urow['id']);
	if (!count($user_info))
		$user_info = array('image' => '', 'url' => '', 'posts' => _NA, 'registerDate' => _NA, 'location' => '',
		'information' => '');
	else
		$user_info['registerDate'] = html_forum_date($user_info['registerDate']);
		
	if (!strlen($user_info['image']))
		$pic = 'default.png';
	else
		$pic = $user_info['image']; ?>
	<div class="dk_content"><img src="media/forum/avatars/<?php echo $pic; ?>" alt="<?php echo basename($pic); ?>" border="1" /></div>
	<div class="dk_content"><?php	echo _FORUM_POSTS.' '.$user_info['posts']; ?></div>
	<div class="dk_content"><?php	echo _FORUM_MEMBER_SINCE.' '.$user_info['registerDate']; ?></div>
	<?php if (strlen($user_info['location'])) { ?>
	<div class="dk_content"><?php echo _FORUM_MEMBER_LOCATION.': '.$user_info['location']; ?></div>
	<?php }
	if ($user_info['url']!=='') { ?>
	<div><?php echo _FORUM_MEMBER_WEBSITE; ?>: <a rel="nofollow" href="<?php echo $user_info['url']; ?>"><?php echo $user_info['url']; ?></a></div>
	<?php } ?>
	<div class="dk_content"><?php echo bbdecode($user_info['information']); ?></div>
	<?php
	if ($my->gid >= $params->get('user_posts', 1)) { ?>
	<div class="dk_content"><a href="<?php echo $d__req; ?>?option=forum&amp;task=userposts&amp;Itemid=<?php echo $inst.'&amp;id='.encode_userid($urow['id']); ?>"><?php echo _FORUM_VIEW_ALL_USER_POSTS; ?></a></div><?php
	}
}

function _botForum_get_params() {
	global $_DRABOTS, $d;
	$params = $_DRABOTS->GetBotParameters('core', 'forum_profile');
	$inst = $params->get('com_id', 0);
	if (!$inst)
		$inst = get_default_instance('forum');
	$com_params = $d->GetComponentParamsRaw('forum', $inst);
	return array($params, $inst, $com_params);
}

define('_FORUM_MAXIMUM_UPLOADED_AVATAR', 600*1024);

function botForumProfileSave($id) {
	global $my, $d_root, $d, $d_pic_extensions;
	include_once $d_root.'lang/'.$my->lang.'/components/forum.php';
	$user_url = in('user_url', __SQL|__NOHTML, $_POST, '', 512);
	if (strlen($user_url) && !is_url($user_url))
		return _FORUM_INVALID_URL;
	list($params, $inst, $com_params) = _botForum_get_params();
	if (!isset($com_params))
		return true;

	$root = $d->SitePath().'media/forum/avatars/';
	if (defined('_VALID_ADMIN') || $params->get('allow_avatar_upload',true)) {
		require $d_root.'includes/upload.php';
		$upload = in_upload('user_uploaded_avatar', $root.'custom/', 0,
						$d_pic_extensions, false,
						_FORUM_MAXIMUM_UPLOADED_AVATAR);
		if (is_array($upload)) {
			$maxw = $params->get('resize_avatar_upload', 48);
			if ($maxw) {
				include $d_root.'classes/thumbnailer/thumbnailer.php';
				Thumbnailer::resize_image($upload[0], $upload[0], $maxw);
			}
			$user_avatar = substr($upload[0], strlen($root));
		} else if (strlen($upload))
			return $upload;
	} else $upload = '';
	global $conn;
	if (!is_array($upload)) {
		$user_avatar = in_raw('user_avatar', $_POST, 'default.png');
		if (!preg_match('/(custom\\/)?[^\\.\\/\\\\]+\\.(?i:'.implode('|', $d_pic_extensions).')$/A', $user_avatar))
			$user_avatar = 'default.png';
		if (!file_exists($root.$user_avatar))
			$user_avatar = 'default.png';
		//TODO: be sure that custom avatars are not hijacked
	} else {
		// check if the user had a previous custom avatar, and in such case remove it
		$row = $conn->SelectRow('#__forum_users', 'image', ' WHERE id='.$id);
		if (strpos($row['image'], 'custom/')===0)
			@unlink($root.$row['image']);
	}
	$user_avatar = sql_encode($user_avatar);
	$user_location = in('user_location', __SQL|__NOHTML, $_POST, '', 100);
	$user_information = in('user_information', __SQL|__NOHTML, $_POST, '', 1024);
	$user_signature = in('user_signature', __SQL|__NOHTML, $_POST, '', 300);
	$conn->Update('#__forum_users', "location='$user_location', url='$user_url', information='$user_information', signature='$user_signature', image='$user_avatar'", " WHERE id=$id");
	return true;
}

function botForumFormHasFileUpload(&$urow) {
	if (defined('_VALID_ADMIN'))
		return true;
	list($params, $inst, $com_params) = _botForum_get_params();
	if (!isset($com_params))
		return false;
	if ($com_params->get('allow_avatar_upload', true))
		return true;
	return false;
}

function botForumProfileEdit(&$urow) {
	list($params, $inst, $com_params) = _botForum_get_params();
	if (!isset($com_params))
		return;

	global $d__req, $d_root, $my;
	include_once $d_root.'components/forum/forum.functions.php';
	include_once $d_root.'includes/bbcode.php';
	include_once $d_root.'lang/'.$my->lang.'/components/forum.php';
	$user_info = get_user_info($urow['id']);
	if (empty($user_info))
		$user_info = array('image' => '', 'url' => '', 'posts' => _NA, 'registerDate' => _NA, 'location' => '',
		'information' => '');
?>
	<div class="dk_content"><h3>Avatar</h3><?php
		$folder = 'media/forum/avatars/';
		$avatars = select_array($folder,null, null,'file', $GLOBALS['d_pic_extensions']);
			array_unshift($avatars, array( 'name' => _FORUM_CURRENT_AVATAR,
			'value' => $user_info['image'],
			'selected' => true) );
		global $d_subpath;
		include $d_root.'includes/dil.php';
		echo dynamic_image_list("user_avatar", $avatars, $d_subpath.'media/forum/avatars/default.png', $folder);
		?></div>
		<?php if (defined('_VALID_ADMIN') || $com_params->get('allow_avatar_upload',true)) { ?>
		<div class="dk_content"><?php echo file_input_field('user_uploaded_avatar', _FORUM_MAXIMUM_UPLOADED_AVATAR); ?></div>
		<?php } ?>
		<div class="dk_content">
		<h3><?php echo _FORUM_USER_STATS; ?></h3><?php
			echo _FORUM_POSTS.' '.$user_info['posts'];
			echo "<br />";
			echo _FORUM_MEMBER_SINCE.' '.html_forum_date($user_info['registerDate']);
		?></div>
	<p><h3><?php echo _FORUM_USER_INFO; ?></h3></p>
	<div class="dk_content"><?php echo _FORUM_MEMBER_LOCATION; ?>: <input class="dk_inputbox" type="text" name="user_location" size="40" maxlength="100" value="<?php echo $user_info['location']; ?>" /></div>
	<div class="dk_content"><?php echo _FORUM_MEMBER_WEBSITE; ?>: <input class="dk_inputbox" type="text" name="user_url" size="40" value="<?php echo $user_info['url']; ?>" /></div>
	<table border="0">
	<?php
	
	bbcode_editor(sprintf(_FORUM_MEMBER_INFORMATION, 1024), 'user_information', $user_info['information'], $com_params->get('enable_bb_img', 0), true, 30);
	bbcode_editor(sprintf(_FORUM_MEMBER_SIGNATURE, 300), 'user_signature', $user_info['signature'], $com_params->get('enable_bb_img', 0), true, 30, 3);?>
	</table><?php
}

function botForumProfileRemove($id) {
	global $conn;
	$conn->Delete('#__forum_notifies', ' WHERE id='.$id);
	//TODO: delete custom avatar, if any
	$conn->Delete('#__forum_users', ' WHERE id='.$id);
	// assign discussion to anonymous user
	$conn->Update('#__forum_posts', 'userid=0', ' WHERE userid='.$id);
	$conn->Update('#__forum_topics', 'userid=0', ' WHERE userid='.$id);
}

?>
