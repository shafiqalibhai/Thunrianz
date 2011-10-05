<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## DB session drabot
# @author legolas558
# Released under GNU GPL License
# This component is part of Lanius CMS core
#
# provides a database based session
#

$_DRABOTS->registerFunction( 'BeforeSessionStart', 'botDBSession' );

## dummy
function _dbs_open($save_path, $session_name) { return true; }

## dummy
function _dbs_close() { return true; }

## read the session data
function _dbs_read($id) {
        global $conn;
        $row = $conn->SelectRow('#__sessions', 'data', ' WHERE id='.$id);
        if (isset($row['data']))
                return $row['data'];
        $conn->Insert('#__sessions', '(id, data, modified)', $id.", '', ".time());
        return '';
}

function _dbs_write($id, $sess_data) {
	global $conn;
//        global $d_db, $d_dbhost, $d_dbusername, $d_dbpassword, $d_dbname, $d_prefix;
//$conn = new DbFork($d_db); $conn->Initialize($d_dbhost,$d_dbusername,$d_dbpassword,$d_dbname, $d_prefix);

        $conn->Update('#__sessions', 'data=\''.sql_encode($sess_data).'\', modified='.time(), ' WHERE id='.$id);
        return ($conn->Affected_Rows()!=0);
}

function _dbs_destroy($id) {
        global $conn;
        $conn->Delete('#__sessions', ' WHERE id='.$id);
        return ($conn->Affected_Rows()!=0);
}

## runs the garbage collector task depending on the relative ini settings
function _dbs_gc($maxlifetime) {
	// do not garbage collect if the probability is not matched
	if (mt_rand(0, ini_get('session.gc_divisor')) > ini_get('session.gc_probability'))
		return true;
	$lifetime = (int)ini_get('session.gc_maxlifetime');
	if (!$lifetime)
		$lifetime = 60*60*15;
	global $conn;
	$conn->Delete('#__sessions', ' WHERE modified<'.(time()-$lifetime)); 	 
	return true;
}

function botDBSession() {
	$sid = (int)@$_REQUEST[session_name()];

	if (!$sid) {
		global $conn;
		do {
			$sid = mt_rand(1, mt_getrandmax()-1);
			$row = $conn->SelectRow('#__sessions', 'id', ' WHERE id='.$sid);
		} while (count($row));
	}

	session_id($sid);

	session_set_save_handler("_dbs_open", "_dbs_close", "_dbs_read", "_dbs_write", "_dbs_destroy", "_dbs_gc");
}

?>