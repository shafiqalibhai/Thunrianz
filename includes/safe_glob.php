<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Safe glob() replacement
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# a safe_glob() replacement for glob() on environments where glob() is not available
#

$tmp = @glob($d_root.'index.php');
if (!is_array($tmp)) {

//thanks to soywiz for the following function, posted on http://php.net/fnmatch
//soywiz at php dot net
//17-Jul-2006 10:12
//A better "fnmatch" alternative for windows that converts a fnmatch pattern into a preg one. It should work on PHP >= 4.0.0
if (!function_exists('fnmatch')) {
   function fnmatch($pattern, $string) {
       return @preg_match('/^' . strtr(addcslashes($pattern, '\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
   }
}

//safe_glob() by BigueNique at yahoo dot ca
//Function glob() is prohibited on some servers for security reasons as stated on:
//http://seclists.org/fulldisclosure/2005/Sep/0001.html
//(Message "Warning: glob() has been disabled for security reasons in (script) on line (line)")
//safe_glob() intends to replace glob() for simple applications
//using readdir() & fnmatch() instead.
//Since fnmatch() is not available on Windows or other non-POSIX, I rely
//on soywiz at php dot net fnmatch clone.
//On the final hand, safe_glob() supports basic wildcards on one directory.
//Supported flags: GLOB_MARK. GLOB_NOSORT, GLOB_ONLYDIR
//Return false if path doesn't exist, and an empty array is no file matches the pattern

function safe_glob($pattern, $flags=0) {
   $split=explode('/',$pattern);
   $match=array_pop($split);
   $path=implode('/',$split);
   if (($dir=lcms_opendir($path, false))!==false) {
       $glob=array();
       while(($file=readdir($dir))!==false) {
           if (fnmatch($match,$file)) {
               if (is_dir("$path/$file") || !($flags&GLOB_ONLYDIR)) {
                   if ($flags&GLOB_MARK) $file.='/';
                   $glob[]=$path.'/'.$file;
               }
           }
       }
       closedir($dir);
       if (!($flags&GLOB_NOSORT)) sort($glob);
       return $glob;
   } else
       return false;
}

} else {

	function safe_glob($pattern, $flags = GLOB_NOSORT) {
		return glob($pattern, $flags);
	}

}

?>