<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## common admin/user includes
# @author legolas558
#
# included files for each Lanius CMS web instance

// $time is always the UTC/GMT timestamp of the system (which must be correct!)
// use lc_strftime and lc_date to get correct user-localized dates, gmstrftime, gmdate otherwise
// strftime and date should not be used because are not localization-consistent under PHP4, @see includes/time.php
global $time, $d_type;
$time = time();	

// a better HTML/XHTML diversification (in references to $d_type) is wanted, see also new drafts
$d_type = 'html';

include $d_root.'classes/drabots.php';

// create the drabots collection handler
$_DRABOTS = new DrabotHandler();

include $d_root.'classes/user.php';

// will be later overriden
$access_acl = 'access<2';
$access_sql='AND '.$access_acl;

// start the session before starting the User
require $d_root.'includes/session.php';

$my = new CMSUser();

$my->Initialize();

// temporarily created to allow drabots fetching from database
$access_acl = 'access<'.($my->gid+1);
$access_sql='AND '.$access_acl;

$edit_acl = 'editgroup<'.($my->gid+1);
$access_acl = 'access<'.($my->gid+1);
$access_sql='AND '.$access_acl;
$edit_sql='AND '.$edit_acl;

include $d_root.'lang/'.$my->lang.'/common.php';

include $d_root.'includes/user_functions.php';

include $d_root.'classes/http.php';

include $d_root.'classes/cms.php';

include $d_root.'classes/pathway.php';

$pathway = new Pathway();

//L: the play begins
$d = new CMS();

//L: we have to optimize (even more) the below visit counter
//L: it should be disabled (at option) from the backend
//L: we should also have a brief statistic in the backend
//L: through it, if always enabled
if ($d_stats) {
/*
if(preg_match('/msie/i', (string)@$_SERVER['HTTP_USER_AGENT']))
	$my->ip='notcalhost';
*/
	include $d_root.'classes/stats.php'	;
	$stats = new Stats();
/*	
echo "TOTAL: ".$stats->TotalVisits()."<br>";
echo "TODAY: ".$stats->TodayVisits()."<br>";
echo "GUESTS: ".$stats->GuestsOnline()."<br>";
echo "MEMBERS: ".$stats->MembersOnline()."<br>";
die;
*/

}

header('Content-Type: text/html; charset='.strtoupper($d->Encoding()));
header('Content-Language: '.$my->lang);

global $d__type;
$d__type = 'xhtml';

//L: $d__utf8 will be set at runtime and will tell if utf8 re-encoding of some PHP functions output is needed
$d__utf8 = false;
$lids = explode("\n", _LOCALE);
foreach($lids as $lid) {
	$d__locale = setlocale(LC_ALL, array($lid.'.UTF8', $lid.'.UTF-8'));
	if ($d__locale!==false) {
		$d__utf8 = true;
		break;
	}
}
if (!$d__utf8) {	// provide a specific conversion function
	include $d_root.'includes/i18n/utf8.php';
} else {
	function lc_enc($s) { return $s; }
}

// special global flag which tells if we have just used an UTF-8 unsafe function
// (and hence the warning should be ignored)
global $d__utf8_unsafe;
$d__utf8_unsafe = false;

if (strtoupper('abcdefghijklmnopqrstuvwxyz')!='ABCDEFGHIJKLMNOPQRSTUVWXYZ') {

	// used for an ASCII strtoupper()
	function _raw_strtoupper_cb($m) { return chr(ord($m[0])-32); }
	function raw_strtoupper($s) {
		if (!strlen($s)) return '';
		return preg_replace_callback('/[a-z]/', '_raw_strtoupper_cb', $s);
	}
} else {
	function raw_strtoupper($s) {return strtoupper($s);}
}

// disallow time travelling
include $d_root.'includes/i18n/time.php';

//NOTE: the $d_online check cannot be performed here because it would not allow admin backend access

// $option and $Itemid are the only two allowed globals, and they should not be touched

$Itemid = in_num('Itemid', $_GET, 0);

// the script name, usually index.php or index2.php, is stored in a global variable for reference
global $d__req;
$d__req = CMSRequest::ScriptName();

?>