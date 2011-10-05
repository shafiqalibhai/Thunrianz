<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Database backup class
# @author legolas558
#
# This class has been rewritten to work with adoDB lite
#

function dkdb_encode($s) {
	return str_replace("'", "''", $s);
}

class DbBackup {

	var $_rebased = array();
	var $_stack = array();
	var $db_queries;
	var $br = '<br />';
	
	function SetRebased($table) {
		$this->_rebased[$table] = true;
	}
	
	function IsRebased($table) {
		return isset($this->_rebased[$table]);
	}
	
	function Push($data) {
		$this->_stack[] = $data;
	}
	
	function Pop() {
		return array_pop($this->_stack);
	}

	// replicate the database into the one specified by parameters
	function Replicate(&$conn) {
		global $sql_data;
		$sql_data = '';
		function _app_outf($f, $s) {	$GLOBALS['sql_data'].=$s;	}
		$f = null;
		$this->Create($f, '_app_outf', '#__');

		$queries = split_sql($sql_data); $GLOBALS['sql_data'] = null;
		
		$err = 0;
		foreach ($queries as $query) {
			if (!@$conn->Execute($query)) {
				if (raw_strtoupper(substr($query, 0, 4))=='DROP')
					continue;
				echo $conn->ErrorMsg().$this->br;
				$err++;
			}
		}
		return $err;
	}

	// get a snapshot within the specified number of hours
	// returns FALSE if file could not be written
	function Snapshot($dest_dir, $minutes = 0) {
		// commented out bad code
/*		if ($minutes) {
			$sql_backups = read_dir($dest_dir,"file",true, array('php'));

			$chosen = false;
			$c=count($sql_backups);
			global $time;
			for ($i=0;$i<$c;$i++) {
				$p = strpos($sql_backups[$i]['id'], '_');
				if ($p===false) continue;
				$ts = intval(substr($sql_backups[$i]['id'], 0, $p));
				if (!$ts) continue;
				if ($time-$ts>=$minutes * 60) continue;
				$chosen = $sql_backups[$i]['id'];
				break;
			}
			if ($chosen === false)
				$chosen = $this->Backup($dest_dir, false);
		} else	*/
			$chosen = $this->Backup($dest_dir, false);
		return $dest_dir.$chosen;
	}

	function _meta_numeric_fix(&$meta) {
		if (!isset($meta->numeric)) {
			$meta->numeric = (	(strpos($meta->type, 'int')===0) ||
								(strpos($meta->type, 'tinyint')===0) ||
								(strpos($meta->type, 'float')!==false) ||
								(strpos($meta->type, 'numeric')!==false) ||
								(strpos($meta->type, 'decimal')!==false) ||
								(strpos($meta->type, 'double')!==false) ||
								(strpos($meta->type, 'real')!==false) );
		}
	}
	
	function BackupFilename() {
		global $time;
		$fname = lc_strftime('%Y-%m-%d', $time).'_'.get_domain(true).'_v'.cms_version().'_'.random_string(8).'.sql.php';
		return $fname;
	}
	
	// returns true if the file was written
	function Backup($dest_dir = null, $can_dump = true, $compress = false) {
		$fname = $this->BackupFilename();
		if (isset($dest_dir)) {
			global $d_root;
			if (!$compress) {
				global $d_root;
				include_once $d_root.'admin/classes/fs.php';
				$fs = new FS();

				$f = $fs->write_open($dest_dir.$fname);
	//			$f = false;
				if ($f!==false) {
					$this->Create($f, 'fwrite');
					$fs->write_close($f, $dest_dir.$fname);
					return $fname;
				}
				if (!$can_dump)
					return '';
			} else {
				$fname .= '.gz';
				$f = @gzopen($dest_dir.$fname, 'wb');
	//			$f = false;
				if ($f!==false) {
					$this->Create($f, 'gzwrite');
					gzclose($f);
					return $fname;
				}
				if (!$can_dump)
					return '';
			}
			// falls back to screen dumping
		}
		global $d_root;
		include_once $d_root.'includes/download.php';
		send_download_headers_norange($fname, 'text/sql');
//		header('Content-Type: text/sql');
//		header('Content-Disposition: attachment; filename='.rawurlencode(basename($fname)).';');
		function _outf($f, $s) {	echo $s;	}
		$this->Create($f, '_outf');
		return '';
	}

function Create($f, $outf, $rand_prefix = null) {
	global $conn,$d_root,$time,$d_prefix;

	$tables = $conn->MetaTables();
	
	$outf($f, dkdb_h(5));
	$outf($f, dkdb_h(5));		// double header for php/SQL files
	$outf($f, dkdb_prefix($rand_prefix));	// specify random prefix
	
//	$this->rand_prefix = $rand_prefix;

	foreach ($tables as $table_name) {
		$table_meta=$conn->MetaColumns($table_name);

		$table = $rand_prefix.$table_name;
		$outf($f, "DROP TABLE $table;\r\n\r\n");
		// create table query
		$outf($f, "CREATE TABLE $table (\r\n");
		$outf($f, "id INTEGER AUTO_INCREMENT");
		if ($table_meta) {
			$keys = array_keys($table_meta);
			foreach ($keys as $k) {
				$meta =& $table_meta[$k];
				$this->_meta_numeric_fix($meta);

				if( $meta->name=="id"
//				|| $key=="lastupd"
				) continue ;

				$outf($f, ",\r\n".$meta->name.' ');
				$outf($f, raw_strtoupper($meta->type));
				if (($meta->max_length>0) && ($meta->type!=='int')) //skip display lengths
					$outf($f, "(".$meta->max_length.")");
				if($meta->not_null) $outf($f, " NOT NULL");
				// to overcome 'mysql, mysqli' binding bugs
				if($meta->has_default && $meta->type != 'text' && $meta->type != 'longtext') {
					$outf($f, " DEFAULT ");
					if ($meta->numeric) {
						$def = (string)$meta->default_value;
						if (!strlen($def)) $def='0';
						$outf($f, $def);
					} else {
						$outf($f, "'".dkdb_encode($meta->default_value)."'");
					}
				}
			}
		}
		$outf($f, ",\nPRIMARY KEY (id) \r\n);\r\n\r\n");

		$rsa=$conn->SelectArray('#__'.$table_name, '*');
		foreach($rsa as $row) {
			$outf($f, "INSERT INTO $table (");
			$c=count($row);

			for($i=0;$i<$c-1;$i++) {
//				if($keys[$i]=='lastupd')continue;
				$outf($f, $table_meta[$keys[$i]]->name.',');
			}
			$outf($f, $table_meta[$keys[$c-1]]->name);
			// removed newlines before VALUES for bug 573
			$outf($f, ") VALUES (");
			reset($table_meta);
			for($i=0;$i<$c;$i++) {
//				if($keys[$i]=='lastupd')continue;
				$meta =& $table_meta[$keys[$i]];
				$val = $row[$meta->name];
				$sep = ($i==$c-1) ? '': ',';
				if (!$meta->numeric)
					$outf($f, "'".dkdb_encode($val)."'".$sep);
				else {
					settype($val, 'string');
					$outf($f, $val.$sep);
				}
			}
			$outf($f, ");\r\n\r\n");
		}
	}
}

function LoadSchema($fn) {
	// get the vanilla database structure
	global $d_root;
	$sql = file_get_contents($fn);
	
	$this->rand_prefix = dkdb_read_prefix($sql);

	$this->db_queries = split_sql($sql); unset($sql);
}

function Rebase() {
	// the function parameters are the table names
	$tables = func_get_args();
	
	// if the db_queries is not set, load the default database schema
	if (!isset($this->db_queries)) {
		global $d_root;
		$this->LoadSchema($d_root.'admin/includes/database.sql.php');
	}
	
	// if no tables were passed, rebase all tables
	if (!count($tables))
		$alltables = true;
	else
		$alltables = false;
	
	global $conn;
	// get all the tables under the current prefix
	$db_tables = $conn->MetaTables();
	
	$qc=count($this->db_queries);
	// start a cycle of 2 statements (DROP, CREATE), typical of a valid Lanius CMS database schema
	for($i=0;$i<$qc;$i+=2) {
		// get the table name
		if (!preg_match('/DROP TABLE '.$this->rand_prefix.'([^;]+);/A', $this->db_queries[$i], $m)) {
			trigger_error('Expected a DROP query: '.$this->db_queries[$i]);
			continue;
		}
		$table = $m[1];
		
		// skip subsites stuff when running from subsite
		if (($table == 'subsites') && ($GLOBALS['d_subpath']!=''))
			continue;
		
		// this table is not interested by the rebase operation
		if (!$alltables && !in_array($table, $tables)) {
			// MAYBE WRONG?
			if (!in_array($table, $db_tables)) {
				// this table is not present in the database, at least create it
				$conn->Execute($this->db_queries[$i+1]);
				echo "Created table $table".$this->br;
			}
			continue;
		}
		
		// check if we have already rebased this table
		if ($this->IsRebased($table)) {
			echo "Table $table has already been rebased".$this->br;
			continue;
		} else
			$this->SetRebased($table);

		if (!in_array($table, $db_tables)) {
			// this table is not present in the database, at least create it
			$conn->Execute($this->db_queries[$i+1]);
			echo "Created table $table".$this->br;
			continue;
		} 
		// cache the recordset rows
		$data = $conn->SelectArray('#__'.$table, '*');
		// drop & re-create the table (data is stored in memory)
		$conn->Execute($this->db_queries[$i]);
		echo "Dropped table $table".$this->br;
		$conn->Execute($this->db_queries[$i+1]);
		echo "Created table $table".$this->br;

		// there is no data, the mission ends
		if (!count($data))
			continue;

		// get the field names
		$avail = array_keys($data[0]);
		// import data considering the new table schema
		$table_meta=$conn->MetaColumns($table);
		$to_encode = $taken = array();
		// scan the table schema and set the wanted fields
		foreach ($table_meta as $meta) {
			// do we have a column for this (new) field?
			if (!in_array($meta->name, $avail))
				// no, we skip this field, totally
				continue;
			// fix the numeric flag for the field's definition
			$this->_meta_numeric_fix($meta);
			$to_encode[] = !$meta->numeric;
			// put this field in the bucket
			$taken[] = $meta->name;
		}
		
		// now craft INSERTs and really import the data
		$fields_list = '('.implode(',', $taken).')';
		
		// insert each row of data
		foreach($data as $row) {
			$vals = '';
			reset($to_encode);
			foreach($taken as $field) {
				if (current($to_encode))
					$vals.=",'".sql_encode($row[$field])."'";
				else {
					if (!$row[$field])
						$vals.=",0";
					else
						$vals.=",".$row[$field];
				}
				next($to_encode);
			}
			if (!$conn->Insert('#__'.$table, $fields_list, substr($vals, 1)))
//				echo $sql.': ';
				echo $conn->ErrorMsg().$this->br;
		}
		echo count($data).' rows re-based for table '.$table.$this->br;
	}
	
	return $qc;	
}


} // DbBackup class

?>