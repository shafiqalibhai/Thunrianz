<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## adodb include file
#
# @requires includes/dracon.php
#
# main adoDB lite class instance
#

include $d_root.'classes/dbfork.php';

/* Main ADODB Lite inclusion point */
include $d_root.'classes/adodb_lite/adodb-errorhandler.inc.php';
include $d_root.'classes/adodb_lite/adodb.inc.php';

$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$conn = new DbFork($d_db);
$conn->Initialize($d_dbhost,$d_dbusername,$d_dbpassword,$d_dbname, $d_prefix);

// workaround for adoDB lite's bug 1707315 - bug tracker item: http://sourceforge.net/tracker/index.php?func=detail&aid=1707315&group_id=140982&atid=747945
$conn->adodb->fetchMode = ADODB_FETCH_ASSOC;

?>