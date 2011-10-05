<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Banner module
# @author legolas558
#
# common include file
#

function showbanner($id, $jsrotate, $module) {
	global $d,$conn,$d_website;

	if ($id!=='') $show ="id=$id AND";
	else {

	if ($jsrotate) { // create a javascript based rotator

	$rsa = $conn->SelectArray('#__banners', 'id,name,imageurl,clickurl', " WHERE published=1");
	$rnum = count($rsa);
	if (!$rnum) return;
	shuffle($rsa);
	$js = '
	var _mbid = 0;
	var _banners = [';
	foreach ($rsa as $row) {
		$js .= '['.$row['id'].", '".js_enc($row['imageurl'])."'";
		if (strlen($row['clickurl']))
			$js .= ', true';
		else
			$js .= ', false';
		$js .= "],\n";
	}
	$inst = module_instance($module['instance'], 'banner');
	$js = substr($js, 0, -2);
	$js .= "];\n\nfunction _rotateBanner() {
		_mbid++;
		if (_mbid>=_banners.length)
			_mbid=0;
		var _aitm = document.getElementById('abanner".$module['id']."');
		var _iitm = document.getElementById('ibanner".$module['id']."');
		_iitm.src = 'media/banners/'+_banners[_mbid][1];
		if (_banners[_mbid][2]) {
			_aitm.target = '_blank';
			_aitm.href = 'index2.php?option=banner&task=go&no_html=1".
			str_replace('&amp;', '&', $inst)."&id='+_banners[_mbid][0];
		} else {
			_aitm.target = '_self';
			_aitm.href = '#top';
		}
		setTimeout('_rotateBanner()', ".($jsrotate*1000).");
	}

	setTimeout('_rotateBanner()', ".($jsrotate*1000).");
	";

	echo $d->script($js);
	?><a id="abanner<?php echo $module['id']; ?>" href="index2.php?option=banner&amp;task=go&amp;no_html=1&amp;id=<?php echo $rsa[0]['id'].$inst; ?>" target="_blank" title="<?php echo xhtml_safe($rsa[0]['name']); ?>" ><img id="ibanner<?php echo $module['id']; ?>" src="media/banners/<?php echo $rsa[0]['imageurl']; ?>" border="0" alt="<?php echo xhtml_safe($rsa[0]['name']); ?>" /></a>
	<?php
	return;
	}

	$show = '';
	}

	$rsa = $conn->SelectArray('#__banners', 'id,name,imageurl,blanktarget,bannercode', " WHERE $show published=1");
	$rnum = count($rsa);
	if (!$rnum) return;
	if ($show=="")
		$row = $rsa[mt_rand(0, $rnum-1)];
	else
		$row = $rsa[0];
	$rsa = null;
	if($row['bannercode']!=='') {
		global $d;
		echo $d->script($row['bannercode']);
	}

	?><a title="<?php echo xhtml_safe($rsa[0]['name']); ?>" href="index2.php?option=banner&amp;task=go&amp;no_html=1&amp;id=<?php echo $row['id'];
	if ($row['blanktarget'])
		echo '" target="_blank';
	?>" ><?php
	if (file_ext($row['imageurl'])=='swf') { ?>
	<object width="468" height="60">
		<param name="movie" value="<?php echo $d_website; ?>media/banners/<?php echo $row['imageurl']; ?>">
				<param name="quality" value="high">
				<embed src="<?php echo $d_website; ?>media/banners/<?php echo $row['imageurl']; ?>" quality="high" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"; type="application/x-shockwave-flash" width="468" height="60"></embed>
	</object>
	<?php } else { ?><img src="media/banners/<?php echo $row['imageurl']; ?>" border="0" alt="<?php echo xhtml_safe($row['name']); ?>" /><?php } ?></a>
	  <?php
	change_val("banners",$row['id'],"imphits",1);
}

?>