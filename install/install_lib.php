<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Lanius CMS common install/uninstall procedures
# @author legolas558
#
# contains common utility functions to modify installation

function mssql_sql_fix($raw_dkdb) {
	return preg_replace(array('/id int(?:eger) AUTO_INCREMENT/i',
								'/\\s+LONGTEXT,/'), array('id int IDENTITY(1,1)',
								' TEXT,'), $raw_dkdb);
}

// PostgreSQL hotfix - no more necessary with AXMLS data dictionary
function postgre_sql_fix($raw_dkdb) {
	return preg_replace(array('/id int(?:eger) AUTO_INCREMENT/i',
								'/\\s+LONGTEXT,/'), array('id SERIAL',
								' TEXT,'), $raw_dkdb);
}

function pick_insert_tables($sql) {
	if (preg_match_all('/^INSERT\\s+INTO\\s+#__([^\\s]+)/m', $sql, $m))
		return array_unique($m[1]);
	return array();
}

// $convert_flag values:
function install_sql(&$conn, $filename, $d_db = null, $verbose = false) {
	$query = file_get_contents($filename);

	if (isset($d_db)) {
		if (strpos($d_db, 'postgres')===0)
			$query = postgre_sql_fix($query);
		if ((strpos($d_db, 'mssql')===0) ||
			(strpos($d_db, 'sqlsrv')===0) ) {
			$query = mssql_sql_fix($query);
			$mssql_tables = pick_insert_tables($query);
			foreach($mssql_tables as $table) {
					$conn->Execute('SET IDENTITY_INSERT #__'.$table.' ON');
			}
		}
	}

	$query_arr = split_sql($query, true);
	$err = 0;
	foreach($query_arr as $query) {
		// silently execute DROP queries
		if (strpos($query, 'DROP')===0)
			@$conn->Execute($query);
		else {
			if ($conn->Execute($query)===false) {
				++$err;
				echo '<a href="javascript:alert(\''.js_enc($query).'\')" title="Click to read the faulty SQL">'.$conn->ErrorMsg().'</a><br />';
				flush();
				continue;
			}
			if ($verbose)
				echo '<p>'.$conn->ErrorMsg().'</p>';
		}
	}

	if ((strpos($d_db, 'mssql')===0) ||
			(strpos($d_db, 'sqlsrv')===0) ) {
		foreach($mssql_tables as $table) {
				$conn->Execute('SET IDENTITY_INSERT #__'.$table.' OFF');
		}
	}

	if ($verbose) {
		$c = count($query_arr);
		echo '<p>'.($c-$err).'/'.$c.' queries executed from file '.fix_root_path($filename).'</p>';
	}

	return $err;
}

function install_cms(&$conn, $d_db, $verbose = false) {
	global $d_root;
	//L: we should use adoDB lite datadict for SQL portability
	$err = install_sql($conn, $d_root.'admin/includes/database.sql.php', $d_db, $verbose );

	if (!$err) {
		// insert the Lanius CMS core package
		$conn->Insert('#__packages', '(type,name,version)', "'core', 'Lanius CMS', '".sql_encode(cms_version(true))."'");

		$err = install_sql($conn, $d_root.'install/inserts.sql.php', $d_db, $verbose);
	}
	return $err;
}

function admin_pwd() {
	global $conn, $my;
	// security check
	$row = $conn->SelectRow('#__users', 'password',' WHERE id='.$my->id);
	return $row['password'];
}

function admin_insert(&$newconn, $apwdh) {
	global $my, $time;
	$newconn->Insert('#__users', '(name,username,email,password,registerDate,lastvisitDate,gid,published)',
			"'".sql_encode($my->name)."', '".sql_encode($my->username).
			"', '".sql_encode($my->email)."', '$apwdh', $time, $time, 5, 1");
}

?>
