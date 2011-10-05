<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Mailer choosing script
# @author legolas558
#
# since mailers are now drabots, this is a bit hackish
# since not all mailers are part of the core, the email chooser should take that in account

define('_TEST_SUBJECT', 'Lanius CMS testing mailer system');
define('_TEST_BODY', "Hello there,\n\nthis email has been generated by Lanius CMS to check the availability of mailing systems.\nIf you are receiving this email the mailing system is working OK.\n\n--\n  Lanius CMS Robot\n");

function _send_test_email($mailer_func, $to) {
	global $d_email_from, $d_email_name;
	return (@$mailer_func($to, _TEST_SUBJECT, _TEST_BODY,
			'From: '.$d_email_from.' <'.$d_email_from.'>') != false);
}

/*
function _choose_mailer($to) {
	global $d_root;
	include_once $d_root.'classes/anyxml/anyxml.php';
	// get all drabots of group 'mail'
	$files = raw_read_dir($d_root.'drabots/', array('xml'), false, _RRD_RECURSE|_RRD_NO_DIRECTORIES);
	$mailers = array();
	foreach ($files as $file) {
		$xml = new AnyXML();
		$xml->fromString(file_get_contents($file));
		$group = $xml->getElementByPath('drabots/drabot/group');
		if ($group->getValue() != 'mail')
			continue;
		$id = $xml->getElementByPath('drabots/drabot/id');
		// hack!
		$id = $id->getValue();
		if ($id == 'dbmail' || $id == 'mailbox')
			continue;
		$mailers[] = $id;
	}
//	$mailers = array('phpmail', 'smtpmail', 'smmail', 'bsdmail');
	global $d__smtp_default_timeout;
	$d__smtp_default_timeout = 3;
	global $_DRABOTS;
	foreach ($mailers as $mailer) {
		require $d_root.'drabots/'.$mailer.'.php';
		if (_send_test_email('mailer_'.$mailer, $to))
			return $mailer;
	}
	// no working mailer found
	return null;
} */

?>