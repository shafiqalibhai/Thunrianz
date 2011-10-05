<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

//FIXME
//L: unused?
/*
if ($my->gid<3) {
	CMSResponse::BackendUnauthorized();
	return;
}*/

global $conn, $access_sql;

//$created_sql = ' AND created<'.$time.' ';

// initialize container array
$arr = array();

// load content items news
$rsc = $conn->SelectArray('#__content', '*', ' WHERE published=2 ORDER BY created DESC');
foreach($rsc as $row) {
	$elem = array();
	$elem['date'] = $row['created'];
	$elem['title'] = $row['title'];
	$elem['type'] = 'text';
	$elem['url'] =  "admin.php?com_option=content&amp;option=items&amp;task=edit&amp;sec_id=".$row['sectionid']."&cid[]=".$row['id'];
	$elem['alt'] = _CONTENT;
	$arr[] = $elem;
}

// load custom news
global $_DRABOTS;
$_DRABOTS->loadCoreBotGroup('admin_news');
$results = $_DRABOTS->trigger('OnCollectNews');
foreach($results as $result) {
	// iteratively merge
	$arr = array_merge($arr, $result);
}

$total = count($arr);

// if there are no news, just return
if(!$total) return;

//L: does it do anything?
//sort($arr);

	?>
      <td valign="top" width="300" align="center">
        <table width="90%" height="350" border="0" cellpadding="5" cellspacing="1" bgcolor="#CCCCCC">
	<tr bgcolor="#EEEEEE"><td height="20"><?php echo _ADMIN_START_NEWS;?></td></tr>
	<tr bgcolor="#FFFFFF" valign="top"><td>

	<table border="0" width="100%" cellspacing="0" cellpadding="3"><tr bgcolor="#EFEFFF"><td align="center"><?php echo _DATE; ?></td><td><?php echo _TITLE;?></td><td><?php echo _TYPE;?></td></tr><?php
	for ($i = 0; $i < $total; ++$i) {
		$b=$total-$i-1;?>
		<tr><td width="10%"><span class="dk_small"><?php if (isset($arr[$b]['date'])) echo lc_strftime('%Y-%m-%d %H:%M:%S',$arr[$b]['date']); else echo '-'; ?></span></td><td width="70%">  <a href="<?php echo $arr[$b]['url']; ?>"><?php echo $arr[$b]['title']; ?></a></td><td width="10%"><?php echo $arr[$b]['alt']; ?></td></tr><?php
	}?>
	</table><br /></td></tr></table></td>
