<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Database abstraction layer
#
# this class should abstract our custom options over adoDB, like db prefix, caching and others
#

class DbFork {
	var $adodb;
	var $prefix;
	var $connected;

	## Replaces any '#__' string in the provviden parameter
	#  @param $sqlstring The string to replace
	#  @access private
	# TODO: should be public
	function _prefixReplace(&$sqlstring) {
		 $sqlstring = str_replace_once("#__", $this->prefix, $sqlstring);
	}

	## Returns the correct database name (to be passed to adoDB lite) depending on the database type
	#  @param $db_type The database type (adoDB lite identifier)
	#  @param $db_name The database name (user-provviden)
	#  @access private
	# Currently modifies for SQLite only
	function _db_name($db_name) {
		//NOTE: $d_private is used WITH subsite path because we are not using d_private from the subsite (which contains the subsite path)
		$db_type = $this->adodb->dbtype;
		if (strpos($db_type,'sqlite')===0) {
			global $d_root, $d_private, $d_uid;
			return $d_root.$d_private.$db_name.'/'.$d_uid.'_'.$db_name.'.db';
		} else if ($db_type == 'gladius') {
			global $d_root, $d_private, $d_uid;
			return $d_root.$d_private.$db_name.'/';
		}
		return $db_name;
	}
	
	function _db_name_subs($db_name, $subsite, $uid) {
		//NOTE: $d_private is used WITH subsite path because we are not using d_private from the subsite (which contains the subsite path)
		$db_type = $this->adodb->dbtype;
		if (strpos($db_type,'sqlite')===0) {
			global $d_root, $d_private;
			return $d_root.$subsite.'/'.$d_private.$db_name.'/'.$uid.'_'.$db_name.'.db';
		} else if ($db_type == 'gladius') {
			global $d_root, $d_private, $d_uid;
			return $d_root.$subsite.'/'.$d_private.$db_name.'/';
		}
		return $db_name;
	}

	
	function DbFork($db_type) {
		if (defined('_VALID_ADMIN'))
			$this->adodb =& ADONewConnection($db_type, 'pear:meta');
		else
			$this->adodb =& ADONewConnection($db_type);
		// enable this line to debug each query inline (will break web responses because of headers sent)
//		$this->adodb->debug = true;
		// enable this line to activate the adoDB lite debug console
		// mod_debug will show the formatted results
		if ($GLOBALS['d_sqldebug'])
			$this->adodb->debug_console = true;
//		ob_start();
	}

	## Constructor which takes in all the database parameters and returns a DbFork instance which incapsulates an ADONewConnection object
	#  @param $sqlstring The string to replace
	#  @param $db_type The database type (adoDB lite identifier)
	#  @param $db_host The database host (user-provviden)
	#  @param $db_password The database password (user-provviden)
	#  @param $db_name The database name (user-provviden)
	#  @param $db_prefix The database prefix (user-provviden)
	#  @access public
	# Also loads specific modules for admin users (when _VALID_ADMIN is set)
	function Initialize($db_host, $db_user, $db_password, $db_name, $db_prefix, $setup = false) {
		
		$this->prefix = $db_prefix;
		$this->connected = @$this->adodb->PConnect($db_host,$db_user,$db_password, $this->_db_name($db_name));
		if (!$setup && !$this->connected) {
			global $d_root;
			include $d_root.'includes/servererror.php';
			header('Status: 500 Server Error', true, 500);

			service_msg('500 - Server Error', 'Database connection error<br/><small>'.$GLOBALS['d__last_error'].'</small>');
			exit();

		}
	}
	
	// for subsites only
	function SubInitialize($subsite, $uid, $db_host, $db_user, $db_password, $db_name, $db_prefix) {
		$this->prefix = $db_prefix;
		$this->connected = $this->adodb->PConnect($db_host,$db_user,$db_password, $this->_db_name_subs($db_name, $subsite, $uid));
	}
	
	function MetaColumns($table) {
		return $this->adodb->MetaColumns($this->prefix.$table);
	}
	
	//NOTE: already optimized
	function MetaTables() {
		$tables = $this->adodb->MetaTables();
		$r = array();
		$l = strlen($this->prefix);
		foreach($tables as $table) {
			$table = strtolower($table);
			if ( substr($table, 0, $l) === strtolower($this->prefix) )
				$r[] = substr($table,$l);
		}
		return	$r;
	}

	## Returns the number of affected rows (simple return of ::adodb->Affected_Rows())
	#  @access public
	function Affected_Rows() {
		return $this->adodb->Affected_Rows();
	}
	
	function Quote($s, $magic = false) {
		// eventual magic quotes slashes already stripped by in() function
		$qs = $this->adodb->qstr($s, $magic);
		return substr($qs, 1, strlen($qs)-2);
	}

	function Insert_ID() {
    	return $this->adodb->Insert_ID();
	}
	
	function Select($arg_tables, $what, $extra = '') {
		$this->_prefixReplace($arg_tables);
		return $this->adodb->Execute('SELECT '.$what.' FROM '.$arg_tables.$extra);
	}

	function SelectRow($arg_tables, $what, $extra = '') {
		$this->_prefixReplace($arg_tables);
		$row = $this->adodb->Execute('SELECT '.$what.' FROM '.$arg_tables.$extra);
		$row = $row->GetArray(1);
		if (isset($row[0]))
			return $row[0];
		return $row;
	}
	
	function SelectArray($arg_tables, $what, $extra = '') {
		$this->_prefixReplace($arg_tables);
		return $this->adodb->GetArray('SELECT '.$what.' FROM '.$arg_tables.$extra);
	}
	
	function SelectArrayLimit($arg_tables, $what, $extra, $nrows, $offset = -1) {
		$this->_prefixReplace($arg_tables);
		$rs = $this->adodb->SelectLimit('SELECT '.$what.' FROM '.$arg_tables.$extra, $nrows, $offset);
		return $rs->GetArray();
	}
	
	function SelectColumn($arg_tables, $what, $extra = '') {
		$rsa = $this->SelectArray($arg_tables, $what, $extra);
		$column = array();
		foreach($rsa as $row) {
			$column[] = current($row);
		}
		return $column;
	}
	
	function Update($arg_table, $sets, $extra = '') {
		$this->_prefixReplace($arg_table);
		return $this->adodb->Execute('UPDATE '.$arg_table.' SET '.$sets.$extra);
	}

	function Delete($arg_table, $extra = '') {
		$this->_prefixReplace($arg_table);
		return $this->adodb->Execute('DELETE FROM '.$arg_table.$extra);
	}
	
	function Insert($arg_table, $fields, $values) {
		$this->_prefixReplace($arg_table);
		return $this->adodb->Execute('INSERT INTO '.$arg_table.' '.$fields.' VALUES('.$values.')');
	}

	function CreateTable($arg_table, $schema) {
		$this->_prefixReplace($arg_table);
		return $this->adodb->Execute('CREATE TABLE '.$arg_table.' ('.$schema.')');
	}
	
	function ErrorMsg() {
		return $this->adodb->ErrorMsg();
	}

	function EOF() {
		return $this->adodb->EOF();
	}
	
	function SelectCount($table, $field = '*', $extra = '') {
		$this->_prefixReplace($table);
		$rsa = $this->adodb->GetArray('SELECT COUNT('.$field.') FROM '.$table.$extra);
		return $this->_count($rsa);
	}
	
	function _count(&$rsa) {
		// the below dirty expression to workaround a Gladius DB limitation when counting for empty queries
		if (!isset($rsa[0])) return 0; //FIXME
		reset($rsa[0]);	//FIXME
		return (int)@current($rsa[0]);
	}
	
	//DEPRECATED
	function Count($sql) {
		$rsa = $this->GetArray($sql);
		return $this->_count($rsa);
	}

	// deprecated in favor of ::SelectRow()
	function GetRow($sql) {
		$this->_prefixReplace($sql);
		$rs = $this->adodb->SelectLimit($sql, 1);
		$arr = $rs->GetArray(1);
		if (isset($arr[0]))
			return $arr[0];
		else
			return $arr;
	}
	
	// deprecated in favor of ::SelectColumn()
	function GetColumn($sql) {
		$rsa = $this->GetArray($sql);
		$column = array();
		foreach($rsa as $row) {
			$column[] = current($row);
		}
		return $column;
	}

	// deprecated in favor of ::SelectArray()
	function GetArray($sql, $nrows = -1) {
		$this->_prefixReplace($sql);
		return $this->adodb->GetArray($sql, $nrows);
	}

	// deprecated in favor of ::Update, ::Select, ::Insert, ::Delete and ::CreateTable
	function Execute($sql) {
		$this->_prefixReplace($sql);
		return $this->adodb->Execute($sql);
	}
	
	// the descendant of ::Execute()
	function RawExecute($sql) {
		return $this->adodb->Execute(str_replace('#__', $this->prefix, $sql));
	}
	
	function PrefixExecute($sql, $prefix) {
		return $this->adodb->Execute(str_replace_once($prefix, $this->prefix, $sql));
	}

	//TODO: make consistent with DbFork method syntax (e.g. SelectRow, SelectArray)
	// parameter 1 is the number of rows, parameter 2 is the offset
	function SelectLimit($sql, $nrows, $offset = -1) {
		$this->_prefixReplace($sql);
		return $this->adodb->SelectLimit($sql, $nrows, $offset);
	}

	function QueryInfo() {
		return array($this->adodb->query_list,$this->adodb->query_list_time);
	}
	
	function IsFlatfile() {
		global $d_db;
		if ($d_db == 'gladius' || (strpos($d_db,'sqlite')===0))
			return 1;
		return 0;
	}

	// MS-SQL specific hacks
	function EnableIdentity($table) {
		global $d_db;
		if (strpos($d_db, 'mssql')===0) {
			$this->adodb->Execute('SET IDENTITY_INSERT '.$this->prefix.$table.' ON');
		}
	}

	function DisableIdentity($table) {
		global $d_db;
		if (strpos($d_db, 'mssql')===0) {
			$this->adodb->Execute('SET IDENTITY_INSERT '.$this->prefix.$table.' OFF');
		}
	}

}

?>
