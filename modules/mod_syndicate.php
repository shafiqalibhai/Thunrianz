<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

global $d;		
$cparams = $d->GetComponentParamsRaw('syndicate');
if (!isset($cparams)) return;
global $d_subpath;
$pics = $params->get('show_images', 1);

if (!strlen($custom_options))
	$custom_options = 'option=syndicate';
else $custom_options = xhtml_safe($custom_options);

?><br /><?php
if ($cparams->get('rss_1_0_enabled', 1)) {
?><a href="index2.php?<?php echo $custom_options; ?>&amp;no_html=1&amp;feed_type=rss_1_0"><?php if (!$pics) echo 'RSS 1.0 |'; else { ?>
<img src="<?php echo $d_subpath; ?>modules/images/rss_1_0.png" alt="RSS 1.0" /><br />
<?php } ?></a><?php }
if ($cparams->get('rss_2_0_enabled', 1)) {?>
<a href="index2.php?<?php echo $custom_options; ?>&amp;no_html=1&amp;feed_type=rss_2_0"><?php if (!$pics) echo 'RSS 2.0 |'; else { ?>
<img src="<?php echo $d_subpath; ?>modules/images/rss_2_0.png" alt="RSS 2.0" /><br />
<?php } ?></a><?php }
if ($cparams->get('atom_1_0_enabled', 1)) {?>
<a href="index2.php?<?php echo $custom_options; ?>&amp;no_html=1&amp;feed_type=atom_1_0"><?php if (!$pics) echo 'Atom 1.0'; else { ?>
<img src="<?php echo $d_subpath; ?>modules/images/atom_1_0.png" alt="Atom 1.0" /><br />
<?php } ?></a>
<?php } ?>
<br />
<br />