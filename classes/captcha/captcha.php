<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
/*
* Original file: CaptchaSecurityImages.php
* Author: Simon Jarvis
* Modified by legolas558 for Lanius CMS
* Copyright: 2006 Simon Jarvis
* Date: 03/08/06
* Updated: 23/11/06
* Requirements: PHP 4/5 with GD and FreeType libraries
* Link: http://www.white-hat-web-design.co.uk/articles/php-captcha.php
* 
* This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 2 
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details: 
* http://www.gnu.org/licenses/gpl.html
*
*/

function generateCode($characters) {
	// list all possible characters, similar looking characters and vowels have been removed
	$possible = '235678BCFGKMNPRVWXYZ';
	$code = ' ';
	$i = 0;
	$l=strlen($possible)-1;
	while ($i < $characters) { 
//		do {
			$chosen = $possible{mt_rand(0, $l)};
//		} while (strpos($code, $chosen)!==false);
		$code .= $chosen;
		++$i;
	}
	return substr($code,1);
}


class Captcha {

	function Captcha($code,$font,$width,$height,$minAngle = 0, $maxAngle = 0) {
		/* font size will be 75% of the image height */
		$font_size = $height * 0.7;
		$image = imagecreate($width, $height);
		/* set the colours */
		$background_color = imagecolorallocate($image, 255, 255, 255);
		$text_color = imagecolorallocate($image, 20, 40, 100);
		$noise_color = imagecolorallocate($image, 100, 120, 180);
		
		$sz = $width*$height;		
		/* generate random dots in background */
		for( $i=0; $i<$sz/3; $i++ ) {
			imagefilledellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noise_color);
		}
		/* generate random lines in background */
		for( $i=0; $i<$sz/400; $i++ ) {
			imageline($image, mt_rand(0,$width), mt_rand(0,$height), mt_rand(0,$width), mt_rand(0,$height), $noise_color);
		}
		/* create textbox and add text */
		$textbox = imagettfbbox($font_size, 0, $font, $code);
		$x = ($width - $textbox[4])/2;
		$y = ($height - $textbox[5])/2;
		$l = strlen($code);
		$ch_w=($width-$x)/$l;
		for ($i=0;$i<$l;$i++) {
			imagettftext($image, $font_size, mt_rand(-$minAngle,$maxAngle), $x+$i*$ch_w, $y, $text_color, $font , $code{$i});
		}
		/* output captcha image to browser */
		imagepng($image);
		imagedestroy($image);
	}

}

?>