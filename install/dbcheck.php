<?php
## database check script
# @author legolas558
#@created 2006
#
# Script used to check database settings for a valid connection
#

define('_VALID', 1);
define('_VALID_ADMIN', 1);

require '../version.php';

// we will now recognize the UID from config.php or from the session
$cfg = $d_root.$d_private.'config.php';
if (file_exists($cfg)) {
	$avail = true;
	include $cfg;
} else
	$avail = false;

require $d_root.'includes/functions.php';

require $d_root."includes/dracon.php";

//WONT WORK
@session_start();

require $d_root.'admin/includes/admin_functions.php';

if (!$avail) {
	$rootid = md5($d_root);

	if (isset($_SESSION[$rootid.'-uid'])) {
		global $d_uid;
		$d_uid = $_SESSION[$rootid.'-uid'];
		$avail = isset($_SESSION[$d_uid.'-installing']);
		// prevent access after installation
//		$_SESSION[$d_uid.'-installing'] = null;
	}
} //WARNING: database testing will be possible also after logout

if (!$avail) {
	header('Status: 403 Not Authorized');
	echo '<h1>403 Not Authorized</h1>';
	exit;
}

//var_dump($_POST);die;

if (	(null !== ($d_db = in_raw('cdb', $_POST))) &&
	 (null !== ($d_dbhost = in_raw('cdbhost', $_POST))) &&
	 (null !== ($d_dbname = in_raw('cdbname', $_POST))) &&
	 (null !== ($d_dbusername = in_raw('cdbusername', $_POST))) &&
	 (null !== ($d_dbpassword = in_raw('cdbpassword', $_POST))) &&
	 ('_' !== ($d_prefix = in_raw('cprefix', $_POST, '').'_'))
	) {

require $d_root.'classes/http.php';

require $d_root.'admin/includes/dbtest.php';

$dbs = databases_by_ref();

// test if the database driver is available
if (!$dbs[$d_db]) {
	echo '<h3>Database driver not available</h3>';
	echo '<p>The database driver for database management system <strong>'.$d_db.'</strong> is not available.</p>';
//	echo '<p>You should install it through the adoDB lite drivers pack.</p>';
	exit;
}

// will fail here, a silencing operator '@' can be used
@include $d_root.'includes/adodb.php';

	echo $conn->ErrorMsg();

	if (!$conn->connected)
		echo '<h3>Connection error</h3>';
	else
		echo '<h3>Connection OK</h3>';
	?><dl>
	<dt>Database management system</dt>
	<dd><?php echo $d_db; ?></dd>
	<dt>Database host</dt>
	<dd><?php echo $d_dbhost; ?></dd>
	<dt>Database name</dt>
	<dd><?php echo $d_dbname; ?></dd>
	<?php
	if ($conn->connected) { ?>
	<dt>Database table creation</dt>
	<dd><?php
	
	if (@!$conn->Execute('CREATE TABLE dk_cmstest_table(id INTEGER, value TEXT)'))
		echo 'Failure'; else {
			echo 'OK';
		$conn->Execute('DROP TABLE dk_cmstest_table');
	}
	?></dd>
	<?php
	}
	?>
	</dl>
	<?php
	exit();
}

header('Status: 400 Bad Request');
echo '<h1>400 Bad Request</h1>';
?>