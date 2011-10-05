<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
// --------------------------------------------------------------------------------
// PhpConcept Library (PCL) Error 1.0
// --------------------------------------------------------------------------------
// License GNU/GPL - Vincent Blavet - Mars 2001
// http://www.phpconcept.net & http://phpconcept.free.fr
// --------------------------------------------------------------------------------
// English :
//   The PCL Error 1.0 library description is not available yet. This library is
//   released only with PhpConcept application and libraries.
//   An independant release will be soon available on http://www.phpconcept.net
// --------------------------------------------------------------------------------

// ----- Look for double include
  define( "PCL_LIB", 1 );
  
  define('OS_WINDOWS', is_windows());
  
  // ----- Optional static temporary directory
  //       By default temporary files are generated in the script current
  //       path.
  //       If defined :
  //       - MUST BE terminated by a '/'.
  //       - MUST be a valid, already created directory
  define( 'PCL_TEMPORARY_DIR', $GLOBALS['d_root'].$GLOBALS['d_private'].'temp/' );


  // ----- Version
  $g_pcl_error_version = "1.0G";	// modified by legolas558

  // ----- Internal variables
  // These values must only be change by PclError library functions
  $g_pcl_error_string = "";
  $g_pcl_error_code = 1;


  // --------------------------------------------------------------------------------
  // Function : PclErrorLog()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclErrorLog($p_error_code=0, $p_error_string="")
  {
    global $g_pcl_error_string;
    global $g_pcl_error_code;

    $g_pcl_error_code = $p_error_code;
    $g_pcl_error_string = $p_error_string;
	
//	echo PclErrorString().'<br>';

  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclErrorFatal()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclErrorFatal($p_file, $p_line, $p_error_string="")
  {
    global $g_pcl_error_string;
    global $g_pcl_error_code;

    $v_message =  "<html><body>";
    $v_message .= "<p align=center><font color=red bgcolor=white><b>PclError Library has detected a fatal error on file '$p_file', line $p_line</b></font></p>";
    $v_message .= "<p align=center><font color=red bgcolor=white><b>$p_error_string</b></font></p>";
    $v_message .= "</body></html>";
    die($v_message);
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclErrorReset()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclErrorReset()
  {
    global $g_pcl_error_string;
    global $g_pcl_error_code;

    $g_pcl_error_code = 1;
    $g_pcl_error_string = "";
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclErrorCode()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclErrorCode()
  {
    global $g_pcl_error_code;
    
    return($g_pcl_error_code);
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclErrorString()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclErrorString()
  {
    global $g_pcl_error_string;
    global $g_pcl_error_code;

    return($g_pcl_error_string." [code $g_pcl_error_code]");
  }
  // --------------------------------------------------------------------------------
  
  function PclTempName($pfix = '') {
	return PCL_TEMPORARY_DIR.$pfix.sprintf('%X', mt_rand()).'.tmp';
  }
  
  // --------------------------------------------------------------------------------
  // Function : PclUtilTranslateWinPath()
  // Description :
  //   Translate windows path by replacing '\' by '/' and optionally removing
  //   drive letter.
  // Parameters :
  //   $p_path : path to translate.
  //   $p_remove_disk_letter : true | false
  // Return Values :
  //   The path translated.
  // --------------------------------------------------------------------------------
  function PclUtilTranslateWinPath($p_path, $p_remove_disk_letter=false)
  {
    if (!OS_WINDOWS) return $p_path;
      // ----- Look for potential disk letter
      if (($p_remove_disk_letter) && (($v_position = strpos($p_path, ':')) != false)) {
          $p_path = substr($p_path, $v_position+1);
      }
      // ----- Change potential windows directory separator
      if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0,1) == '\\')) {
          $p_path = strtr($p_path, '\\', '/');
      }
    return $p_path;
  }
  // --------------------------------------------------------------------------------
  
  // by legolas558
  function NormalizePath($path) {
	if (!strlen($path))
		return '';
	
	// remove windows drive letter and convert backslashes
	$path = PclUtilTranslateWinPath($path);
	
	// remove the trailing part (after the slash) if present
	$path = preg_replace('/\\/[^\\/]*$/', '/', $path);
	
	// force path relativization
	if (!preg_match('/'.(OS_WINDOWS ? '([A-Za-z]:\\/)|' : '').'(\\/)|(\\.\\/)|(\\.\\.)/A', $path))
		$path = './'.$path;
	
	return $path;
}

// the safe_is_dir and safe_is_file replacement functions have been introduced due to a PHP bug at handling file/folders under the Windows' temporary path
//NOTE: disabled because Lanius CMS no more uses the system temporary paths for archive extractions
/*
if (OS_WINDOWS) {
	if (strnatcmp(phpversion(), '5.2')>=0) {
		function safe_is_dir($dir) {		
			return is_dir($dir);
		}
	} else {
		function safe_is_dir($dir) {
			// check directory existance under temp folder (tested on Windows)
			// see bug #31918 http://bugs.php.net/bug.php?id=39198
			// by legolas558
			if (!@mkdir($dir))
				return true;
			rmdir($dir);
			return false;
		}
	}
		
	function safe_is_file($file) {
		$f=@fopen($file,'rb');
		if ($f===false) return false;
		fclose($f);
		return true;
	}
} else */
{	
	function safe_is_dir($dir) { return is_dir($dir); }
	function safe_is_file($file) { return is_file($file); }
}

//NOTE: all touch() calls  in PclZip/PclTar libraries must be silented with '@' because of bug [F5F8JZG1]

?>