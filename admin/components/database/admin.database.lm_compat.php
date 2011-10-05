<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Database compatibility functions
# @author legolas558
#
# sanitizes SQL so that it is conformant to the format used by Lanius CMS::
# contains hotfixes for Limbo's broken SQL
#

global $msword_cleanup;
$msword_cleanup = false;

function	lm_fix_sql(&$sql) {
	//1. identify the prefix (min 2, max 20) used throughout the SQL file
	if (preg_match('/create\\s+table\\s+(.{2,20}categories)\\s*\\(/i', $sql, $m)) {
		$p = strpos($m[1], 'categories');
		$prefix = preg_quote(strtolower(substr($m[1], 0, $p)));
	} else
		//TODO: proper error termination!
		trigger_error('Cannot find #__CATEGORIES table prefix');
		
	//1.a: rename lim* tables
	$sql = str_replace($prefix.'limbots', '#__drabots', $sql);
	
	//1.b: fix events table
	$sql = str_replace("date varchar(20) NOT NULL DEFAULT '0',", "date INTEGER NOT NULL DEFAULT 0,", $sql);

	//2. fix all tables prefixes
	if ($prefix!='#__') {
		$sql = preg_replace('/create\\s+table\\s+'.$prefix.'/i', 'CREATE TABLE #__', $sql);
		$sql = preg_replace('/drop\\s+table\\s+'.$prefix.'/i', 'DROP TABLE #__', $sql);
		$sql = preg_replace('/insert\\s+into\\s+'.$prefix.'/i', 'INSERT INTO #__', $sql);
	}

	//2.a convert the lim* to dra* references
	function db_alterate($m) {
		return preg_replace("/'lim(\\w+)/", '\'dra$1', $m[0]);
	}

	$sql = preg_replace_callback('/insert\\s+into\\s+#__drabots\\s*\\(.*\\)\\s+values\\s*\\(.*\\)\\s*;/i', 'db_alterate', $sql);


	function alterator($m) {
		global $alt;
		$p = strpos($m[0], $alt[0]);
		if ($p===false) {
			if ($alt[2])
				return str_replace('PRIMARY KEY', $alt[0].' '.$alt[1]."\r\n".
					'PRIMARY KEY', $m[0]);
		}
		
		if (!$alt[2])
			return preg_replace('/\\s+'.preg_quote($alt[0]).'\\s+([^,]+,)/', "\n".$alt[1], $m[0]);
		return $m[0];
	}

	function alter_table(&$sql,$table, $field, $def, $new=true) {
		global $alt;
		$alt[0] = $field;
		$alt[1] = $def.',';
		$alt[2] = $new;
		
		$sql = preg_replace_callback('/create\\s+table\\s+#__'.$table.
		'\\s+([^;]*\\);)/i', 'alterator', $sql);
	}
	global $alt;
	$alt = array();
	//3: add new fields
//	add_field($sql, 'downloads', 'access', 'int(3) NOT NULL DEFAULT \'0\'');
	alter_table($sql, 'downloads', 'author', 'varchar(100) NOT NULL DEFAULT \'\'');
	alter_table($sql, 'downloads', 'image_url', 'varchar(255) NOT NULL DEFAULT \'\'');
	alter_table($sql, 'downloads', 'mod_date', 'varchar(20) NOT NULL DEFAULT \'\'');
	alter_table($sql, 'downloads', 'down_date', 'varchar(20) NOT NULL DEFAULT \'\'');

	//4: alter tables
	alter_table($sql, 'downloads', 'date', 'add_date varchar(20) NOT NULL DEFAULT \'\'', false);
	alter_table($sql, 'content', 'created', 'created INTEGER NOT NULL', false);
	alter_table($sql, 'content', 'modified', 'modified INTEGER NOT NULL', false);
	alter_table($sql, 'sections', 'editgroup', "int(3) NOT NULL DEFAULT '0'");
	// now the user changes browser but Lanius CMS remembers his language
	alter_table($sql, 'users', 'lang', "varchar(2) NOT NULL DEFAULT 'en'");

	// fix the ip fields which are extended to 36 characters in Lanius CMS
	$oldfield = "/ip\\s+varchar\\(\\d+\\)/i";
	$newfield = 'ip varchar(36)';	// IPV6 does not scare us
	$sql = preg_replace($oldfield, $newfield, $sql);
	$oldfield = '/ip\\s+int\\(\\d+\\)/i';
	$sql = preg_replace($oldfield, $newfield, $sql);
	

//	$sql = str_replace("usertype varchar(25) NOT NULL DEFAULT '',", '', $sql);

	$sql = str_replace('message_subject text,', 'message_subject varchar(255),', $sql);

	//5: fix default declarations
	function def_reformat($m) {
		$s = str_replace( array('"', "'"), '', $m[2]);
		if (!$s)
			$s = 0;
		return 'int('.$m[1].') NOT NULL DEFAULT '.$s.',';
	}
	$sql = preg_replace_callback(
		'/inte?g?e?r?\\s*\\((\\d+)\\)\\s+NOT\\s+NULL\\s+DEFAULT\\s+(\'?0?\'?),/i',
				'def_reformat', $sql);

	//6: sanitize data from the old weird format
	// special characters will be handled by the db driver
	
	// fix the icons path
	function fix_gif_pictures($m) {
		global $d_root, $d_subpath;
		$pic = $m[0];
		$p=strrpos($pic, '.');
		$pic = substr($pic, 0, $p+1).'png';
		if (file_exists($d_root.$pic))
			return $pic;
		return $m[0];
	}
	
	$prev_sz = strlen($sql);
	function normalize($m) {
		$s = substr($m[0], 1, strlen($m[0])-2);
		if (!strlen($s)) return "''";

		$lev0 = str_replace(	array("[CR][NL]","[CR]","[NL]", "[ES][SQ]", "[ES][DQ]"),
							array("\n", "\n", "\n", "'", '"'), $s);
		// replace single quotes
		$lev0 = str_replace( array('’', '[SQ]', '[DQ]', '[ES]'), array("'", "'", '"', '\\'), $lev0);
		// here was the bug of bodytext being eaten!
		$lev0 = preg_replace_callback('/\\w+\\/.*?\\.gif/i', 'fix_gif_pictures', $lev0);
		return "'".
//		dkdb_encode(xhtml_safe($lev0, null), null)	// please check xhtml.php for proper filtering
		dkdb_encode($lev0)
		."'";
	}
	$sql = preg_replace_callback('/'.__QUOTED_S.'/', 'normalize', $sql);

	//7: cleanup the content texts
	global $msword_cleanup;
	if ($msword_cleanup) {
		global $d_root;
		include $d_root.'includes/msword_clean.php';
	}
	function _content_cleanup($m) {
		global $msword_cleanup;
		if ($msword_cleanup)
			return $m[1].msw_clean($m[2])."', '".msw_clean($m[3])."'";
		return $m[1].xhtml_filter($m[2])."', '".xhtml_filter($m[3])."'";
	}
	$sql = preg_replace_callback("/(content.*metadesc,access,hits\)[^V]*VALUES \\(\\d*+,'.*','.*',')(.*)','(.*?)'/", '_content_cleanup', $sql);

//	echo 'Bytes gain: '.($prev_sz-strlen($sql));		// debug line

	//8.b: fix Limbo SQL export bugs on ending parenthesis
	$sql = preg_replace('/,\\s*\\)\\s*;\\s*$/m', ',0);', $sql);

	//8.a: fix Limbo's SQL export bugs (missing ordering field)
	if (preg_match_all('/VALUES\\s+\\(.*?[\'\\d\\)](,,)[\'\\d\\)].*?\\);/',$sql,$m,PREG_OFFSET_CAPTURE)) {
		$ofs=0;
		foreach ($m[1] as $bug) {
			$sql = substr($sql, 0, $bug[1]+$ofs).
					',0,'.
					substr($sql, $bug[1]+2+$ofs);
			$ofs++;
		}
	}

/*//
	ob_end_clean();
	header('content-type: text/plain');
	echo($sql);
	die;	//*/

//	return $sql;
}

?>