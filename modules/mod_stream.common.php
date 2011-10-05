<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Media Streaming module
# @author legolas558
#
# common include file (drabot and module)
#

/*$video_ext = array('mov', 'moov', 'qt', 'mpg', 'mpeg', 'wmv', 'avi', 'm1v', 'm2v', 'm4e', 'm4u', 'mp4', 'rm');
$audio_ext = array('mp3', 'ogg', 'wma', 'mid', 'kar', 'ac3', 'aif', 'aiff', 'm3u', 'm4a', 'midi', 'mp2', 'mpa', 'ram', 'wav'); */

function to_bool($b) {
	return $b ? 'true' : 'false';
}

function place_stream_object($url, $type, $hidden, $width, $height, $controls, $autostart) {
	switch($type) {
		case 'qt':
?><object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="<?php echo (1-$hidden)*$width;?>" height="<?php echo (1-$hidden)*$height;?>">
  <param name="src" value="<?php echo $url;?>" />
  <param name="controller" value="<?php echo to_bool($controls);?>" />
  <param name="autoplay" value="<?php echo to_bool($autostart);?>" />
  <!--[if !IE]>-->
  <object type="video/quicktime" data="<?php echo $url;?>" width="<?php echo (1-$hidden)*$width;?>" height="<?php echo (1-$hidden)*$height;?>">
    <param name="autoplay" value="<?php echo to_bool($autostart);?>" />
    <param name="controller" value="<?php echo to_bool($controls);?>" />
  </object>
  <!--<![endif]-->
</object><?php
	break;
	case 'real':?>
<object id="RVOCX" classid="CLSID:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="<?php echo (1-$hidden)*$width;?>" height="<?php echo (1-$hidden)*$height;?>">
<param name="SRC" value="<?php echo $url; ?>"><param name="AUTOSTART" value="<?php echo to_bool($autostart);?>">
<param name="CONTROLS" value="imagewindow">
<param name="CONSOLE" value="video">
<embed src="<?php echo $url; ?>" type="audio/x-pn-realaudio-plugin" nojava="true" controls="imagewindow" console="video" autostart="<?php echo to_bool($autostart);?>" width="<?php echo (1-$hidden)*$width;?>" height="<?php echo (1-$hidden)*$height;?>">
</object>
<?php if ($controls) { ?>
<object id="RVOCX" classid="CLSID:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" height="36" width="<?php echo (1-$hidden)*$width;?>"><param name="SRC" value="<?php echo $url; ?>"><param name="AUTOSTART" value="<?php echo to_bool($autostart);?>"><param name="CONTROLS" value="ControlPanel"><param name="CONSOLE" value="video"><embed src="<?php echo $url; ?>" type="audio/x-pn-realaudio-plugin" nojava="true" controls="ControlPanel" console="video" autostart="<?php echo to_bool($autostart);?>" height="36" width="<?php echo (1-$hidden)*$width;?>"></object>

<?php
	}
	break;
	case 'wm': ?>
<object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" id="player" width="<?php echo (1-$hidden)*$width;?>" height="<?php echo (1-$hidden)*$height;?>">
  <param name="url" value="<?php echo $url;?>" />
  <param name="src" value="<?php echo $url;?>" />
  <param name="showcontrols" value="<?php echo to_bool($controls);?>" />
  <param name="autostart" value="<?php echo $autostart;?>" />
  <!--[if !IE]>-->
  <object type="video/x-ms-wmv" data="<?php echo $url;?>" width="<?php echo (1-$hidden)*$width;?>" height="<?php echo (1-$hidden)*$height;?>">
    <param name="src" value="<?php echo $url;?>" />
    <param name="autostart" value="<?php echo $autostart;?>" />
    <param name="controller" value="<?php echo to_bool($controls);?>" />
  </object>
  <!--<![endif]-->
</object><?php
	break;
	case 'flash':
	global $my;
	include_once $GLOBALS['d_root'].'lang/'.$my->lang.'/modules/mod_stream.php';
	?><object type="application/x-shockwave-flash" data="<?php echo $url;?>" width="<?php echo $width;?>" height="<?php echo $height;?>">
<param name="movie" value="<?php echo $url;?>" />
<big><?php echo _STREAM_NO_FLASH; ?></big>
</object><?php
	break;
	default:?>
	<embed src="<?php echo $url; ?>" showcontrols="<?php echo $controls; ?>" autostart="<?php echo to_bool($autostart);
	if ($hidden)
		echo '" hidden="true';
	else { ?>" width="<?php echo (1-$hidden)*$width;?>" height="<?php echo (1-$hidden)*$height; }?>"><?php 
}

}

