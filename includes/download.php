<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Download function
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# utility function to correctly serve download for binary/ASCII files
#

function send_download_headers_norange($dl_filename, $xtype) {
	header("Content-Transfer-Encoding: binary");
	header("Expires: 0");
	header("Accept-Ranges: bytes");
	// hack for IE5.5 and IE6
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($agent, 'MSIE 5'))
			$attch = '';
		else {
			$attch = 'attachment; ';
	//		if (strpos($agent, 'MSIE 6'))
	//			$xtype = 'application/download';
		}
	} else
		$attch = 'attachment; ';
	header("Content-Type: ".$xtype);
	// quotes added for Safari
	// removed rawurlencode() because messes up spaces
	header('Content-Disposition: '.$attch.'filename="'.$dl_filename.'";');
}

## sends the proper download headers
function send_download_headers($dl_filename, $filesize, $xtype) {
	
	send_download_headers_norange($dl_filename, $xtype);

	// try to disable possible ob_start()s
	CMSResponse::ClearOutput();
	
	//  multipart-download and resume-download
	if (CMSResponse::Compressing() || !isset($_SERVER['HTTP_RANGE'])) {
		header("Content-Length: ".$filesize);
		return array();
	}
	// validate the HTTP_RANGE
	$range = strstr($_SERVER['HTTP_RANGE'], 'bytes=');
	if ($range === false)
		return array();
	$end_offset = $filesize-1;
	$range = explode('-', substr($range, 6), 2);
	if (count($range)!=2) return 0;
	if (($range[0] > 0) && ($range[0] < $end_offset))
		$start = intval($range[0]);
	else return array();
	if (($range[1] > $start) && ($range[1]<=$end_offset))
		$end = intval($range[1]);
	else return array();
		
	// if there is a valid range, then use it
	// crop the chunk length to 2 MB
	$new_length = min($end-$start, 1 * 1024 * 1024);
	header("HTTP/1.1 206 Partial Content");
	header("Content-Length: $new_length");
	header("Content-Range: bytes $start-$end/$filesize");
	return array($start, $new_length);
}

## sends the actual file, taking care of the range if set
function actual_download($filename, $range) {
	if (!isset($range[0])) {
		safe_readfile($filename);
		return;
	}
	global $d;
	$d->log(1, "Serving $filename for download with range (".$range[0]." - ".$range[1].")");
	// serve the selected range of bytes
	$file = fopen($filename, 'rb');
	fseek($file, $range[0]);
	$buffer = fread($file, $range[1]);
	fclose($file);
	echo $buffer;
	flush();
}

include $d_root.'includes/mime.types.php';

## quick download function, the file MUST exist
function download($filename, $filesize, $give_fname=null) {
	// if given filename is not set, set it
	if (!isset($give_fname))	$give_fname=basename($filename);
	
	// guess the mime type
	global $mime;
	$ext = file_ext($give_fname);
	if (!isset($mime[$ext]))
		$xtype = $mime[''];
	else
		$xtype = $mime[$ext];

	$range = send_download_headers($give_fname, $filesize, $xtype);

	actual_download($filename, $range);
}

?>