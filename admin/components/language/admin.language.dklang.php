<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Admin language component
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# DKLANG format management functions
#

include_once $d_root.'admin/classes/fs.php';
global $_FS;
$_FS = new FS();

class Flags {

	var $_flags;
	var $_eng_rev;
	var $_lang_rev;
	
	## Creates a Flags object starting from the comma-separated flags list
	# The object will contain the english revision and the language revision fields
	function Flags(&$flags) {
		$this->_flags =& $flags;
		if (!count($this->_flags)) {
			$this->_lang_rev = 0;
			$this->_eng_rev = $GLOBALS['d__revision'];
		} else {
			$this->_lang_rev = (int)substr($this->_flags[0], 1);
			$this->_eng_rev = (int)substr($this->_flags[count($this->_flags)-1], 2);
		}
	}
	
	function GetFlags() {
		return _lr_compact_flags($this->_flags);
	}
	
	function Outdated() {
		return ($this->_lang_rev < $this->_eng_rev);
	}
	
	function DisplaySource($resource, $id, $def) {
		$col = ($this->Outdated() ? 'color:orange':'');

		$label = '<tt id="pr'.$id.''.$def.'" style="font-weight:bold; font-size: large;'.$col.'">';

		if (in_array('sprintf', $this->_flags))
			$label .= preg_replace('/(%[sd])/', '<span style="background-color: yellow">$1</span>', $resource);
		else
			$label .= $resource;
		if (in_array('special', $this->_flags))
			$label = "<span style='background-color:cyan'>".text_to_html(_LANGUAGE_SPECIAL_NOTICE)."</span>".$label;
		return $label.'</tt>';
	}
}

/* these are some of the flags currently used in language resources:

	rNNNN		revision "NNNN" where NNNN is a number with 4 ore more digits
	raw			this resource is not HTML-encoded
	sprintf		this resource contains parametric variables prefixed by '%'
	date			this resource contains date/time parameters prefixed by '%'
	special		this resource is considered special
*/

// given an array representation of flags, return the list in string format
function _lr_compact_flags($flags) {
	return implode(', ',$flags);
}

function _lr_parse_flags($base) {
	$base = trim($base);
	if (!strlen($base))
		return array();
	return array_map('trim', explode(',', $base));	
}

function _lr_merge_flags($base, $res) {
	// create the flags array
	$base = _lr_parse_flags($base);
	// if no base flags set, return empty array
	if (!count($base)) return array();
	// get the original revision
	$old_rev = (int)substr(array_shift($base), 1);
	// now parse the translated resource flags
	$res = _lr_parse_flags($res);
	// if it contains no flags, return a basic one
	if (!count($res)) {
		array_unshift($base, 'r0');
		$base[] = 'or'.$old_rev;
		return $base;
	}
	// get the language resource revision
	$lrev = array_shift($res);
	array_unshift($base, $lrev);
/*
	// add the other custom flags (without duplicates)
	foreach($res as $flag) {
		if (!in_array($flag, $base))
			$base[] = $flag;
	} */
	// finally add the language resource revision to the merged flags array
	$base[] = 'or'.$old_rev;
	return $base;
}

// copies only folders and files in subfolders
function txcopy(&$fs, $src, $to) {
	$done = 0;
	$folders = read_dir($src, 'dir');
	foreach ($folders as $dir) {
		$fs->assertdir($to.$dir);
		$done += $fs->xcopy($src.$dir.'/', $to.$dir.'/');
	}
	return $done;
}

function parsefiles($root, $folder, $f, &$valid_files) {
	$files = read_dir($root.$folder, 'file', false, array('php', 'js', 'png'));
	sort($files);
	$folders = read_dir($root.$folder, 'dir');
	foreach ($files as $file) {
		if (!in_array($folder.$file, $valid_files))
			unlink($root.$folder.$file);
		else
			fwrite($f, "\t\t\t\t".'<filename>'.$folder.$file.'</filename>'."\n");
	}
	foreach ($folders as $nf) {
		parsefiles($root, $folder.$nf.'/', $f, $valid_files);
	}
}

function la_load_def($fname, $pick_flags = false) {
	global $total;
	$defines = array();
	
	$rd = file_get_contents($fname);
	$c = preg_match_all("/define\\s*\(\\s*('|\")([A-Z_0-9]+)('|\")/i", $rd, $original);
	$original = $original[2];
	if (preg_match_all("/(?i:define)\\s*\\(\\s*('|\")([A-Z0-9_]+)('|\")\\s*,".
						"\\s*('|\")(.*?)('|\")".
						// additional regex for comment parsing (containing flags)
						"\\s*\\);".($pick_flags ? '\\s*\\/\\*([^\\*]*)\\*\\/':'')."/U", $rd, $m)) {
		if (!$pick_flags) {
			// don't parse the flags, just assign keys -> values
			foreach($m[2] as $k) {
				$defines[$k] = str_decode(current($m[5]));
				next($m[5]);
			}
		} else {
			// parse also the flags
			global $dklang_flags;
			$dklang_flags = array();
			foreach($m[2] as $k) {
				$defines[$k] = str_decode(current($m[5]));
				$dklang_flags[$k] = trim(current($m[7]));
				next($m[5]);
				next($m[7]);
			}
		}
	} else {
		if ($pick_flags) {
			// blank flags array
			global $dklang_flags;
			$dklang_flags = array();
		}
	}
	if (count($defines)!=$c) {
		global $d;
		$d->log(1, sprintf('Original resource file '.$fname.' has %d resources but %d have been parsed', $c, count($defines)));
	}
	return $defines;
}

function dklang_parse_definitions(&$gui, $lang, $filename, $id) {
	global $d_root, $dklang_flags;
	// $lang_subpath not needed here because this is an UI function

	$base_defines = la_load_def($d_root.'lang/en/'.$filename, true);
	if (empty($base_defines))
		return;
		
	$orig_flags = $dklang_flags;
	
	$lang_root = $d_root.'lang/'.$lang.'/';
	$defines = la_load_def($lang_root.$filename, true);
	if (empty($defines))
		return;
	
	$defines_count = count($defines);
		
	$head_item =& $gui->add("tab",'',_LANGUAGE_RESOURCES,"dtab");
	$head_desc =& $gui->add('text');
	$gui->add('spacer');
	
	global $d_subpath;
	$outdated = 0;
//	$js = '';
	foreach($defines as $define => $value) {
		// give a bigger textarea if needed
		$l = strlen($value);
		if (($l<50) && (strpos($value, "\n")===false))
			$area_type = 'textfield';
		else if ($l<100)
			$area_type = 'textarea';
		else
			$area_type = 'textarea_big';
		
		// initialize the flags
		$flags = _lr_merge_flags($orig_flags[$define], $dklang_flags[$define]);
		$flags_obj = new Flags($flags);
		
		// if this is not the English language
		if ($lang!='en') {
			// create the label of the original English language resource
			$label = $flags_obj->DisplaySource(wordwrap($base_defines[$define], 50,
						'<img src="'.$d_subpath.'admin/templates/default/images/par.png" alt="" />'."\n"),
						$id, $define);
			// increment if this resource is outdated
			if ($flags_obj->Outdated())
				++$outdated;
			// display the flags bar, language text boxes and hidden flags field
			$gui->add('text', '', $define.' <span style="font-weight: normal" id="fp'.$id.''.$define.
				'">'.($flags_obj->Outdated() ? _LANGUAGE_OUT_OF_DATE : _LANGUAGE_UPDATED).'</span>');
		} else // if editing the own English language
			$label = $define;
		$gui->add($area_type, 'lr['.$id.']['.$define.']', '<p>'.$label.'</p>', $value,
			null, 'onkeyup="resource_changed('.$id.', \''.js_enc($define).'\')"');
			$label = null;
		
		$flags = _lr_compact_flags(array_slice($flags, 0, -1));
//		$gui->row('text', '', '<input type="hidden" id="lf'.$id.''.$define.'" name="lf['.$id.']['.$define.']" value="'.xhtml_safe($flags).'" />');
		$gui->add('spacer');
		
//		$js .= "resource_flags['$define']={'lang_rev':".$flags_obj->_lang_rev.",'eng_rev':".$flags_obj->_eng_rev.",'flags':'".$flags."'};\n";

	} $defines = null;

	if ($outdated)
		$info = ', <span style="color:orange">'.$outdated.' '._LANGUAGE_OUTDATED.'</span>';
	else $info = '';
	
	$head_item['name'] = $filename.
						($outdated ? ' ('.$outdated.'/'.$defines_count.' '._LANGUAGE_OUTDATED.')':'');
	$head_desc['desc'] = sprintf( _LANGUAGE_RES_COUNT, '<big>'.$defines_count.'</big> ').$info;
	
	$gui->add("tab_end");

//	global $d;
//	$d->add_raw_js($js);
}

function parse_lang_xml($lang, &$name, $extra=false) {
	global $d_root, $lang_subpath;

	$xml = new AnyXML();
	if (!$xml->fromString(file_get_contents($d_root.$lang_subpath.$lang.'/language.xml'))) {
		echo 'Error reading XML file';
		return false;
	}
	
	$xml = $xml->getElementByPath('languages/language');
	if (!isset($xml) || !($files = $xml->getElementByPath('files'))) {
		echo 'Invalid XML file';
		return false;
	}
	
	$e = $xml->getElementByPath('name');
	$name = $e->getValue();
	if ($extra) {
		$e = $xml->getElementByPath('version');
		$ver = $e->getValue();
		$e = $xml->getElementByPath('author');
		$author = $e->getValue();
		$e = $xml->getElementByPath('authorEmail');
		if (isset($e))
			$authorEmail = $e->getValue();
		else $authorEmail = '';
		$name = array($name, $ver, $author, $authorEmail);
	}
	
	$childs = $files->getAllChildren();
	$files = array();
	while ($child = current($childs)) {
		$file = $child->getValue();
		if (file_ext($file)=='php')
			$files[] = $file;
		next($childs);
	}
	return $files;
}

function write_language_descriptor($lang_cc, $lang_name, $lang_version, $lang_author, $lang_email, &$valid_files) {
	global $d_root, $lang_subpath;
	
	$lang_name = safe_html_entity_decode($lang_name);
	$lang_author = safe_html_entity_decode($lang_author);
		$raw_xml = '<?xml version="1.0" encoding="utf-8"?>
<dkinstall version="0.2">
	<languages>
		<language>
			<id>'.$lang_cc.'</id>
			<name>'.$lang_name.'</name>
			<version>'.$lang_version.'</version>
			<creationDate>'.lc_date('Y-m-d').'</creationDate>
			<author>'.$lang_author.'</author>
			<authorEmail>'.$lang_email.'</authorEmail>
			<license>GNU/GPL v2</license>
			<requirements>
				<core>
					<version>'.cms_version().'</version>
				</core>
			</requirements>
			<files>
';
		global $_FS;
		$fname = $d_root.$lang_subpath.$lang_cc.'/language.xml';
		$f = $_FS->write_open($fname);
			fwrite($f,$raw_xml);
			parsefiles($d_root.$lang_subpath.$lang_cc.'/', '', $f, $valid_files);
			fwrite($f, '</files>
		</language>
	</languages>
</dkinstall>');
		$_FS->write_close($f, $fname);
}

/*
function _copy_outdate($src, $dst) {
	global $dklang_flags;
	$defines = la_load_def($src, true);
	$keys = array_keys($dklang_flags);
	foreach($keys as $k) {
		$flags = _lr_merge_flags($dklang_flags[$k], '');
		echo '<pre>';var_dump($flags);echo '</pre>';die;
	}
}
*/

function repair_language($lang, $echo_cb) {
	$en_files = parse_lang_xml('en', $lang_name);
	if (!$en_files) {
		$echo_cb(sprintf(_LANGUAGE_CANNOT_FIND, 'EN'));
		return -1;
	}
	
	$files = parse_lang_xml($lang, $xml_info, true);
	if (!$files) {
		$echo_cb(sprintf(_LANGUAGE_CANNOT_FIND, raw_strtoupper($lang)));
		return -2;
	}
	
	global $d_root;
	
	$xml_upd = false;
	
	include_once $d_root.'admin/classes/fs.php';
	$fs = new FS();

	// check for files not currently indexed in en/language.xml
	global $lang_subpath;
	$fkeys=array_flip($files);
	foreach ($files as $file) {
		if (!in_array($file, $en_files)) {
			$fname = $d_root.$lang_subpath.$lang.'/'.$file;
			$fs->assert_remove($fname);
			$echo_cb($file.' '._LANGUAGE_REMOVED);
			unset($files[$fkeys[$file]]);
			$xml_upd = true;
		}
	} $fkeys = null;

	$modified = 0;

	global $dklang_flags;
	// check for files indexed in en/language.xml but not on this language
	foreach ($en_files as $file) {
		$dest = $d_root.$lang_subpath.$lang.'/'.$file;
		if (!in_array($file, $files) || !file_exists($dest)) {
			$fs->assertdir(dirname($dest));
			//_copy_outdate($d_root.'lang/en/'.$file, $dest);
			$fs->copy($d_root.$lang_subpath.'en/'.$file, $dest);
			$echo_cb($file.' '._LANGUAGE_ADDED);
			$xml_upd = true;
//			continue; 
			$copycat = true;
		} else $copycat = false;
		// load the English language resource with flags
		$en_defines = la_load_def($d_root.$lang_subpath.'en/'.$file, true);
		if (!count($en_defines))	// if there are no definitions, skip this file
			continue;
		// make a backup of the original flags variable
		$orig_flags = $dklang_flags;
//		$missing = array();
		if (!$copycat) {
			// load the destination language resources with flags
			$defines = la_load_def($dest, true);
		} else
			$defines = array();
		$added = 0;
		$removed = 0;
/*		// deprecated
		if (count($missing)) {
			foreach ($missing as $mdef) {
				if (isset($en_defines[$mdef])) {
					$defines[$mdef] = $en_defines[$mdef];
					++$added;
				}
			}
			unset($missing);
		} */
		$en_dkeys = array_keys($en_defines);
		if (!$copycat)
			$dkeys = array_keys($defines);
		if (!$copycat) {
			foreach($en_dkeys as $dk) {
				// if the language resource does not exist in this language, add it (from English)
				if (!in_array($dk, $dkeys)) {
					$defines[$dk] = $en_defines[$dk];
					// generate an array with r0
					$flags = _lr_merge_flags($orig_flags[$dk], '');
					// remove the original revision "orNNNN" flag
					$dklang_flags[$dk] = _lr_compact_flags(array_slice($flags, 0, -1));
					++$added;
				}
			}
			foreach($dkeys as $dk) {
				// if the language resource exists in this language but not in the English language resources for this file
				// remove it from the array
				if (!in_array($dk, $en_dkeys)) {
					unset($defines[$dk]);
					unset($dklang_flags[$dk]);
					++$removed;
				}
			}
		} else {
			foreach($en_dkeys as $dk) {
				$defines[$dk] = $en_defines[$dk];
				// generate an array with r0
				$flags = _lr_merge_flags($orig_flags[$dk], '');
				// remove the original revision "orNNNN" flag
				$dklang_flags[$dk] = _lr_compact_flags(array_slice($flags, 0, -1));
				++$added;
			}
		}
		if ($added || $removed) {
			$modified += $added;
			$modified += $removed;
			save_defines($dest, $defines);
			if ($added)
				$echo_cb($file.':'.$added.' '._LANGUAGE_RESOURCES_ADDED.
				($copycat ? ' ('." added new file".')': ''));
			if ($removed)
				$echo_cb($file.':'.$removed.' '._LANGUAGE_RESOURCES_REMOVED);
		}
	}
	
	if (!$modified && !$xml_upd) {
		$echo_cb(_LANGUAGE_UP_TO_DATE);
		return 0;
	}

	if ($xml_upd) {
		write_language_descriptor($lang, $xml_info[0], $xml_info[1], $xml_info[2], $xml_info[3], $en_files);
		$modified++;
	}
	
	$echo_cb(_LANGUAGE_REPAIRED);
	return $modified;
}

function atomic_lang_op($lang, $callback, $echo_cb) {
	global $d_root;
	
	$files = parse_lang_xml($lang, $lang_name);
	if (!$files) {
		$echo_cb("Could not read the list of files");
		return -256;
	}
	
	$n = null;
	$rv = 0;
	$failure = false;
	$cf = count($files);
//	global $basedef_pool;
	global $lang_subpath;
	for ($id=0;$id<$cf;$id++) {
		$lang_root = $d_root.$lang_subpath.$lang.'/';
		if (!file_exists($lang_root.$files[$id])) {
			$echo_cb("ERROR: file not found: ".fix_root_path($lang_root.$files[$id]));
			$failure = true;
			continue;
		}
//		echo "Reading ".fix_root_path($lang_root.$files[$id])."...";
		$defines = la_load_def($lang_root.$files[$id], true);
//		echo count($defines)." definitions found\n";
		$lang_root = $d_root.$lang_subpath.'en/';
		if (!file_exists($lang_root.$files[$id])) {
			$echo_cb( "ERROR: file not found: ".fix_root_path($lang_root.$files[$id]));
			$failure = true;
			continue;
		}
//		echo "Reading ".fix_root_path($lang_root.$files[$id])."...";
/*
		// use a pool for the base definitions - will shortcut processing time in case of sequential calls to atomic_lang_op()
		if (!isset($basedef_pool[$files[$id]]))
			$basedef_pool[$files[$id]] = la_load_def( $lang_root.$files[$id];
		$base_defines = $basedef_pool[$files[$id]]; */
		$base_defines = la_load_def($lang_root.$files[$id]);
		
//		echo count($base_defines)." definitions found\n";
		
		$dk = array_keys($defines);
		$bk = array_keys($base_defines);
		sort($dk);	sort($bk);
		if ($dk!=$bk) {
			$echo_cb("ERROR: definition identifiers do not match in file ".fix_root_path($lang_root.$files[$id])." (repair needed)");
			$failure = true;
			continue;			
		}
		
		if (!$callback($lang, $defines, $base_defines, $files[$id], $rv, $echo_cb))
			break;
	}
	
	$echo_cb("$cf files processed");
	if ($failure)
		return -256;
	return $rv;
}

function normalize_cb($lang, &$defines, &$base_defines, $filename, &$rv, $echo_cb) {
	global $d_root, $lang_subpath;
	$filename = $d_root.$lang_subpath.$lang.'/'.$filename;
	save_defines($filename, $defines);
	$echo_cb(fix_root_path($filename)." normalized");
	$rv++;
	return true;
}

global $all_defines;
$all_defines = array();

function verify_cb($lang, &$defines, &$base_defines, $filename, &$rv, $echo_cb) {
	global $all_defines, $lang_subpath;
	if ($filename=='common.php') {
		$a=explode("\n", $defines['_LOCALE']);
		$l = strlen($a[0]);
		if ($l!=2 && $l!=3)
			$echo_cb("WARNING: _LOCALE's first value should be a 2 or 3 letters ISO identifier (\"".$a[0]."\" was found)");
	}
	$head = false;
	foreach($defines as $k => $def) {
		$bdef = $base_defines[$k];
		
		if (isset($all_defines[$k]))
			$echo_cb("WARNING: $filename contains a definition for $k, but one was already defined in ".$all_defines[$k]);
		else
			$all_defines[$k] = $filename;
		
		if (strpos($def, '<')!==false) {
			$echo_cb("In file ".fix_root_path($GLOBALS['d_root'].$lang_subpath.$lang.'/'.$filename).":");
			$head = true;
			$echo_cb("WARNING: invalid HTML character found in resource $k (normalization needed)");
			$rv--;
		}
		
		// ignore '%%'
		$c=substr_count(str_replace($def, '%%', ''), '%');
		$bc=substr_count(str_replace($bdef, '%%', ''), '%');
		if ($c!=$bc) {
			if (!$head) {
				$echo_cb("In file ".fix_root_path($GLOBALS['d_root'].$lang_subpath.$lang.'/'.$filename).":");
				$head = true;
			}
			$echo_cb("ERROR: sprintf() special parameters '%' count mismatch for resource ".$k.
				" (found ".$c.' but should be '.$bc.")");
			$rv--;
		}
	}
	
	return true;
}

// encode the PHP string
function _lr_format($s, $encode) {
	if ($encode)
		$s = xhtml_safe($s);
	return preg_replace('/(\r\n)|(\r)|(\n)/', '\\n', str_replace(array('\\', '"'), array('\\\\', '\\"'), $s));
}

function dklang_language_update($lang, &$files) {
	// pick definitions which have been modified
	$defines = in_arr2('lr', __RAW, $_POST);
	if (!isset($defines))
		return;
	
	// update flags using English flags and current revision
	global $dklang_flags, $d_root;
	foreach($defines as $i => $def) {
		// this will set $dklang_flags to English language resource flags
		la_load_def($d_root.'lang/en/'.$files[$i], true);
		save_defines($d_root.'lang/'.$lang.'/'.$files[$i], $def, true);
	}
}

function _lr_auto_flags($flags, &$res) {
	if (strlen($flags)) return $flags;
// global $d__revision;
	return 'r0'.((strpos($res, '%')!==false)?', sprintf':'');
}

function save_defines($fname, &$defines, $need_encode = false) {
	global $dklang_flags, $_FS;
	$f = $_FS->write_open($fname);
		fwrite($f, '<'.'?p'.'hp /* DKLANG v0.1 format - saved with Lanius CMS v'.cms_version()." */\n".
		'// DO NOT edit this file manually, see '.create_context_help_url('I18N/Language_files', true)."\n");
	
//	if (!$defines)
//		trigger_error('Invalid values for $defines array');
	foreach($defines as $dk => $val) {
		// there is no need to apply xhtml_safe as the input has not yet been touched by browsers
		$fixed = _lr_format($val, $need_encode && (strpos($dklang_flags[$dk], 'raw')===false));
		fwrite($f, 'define(\''.$dk.'\', "'.$fixed.'"); /* '._lr_auto_flags($dklang_flags[$dk], $val).' */'."\n");
	}
	fwrite($f, '?'.'>');
	$_FS->write_close($f, $fname);
}

?>
