<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Thumbnailer class
# @author legolas558
# @version 0.1
# Released under GNU/GPL License
# This component is part of Lanius CMS core
#
# thumbnail generation code
#

define('_GALLERY_THUMBNAIL_SIZE', 100);
// here you can choose the default resize mode
define('_THUMBNAILER_DEFAULT_MODE', 0);

class Thumbnailer {
	## calculate new size
	function calcsize ($maxsize, $th_h, $th_w) {
		if ($maxsize != 0) {
			if ($th_w <= $th_h) {
				if ($th_h != $maxsize) {
					$th_ratio = ($th_h / $maxsize);
					$th_h = $maxsize;
					$th_w = round($th_w / $th_ratio);
				}
			} else {
				if ($th_w != $maxsize) {
					$th_ratio = ($th_w / $maxsize);
					$th_w = $maxsize;
					$th_h = round($th_h / $th_ratio);
				}
			}
		}
		return array ($th_h,$th_w);
	}
	
	function _resize_php($src, $dest, $max_sz) {
		$im_stat = GetImageSize($src);
		// pick original
		$width  = $im_stat[0];
		$height = $im_stat[1];
		list ($h,$w) = Thumbnailer::calcsize ($max_sz, $height, $width);
		// this is a null resize, just copy the file
		if (($h == $height) && ($w == $width))
			return copy($src, $dest);
		switch($im_stat[2]) {
				case 1:
					$im = @ImageCreateFromGIF ($src);
				break;
				case 2:
					$im = @ImageCreateFromJPEG ($src);
				break;
				case 3:
					$im = @ImageCreateFromPNG ($src);
				break;
				default:
					// 4 = SWF
					trigger_error('Unsupported format: '.$im_stat[2]);
					return false;
		}
		// quit if an unrecoverable error has happened
		if (!$im) return false;
		$imnew = ImageCreatetruecolor($w,$h);
		imagecopyresampled ($imnew,$im,0,0,0,0,$w,$h,$im_stat[0],$im_stat[1]);
		ImageJPEG($imnew,$dest,65);
		ImageDestroy($im);
		ImageDestroy($imnew);
		return true;
	}
	
	function _resize_aspx($src, $dest, $max_sz) {
		global $d_website,$d_root,$d_private;
		$rk = random_string(7);
		$temp_holder = $d_root.$d_private.'temp/'.$rk.'.tmp';
		$kf = fopen($temp_holder,'w');
		fputs($kf,$src);
		fclose($kf);
		$aspxurl = $d_website.'classes/thumbnailer/thumb.aspx?key='.$rk.'&max='.$max_sz;
		$thumbnail = get_url($aspxurl);
		unlink($temp_holder);
		if (($thumbnail===false) || (strlen($thumbnail)===0)) {
			global $d;
			$d->log(4, _THUMBNAILER_ASPNET_FAIL);
			return false;
		}
		if ($thumbnail[0]=='<') {
			global $d;
			$d->log(4, sprintf(_THUMBNAILER_ASPNET_NA, $src));
			return false;
		}
		return file_put_contents($dest, $thumbnail);
	}

	function resize_image($src, $dest, $max_sz, $mode = _THUMBNAILER_DEFAULT_MODE) {
		switch ($mode) {
			case 0:
				if (function_exists('ImageJPEG'))
					return Thumbnailer::_resize_php($src, $dest, $max_sz);
				return Thumbnailer::_resize_aspx($src, $dest, $max_sz);
			break;
			case 1:
				return Thumbnailer::_resize_php($src, $dest, $max_sz);
			break;
			case 2:
				// use thumbnail .NET wrapper
				return Thumbnailer::_resize_aspx($src, $dest, $max_sz);
		}
	}
	
	function make_thumbnail_from_url(&$fs, $url, $thumbs_path, $mode = 0) {
		global $d_temp;
		$fname = clear_name($url);
		$dest = $d_temp.random_string(7).'_'.$fname;
		if (get_url($url, $dest))
			return Thumbnailer::resize_image($dest, $thumbs_path.$fname,_GALLERY_THUMBNAIL_SIZE, $mode) && $fs->unlink($dest);
		return false;
	}

}

?>