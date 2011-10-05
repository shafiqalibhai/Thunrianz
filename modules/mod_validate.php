<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## W3C validations module
# @author legolas558
#

$show_xhtml = (int) $params->get( 'show_xhtml', 1 );
if ($show_xhtml) {
	$xhtml_version = $params->get( 'xhtml_version' );
	$xhtml_ver = str_replace('.', '', $xhtml_version);
}
$show_css = (int) $params->get( 'show_css', 1 );
if ($show_css) {
	$css_version = $params->get( 'css_version' );
	$css_ver = str_replace('.', '', $css_version);
}

if ($show_xhtml) { ?>
	<br />
	<a href="http://validator.w3.org/check?uri=referer" target="_blank"><img src="<?php echo $GLOBALS['d_subpath']; ?>modules/images/valid-xhtml<?php echo $xhtml_ver; ?>.png" alt="Valid XHTML <?php echo $xhtml_version; ?> Transitional" border="0" height="31" width="88" /></a>
<?php } ?>
<?php if ($show_css) { ?>
	<br />
	<a href="http://jigsaw.w3.org/css-validator/validator?uri=<?php
	global $d_website, $d_template;
	echo rawurlencode($d_website.'templates/'.$d_template.'/template.style.css').'&amp;warning=1&amp;profile=css'.$css_ver.'&amp;usermedium=all';
	?>" target="_blank"><img src="<?php echo $GLOBALS['d_subpath']; ?>modules/images/valid-css.png" alt="Valid CSS <?php echo $css_version; ?>" border="0" height="31" width="88" /></a>
<?php
	}
?>