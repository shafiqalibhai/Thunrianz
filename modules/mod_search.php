<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$module = $module['module'];

$path = mod_lang($my->lang, $module);
include_once $path;

//TODO: this should be done in CSS
$width = $params->get('width', 20);
$text = $params->get('text', _SEARCH_BOX);

?><form action="index.php" method="get">
<?php if ($module['instance']) {
?><input type="hidden" name="Itemid" value="<?php echo $module['instance']; ?>" /><?php } ?>
    <input type="hidden" name="option" value="search" />
	<input alt="search" class="dk_inputbox" type="text" name="q" id="q" size="<?php echo $width; ?>" value="<?php echo $text; ?>"  onblur="if(this.value=='') this.value='<?php echo js_enc($text); ?>';" onfocus="if(this.value=='<?php echo js_enc($text); ?>') this.value='';" />    
</form>
