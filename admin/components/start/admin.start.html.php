<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

	function segment($url, $title, $desc) {
		return '<strong><a href="'.$url.'">'.$title.'</a></strong><br/>'.$desc.'<br/>';
	}
	
	function segment_pair($a_url, $a_pic, $a_title, $a_desc, $a_ext = false, $a_w = null, $a_h = null,
							$b_url = null, $b_pic = null, $b_title = null, $b_desc = null,
							$b_ext = false,
							$b_w = null, $b_h = null) {
	if (!isset($b_url)) $colspan=' colspan="2"'; else $colspan='';
	?>
        <tr>
          <td<?php echo $colspan; ?>><table width="100%" border="0" cellspacing="0" cellpadding="5">
              <tr>
                <td class="grayborder" width="16">
<a href="<?php echo $a_url; if ($a_ext) echo '" target="_blank'; ?>"><img src="<?php echo $a_pic; ?>" border="0"<?php if (isset($a_w)) echo ' width="'.$a_w.'" '; if (isset($a_h)) echo ' height="'.$a_h.'" '; ?> alt="<?php echo $a_title; ?>" /></a></td>
                <td valign="top"><?php echo segment($a_url, $a_title,$a_desc); ?> </td>
              </tr>
            </table></td><?php if (isset($b_url)) {?>
          <td><table width="100%" border="0" cellspacing="0" cellpadding="5">
              <tr>
                <td class="grayborder" width="16">
<a href="<?php echo $b_url; if ($b_ext) echo '" target="_blank'; ?>"><img src="<?php echo $b_pic; ?>" border="0"<?php if (isset($b_w)) echo ' width="'.$b_w.'" '; if (isset($b_h)) echo ' height="'.$b_h.'" '; ?> alt="<?php echo $b_title; ?>" /></a></td>
                <td valign="top"><?php echo segment($b_url, $b_title,$b_desc); ?> </td>
              </tr>
            </table></td><?php } ?>
        </tr>
		<tr><td colspan="2">&nbsp;</td></tr>
<?php	
	}
	
	$d->add_css(rel_com_path('css'));
	
	// get all the available components
	$active_com = $conn->SelectColumn('#__components', 'option_link', ' WHERE admin_access<='.$my->gid);
	$START_DATA = array();
	if (in_array('com_content', $active_com))
		$START_DATA[] = 
			array( 'url' => 'admin.php?com_option=content&amp;option=items',
			'pic' => 'categories.png',
			'label' => _START_POST, 'desc' => _START_POST_DESC);
	if (in_array('com_templates', $active_com))
		$START_DATA[] =
			array('url' => 'admin.php?com_option=templates',
			'pic' => 'template.png',
			'label' => _START_TEMPLATE, 'desc' => _START_TEMPLATE_DESC);
	if (in_array('com_user', $active_com))
		$START_DATA[] = 
			array('url' => 'admin.php?com_option=user', 'pic' => 'users.png',
			'label' => _START_USERS, 'desc' => _START_USERS_DESC);
	if (in_array('com_config', $active_com))
		$START_DATA[] =
			array(
			'url' => 'admin.php?com_option=config', 
			'pic' => 'config.png', 'label' => _START_CONFIG, 'desc' => _START_CONFIG_DESC);
	$START_DATA[] =
		array('url' => create_context_help_url(), 'pic' => 'docs.png', 'label' => _START_HELP, 'desc' => _START_HELP_DESC,
		'external' => true, 'w' => 16, 'h' => 16);
	$START_DATA[] = 
		array('url' =>	'http://www.laniuscms.org/index2.php?option=syndicate&amp;no_html=1&amp;feed_type=rss_2_0', 'pic' => 'rss.png',
		'label' => _START_FEED, 'desc' => _START_FEED_DESC, 'external' => true, 'w' => 16, 'h' => 7);
	if (in_array('com_database', $active_com))
		$START_DATA[] = 
			array('url' =>	'admin.php?com_option=database', 'pic' => 'db.png',
			'label' => _START_BACKUP, 'desc' => _START_BACKUP_DESC, 'w' => 16, 'h' => 16);
	if (in_array('com_about', $active_com))
		$START_DATA[] =
			array('url' => 'admin.php?com_option=about', 'f_pic' => $d_subpath.'media/favicon.png',
			'label' => _START_ABOUT, 'desc' => _START_ABOUT_DESC, 'w' => 16, 'h' => 16);
?>
<table width="100%" border="0" align="center" cellpadding="6" cellspacing="0" >
<tr>
<td><?php include com_path('news'); ?></td>
<td><h2 align="center" class="startpageheader"><?php echo _START_HEAD; ?></h2>
      <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
	<?php
	$c=count($START_DATA);
	// split the elements in couples
	$c2 = (int)($c / 2);
	for($i=0;$i<$c2;++$i) {
		$a = $START_DATA[$i*2];
		$b = $START_DATA[$i*2+1];
		segment_pair(
			$a['url'], isset($a['pic']) ? admin_template_pic($a['pic']) : $a['f_pic'],
			$a['label'], $a['desc'], isset($a['external']), isset($a['w']) ? $a['w'] : null, isset($a['h']) ? $a['h'] : null,
			$b['url'], isset($b['pic']) ? admin_template_pic($b['pic']) : $b['f_pic'],
			$b['label'], $b['desc'], isset($b['external']), isset($b['w']) ? $b['w'] : null, isset($b['h']) ? $b['h'] : null
			);
	}
	// process the last element, if any
	if ($c % 2) {
		$a = $START_DATA[$c-1];
		segment_pair(
			$a['url'], isset($a['pic']) ? admin_template_pic($a['pic']) : $a['f_pic'],
			$a['label'], $a['desc'], isset($a['external']), isset($a['w']) ? $a['w'] : null, isset($a['h']) ? $a['h'] : null);
	}
?>
	</table></td>
</tr>
</table>