<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Database abstraction layer self-testing
# @author legolas558
#
# functions used to check for availability of DBMSes
# $d_db must be defined globally

## define a dummy function called _gladius_dummy_fn if Gladius DB can be installed
if ((strnatcmp(phpversion(), '4.3.3')>=0) &&
	function_exists('gzuncompress')) {
	function _gladius_dummy_fn() { }
}
global $databases;
	  $databases = array(
	  // the first available DBMS will be selected by default
						array('gladius' , 'Gladius DB (flat file)', '_gladius_dummy_fn'),
						array('mysqli' , 'MySQLi', 'mysqli_connect'),
						array('mysql' , 'MySQL', 'mysql_connect'),
						array('sqlite' , 'SQLite', 'sqlite_open'),
						array('sqlitepo' , 'SQLite Pro', 'sqlite_open'),
						array('mysqlt' , 'MySQLt', 'mysqlt_connect'),
						array('fbsql' , 'Frontbase', 'fbsql_connect'),
						array('maxdb' , 'MaxDB', 'maxdb_connect'),
						array('msql' , 'miniSQL', 'msql_connect'),
						array('odbc' , 'ODBC', 'odbc_connect'),
						array('postgres' , 'PostgresSQL', 'pg_connect'),
						array('sybase' , 'Sybase', 'sybase_connect'),
						array('sybase_ase' , 'Sybase ASE', 'sybase_connect'),
						);

		global $d_db;
			$d__elect_db = $d_db;
			// sets up the databases systems array, the third value can be: 0 = not available (need drivers pack), 1 = not available (PHP has not it),
			// 2 = available but needs drivers pack, 3 - folder exists and functions too
			for($i=0;$i<count($databases);$i++) {
				$db =& $databases[$i];
				$db[3] = (int)is_dir($d_root.'classes/adodb_lite/adodb_sql_drivers/'.$db[0]);
				$db[3] |= (((int)function_exists($db[2])) << 1);
				if (($db[3]==3) && ($d__elect_db==''))
					$d__elect_db = $db[0];
			}

			// scan array again
			if ($d__elect_db=='') {
				$d__elect_db = $databases[0][0];
				for($i=0;$i<count($databases);$i++) {
					$db =& $databases[$i];
					if ($db[3]>1) {
						$d__elect_db = $db[0];
						break;
					}
				}
			}
			
function databases_by_ref() {
	global $databases;
	$ra = array();
	foreach($databases as $db) {
		// true if driver exists and API is available
		$ra[$db[0]] = ($db[3]==3);
	}
	return $ra;
}

?>
