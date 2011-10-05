<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Database restore procedures
# @author legolas558
#
#

// DO NOT, I repeat, DO NOT CHANGE THE BELOW LINE
global $svn_support;
	$svn_support = false;
// SVN database backups are *NOT* supported
// Please don't waste developers' time with your broken SVN database backup

/*
function _db_consistent_precheck() {
	global $d_version, $svn_support;
	if (!$svn_support) {
		if (strpos($d_version, 'SVN')!==false) {
			echo '<h3>'._DB_IMPORT_NOTICE.'</h3><p>';
			echo _DB_IMPORT_NOTICE_SVN;
			echo '</p>';
			return false;
		}
	}
	return true;
}*/

function _convert_old_params($txt) {
	$params = array();
	if (strlen($txt)) {
		if (preg_match_all('/^\\s*(\\w+)=(.*?)\\s*$/sm', $txt, $m)) {
			foreach($m[1] as $item) {
				$params[$item] = current($m[2]);
				next($m[2]);
			}
		}
	}
	return $params;
}

function _db_consistent_check($oldver, $is_svn) {
	global $d_version, $svn_support;
	// since v0.4.8 we have a 'packages' table which contains the CMS version in the Lanius CMS record
	if (strnatcmp($oldver, '0.4.8')>=0) {
		// SVN backups are not supported
		if ($is_svn) {
			echo '<h3>'._DB_IMPORT_ERROR.'</h3><p>';
			echo _DB_IMPORT_ERROR_SVN;
			echo '</p>';
			//TODO: might allow SVN imports from same version
			if (!$svn_support)
				return false;
		}
		echo '<h3>'._DB_IMPORT_SOURCE_VER.'</h3><p>';
		echo sprintf(_DB_IMPORT_SOURCE, $oldver);
		echo '</p>';
	}
	return true;
}

function database_restore($basefn, &$dbbak, $split_job = true, $raw_data = null) {
	global $d_root, $d_private, $conn, $time;
	global $sqlfile;
	if (isset($raw_data))
		$hdr = open_dkdb_raw($raw_data);
	else
		$hdr = open_dkdb($basefn);
	// if it has not header we assume it comes from the old lembo
	$lm_compat = !isset($hdr[0]);
	if (!$lm_compat) {	// if there is a header, check the SQL version against the current Lanius CMS version
		// check for newer versions import
		if (strnatcmp($hdr[1], cms_version())>0)
			return 'Cannot import database from newer versions into lower versions';
		$oldver = $hdr[1];
		$is_svn = $hdr[3];
	} else {
		$oldver = '';
		$is_svn = false;
	}
	// get the full query (with headers)
	if (file_ext($basefn)=='gz') {
		if (isset($raw_data)) {
			$len = substr($raw_data, -4);
		} else {
			$f=fopen($basefn, 'rb');
			fseek($f, -4, SEEK_END);
			$len = fread($f, 4);
			fclose($f);
		}
		$len = unpack("V", $len);
		$len = end($len);
		if ($len < 0)
			return 'Corrupt gzipped database';
//					$maxmem = (int)(return_bytes(ini_get('memory_limit'))*0.7);
		if (isset($raw_data))
			$query = gzuncompress(substr($raw_data, 4, $len));
		else {
			$f = gzopen($basefn, "rb");
			$query = gzread($f, $len);
			gzclose($f);
		}
	} else {
		if (isset($raw_data))
			$query =& $raw_data;
		else
			$query = file_get_contents($basefn);
	}
	// check that the database being imported is OK
	if (isset($is_svn)) {
		if (!_db_consistent_check($oldver, $hdr[3]))
			return '';
	}
	if ($lm_compat) {
					// two heuristic functions to determine the format of the database
					function _is_limbocms($sql) {
						return preg_match('/CREATE TABLE [^_]+_+categories \\(\\s*'.
						'id\\s+int(\\(10\\)\\s+)?auto_inc(rement)?,\\s*parent_id/i', $sql);
					}
					function _maybe_drakecms($sql) {
						return strpos(substr($sql, 0, 1024), array('/* DKDB', '/* DKPDL'))!==false;
					}
					// check that this is really a Limbo CMS database
					if (!_is_limbocms($query)) {
						echo '<h3>'._DB_IMPORT_ERROR.'</h3><p>';
						if (_maybe_drakecms($query))
							echo _DB_IMPORT_ERROR_WEIRD_DKDB;
						else
							echo _DB_IMPORT_ERROR_NOT_LM;
						echo'</p>';
						return '';
					}
					
					// apply old CMS conversion
					include com_path('lm_compat');
					lm_fix_sql($query);

					// create only when not in raw_data mode
					if (!isset($raw_data)) {
						// now save a backup of the finished converted SQL data
						$tmp = $d_root.$d_private.'backup/'.$time.'_'.$sqlfile.'_converted.php';
						include_once $d_root.'admin/classes/fs.php';
						$fs = new FS();
						$f = $fs->write_open($tmp);
						// two is megl che one
						fwrite($f, dkdb_h(5));
						fwrite($f, dkdb_h(5));
						fwrite($f, "/* DKDB PREFIX = ".$hdr[2]." */\n\n");
						fputs($f,$query);
						$fs->write_close($f, $tmp);
					}
	}
				
	// will need to recode in case of database different from Gladius DB
	global $d_db;
	$query_arr = split_sql($query, ($d_db!='gladius'), (strnatcmp($oldver, '0.3.4')<0)||$lm_compat);	unset($query);
	
	// retrieve SVN and version from queries
	// we need it to perform the pre-check
	if (!isset($is_svn) && (strnatcmp($oldver, '0.4.8')>=0)) {
		//var_dump($query_arr);die;
		foreach($query_arr as $query) {
			if (!preg_match('/INSERT\\s+INTO\\s+.*?_packages/sA', $query)) continue;
			//var_dump($query);die;
			if (preg_match('/INSERT\\s+INTO\\s+.*?_packages\\s+\\(id,\\s*type,\\s*name,\\s*version\\)\\s*'.
						'VALUES\\s*\\(\\s*\\d+,\\s*\'core\',\\s*\'(Lanius|Drake) CMS\',\\s*\'([^\']+)\'\\)'.
						'/sA', $query, $m)) {
				$tver = explode(' ', $m[2]);
				$tver = current($tver);
				if ($oldver != $tver) {
					echo sprintf('DB version %s not matching backup version %s', $oldver, $m[2]);
//					return '';
				}
				$is_svn = (strpos($m[2], 'SVN') !== false);
				break;
			}
		}
		if (!isset($is_svn)) {
			echo "Could not find CMS package";
			return '';
		}
		if (!_db_consistent_check($oldver, $is_svn))
			return '';
	}
	
	require $d_root.'install/install_lib.php';

	if ($split_job) {
		// take care of the #__users table (needed for Lanius CMS to live) and get the current admin password before restoring anything
				$apwdh = admin_pwd();
				$c=count($query_arr);
				if (!$c)
					return 'No queries found';
				
				global $d_uid;
				// fix the user table before continuing
				if (!isset($_SESSION[$d_uid.'-userfixed'])) {
					$created = false;
					for($i=0;$i<$c;++$i) {
						// if this SQL statement affects the user table, then execute it
						if (preg_match('/((drop\\stable)|(create\\stable)|(insert\\sinto))\\s+'.
							$hdr[2].'users/iA',	$query_arr[$i])) {
							$conn->PrefixExecute($query_arr[$i], $hdr[2]);
							if (!$created)
								$created = (preg_match('/create/A',$query_arr[$i])!=0);
							else {
								$row = $conn->SelectRow('#__users', 'id', ' WHERE gid=5');
								// continue executing queries until an admin user is found, then impersonate it and break
								if (count($row)) {
									global $d_uid;
									$_SESSION[$d_uid.'-uid'] = $row['id'];
									break;
								}
							}
							unset($query_arr[$i]);
						}
					}
					// if some queries were already run, re-index the query array
					if (count($query_arr)!=$c)
						$query_arr=array_values($query_arr);
					else
						// otherwise if no queries were run, add the current admin user
						admin_insert($conn, $apwdh);

					// flag that we are done with the users table
					$_SESSION[$d_uid.'-userfixed'] = true;
				}

				include $d_root.'admin/classes/sqljobsplitter.php';
				global $d_uid;
// enable the following line to reset (debug)
//				$_SESSION[$d_uid.'sqljobplitter']= array();

				$sjs = new SQLJobSplitter($_SESSION[$d_uid.'-sqljobplitter'], count($query_arr));

				function sjs_hook($finished, $current, $total) {
					echo '<h3>'._DB_OPERATION_STATUS.'</h3>';
					if (!$finished) {
						global $sqlfile;
						echo ((int)($current*100/$total)).'% complete';
						?><p><form name="sjscontinue" method="post" action="admin.php?com_option=database&amp;option=manage_sql">
							<input type="hidden" name="task" value="restore_sql"/>
							<input type="hidden" name="cid[]" value="<?php echo $sqlfile; ?>"/>
							<input type="submit" value="<?php echo _CONTINUE; ?>"/>
						</form></p>
						<?php
					}
					return $finished;
				}

				echo '<h2>'._DB_SQL_RESULTS.'</h3>';
				
				// if we are in the middle of a previous job, remove the first queries
				if ($i = $sjs->CurrentJob())
					$query_arr = array_slice($query_arr, $i);
				// else if we are importing from limbo show the proper notice (shown only once at the beginning)
				else if ($lm_compat)
					echo '<h3>'._DB_IMPORT_NOTICE.'</h3><p>'._DB_IMPORT_NOTICE_FACTS.':</p><ul><li>'._DB_IMPORT_NOTICE_FACT_1.'</li><li>'._DB_IMPORT_NOTICE_FACT_2.'</li></ul><hr>';

				// try to run for about 10 minutes
				$max_time = shift_timeout(600);
				// in case of negative time, suppose that 30 seconds are given
//				if ($max_time <= 0)
//					$max_time += 30;
					
				// used to test other timeout configurations
//				$max_time = 20;
	} else { // do not split SQL queries job
//		$dbbak->db_queries =& $query_arr;
		foreach($query_arr as $query) {
			// run DROP queries with error silencing
			if (strtoupper(substr($query, 0, 4)) == 'DROP') {
				if (@!$conn->PrefixExecute($query, $hdr[2]))
					echo "\n".$conn->ErrorMsg()."\n";
				else
					echo '.';
			} else {
				if (!$conn->PrefixExecute($query, $hdr[2]))
					echo "\n".$conn->ErrorMsg()."\n";
				else
					echo '.';
			}
		}
		return _finalize_db_upgrade($conn, $dbbak, $oldver);
	}
	
	$rv = true;
	
	if ($sjs->finished || (empty($query_arr) ||
		$sjs->ExecuteJobs($query_arr, $hdr[2], 'sjs_hook', $max_time*0.85))) {
				
		echo sprintf(_DB_FINISHED, $sjs->Errors(), $sjs->Count()).'<br />';
		unset($sjs);
		$_SESSION[$d_uid.'-sqljobplitter']= array();
		unset($_SESSION[$d_uid.'-userfixed']);

		$rv = _finalize_db_upgrade($conn, $dbbak, $oldver);
		// remove the temporary conversion file
		if ($lm_compat && !isset($raw_data)) unlink($tmp);
	}

	return $rv;
}

function _finalize_db_upgrade(&$conn, &$dbbak, $oldver) {
		database_upgrade($conn, $dbbak, $oldver);
		return true;
}

function database_upgrade(&$conn, &$dbbak, $oldver) {
	// apply all the (necessary) Drake CMS compatibiliy fixes to database structure and data
	if (!strlen($oldver) || (strnatcmp($oldver, '0.5.0') <= 0)) {
		include com_path('dk_compat');
		dk_upgrade($conn, $dbbak, $oldver);
	}
	// Lanius CMS-only upgrades
	include com_path('lc_compat');
	lc_upgrade($conn, $dbbak, $oldver);
	
	if (strlen($oldver) && (strnatcmp($oldver, '0.4.8')>=0)) {
		// from 0.4.9+, database internal version will always be updated
		$conn->Update('#__packages', 'version=\''.sql_encode(cms_version(true))."'",
					' WHERE type=\'core\' AND name=\'Lanius CMS\'');
	}

}

?>
