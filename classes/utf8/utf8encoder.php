<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## UTF-8 Encoder class
# @author legolas558
# Released under GNU GPL License
#
# this class will load a binary gzipped archive of codepages,
# setup the decoder and then translate strings accordingly
#
# original codepage mappings from ftp://ftp.unicode.org/Public/MAPPINGS/
#

if(!function_exists('str_split')){	//PHP5
   function str_split($string,$split_length=1){
       $count = strlen($string); 
       if($split_length < 1){
           return false; 
       } elseif($split_length > $count){
           return array($string);
       } else {
           $num = (int)ceil($count/$split_length); 
           $ret = array(); 
           for($i=0;$i<$num;$i++){ 
               $ret[] = substr($string,$i*$split_length,$split_length); 
           } 
           return $ret;
       }     
   } 
}

class UTF8Encoder {

	var $_map;
	
	// legolas558's GZA file format is under LGPL license
	function _gza_open($gza_file, $chunk) {
		$f = file_get_contents($gza_file);
		$c = current(unpack('V', substr($f, 0, 4)));
		$hdr_ofs = current(unpack('V', substr($f, 4, 4)));
		for($i=0;$i<$c;++$i) {
			if (trim(substr($f, 8+$i*32, 24))===$chunk) {
				$ds = current(unpack('V', substr($f, 8+$i*32 + 24, 4)));
				$sz = current(unpack('V', substr($f, 8+$i*32 + 28, 4)));
				return substr(gzuncompress(substr($f, $hdr_ofs)), $ds, $sz);
			}
		}
		return null;
	}
	
	function _trim(&$token) {
		if ($token[3]==="\x0") {
			if ($token[2]==="\x0") {
				if ($token[1]==="\x0")
					$token = $token[0];
				else
					$token = substr($token, 0, 2);
			} else
				$token = substr($token, 0, 3);
		}
	}
	
	function _decToBinary($n) {
		// shifts and modulo operator cannot be used because
		// PHP's shift and chr() clip their arguments to (2^31-1)
		$hex = str_pad(base_convert($n, 10, 16), 8, '0', STR_PAD_LEFT);
		return chr(hexdec($hex{6} . $hex{7})).chr(hexdec($hex{4} . $hex{5})).
				chr(hexdec($hex{2} . $hex{3})).chr(hexdec($hex[0] . $hex{1}));
/*		return chr(hexdec($hex[0] . $hex{1})).chr(hexdec($hex{2} . $hex{3})).
				chr(hexdec($hex{4} . $hex{5})).chr(hexdec($hex{6} . $hex{7}));	*/
	}
	
	function LoadCharset($charset) {
		$data = $this->_gza_open($GLOBALS['d_root'].'classes/utf8/codepages.gza', $charset);
		if (!isset($data))
			return false;
		$this->_map = str_split($data, 4);
		if (count($this->_map)<=256) {
			array_walk($this->_map, array(&$this, '_trim'));
			$this->_mapper = '_enc8bit';
		} else {	//TODO: 16bits encodings!
			$nmap = array();
			foreach($this->_map as $cp => $utf) {
				$nmap[$this->_decToBinary($cp)] = $this->_trim($utf);
			}
			$this->_map = $nmap;
			$this->_mapper = '_enc_mb';
		}
		return true;
	}
	
	function _enc8bit($s) {
		$l=strlen($s);
		$ec='';
		for($i=0;$i<$l;++$i) {
			$ec .= $this->_map[ord($s[$i])];
		}
		return $ec;
	}
	
	function _enc_mb($s) {
		return strtr($s, $this->_map);
	}
	
	function Encode($s) {
		return $this->{$this->_mapper}($s);
	}

}

?>