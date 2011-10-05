<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## GeloMail class
# @author legolas558
#
# HTML and text emails generation
# TODO: remove directory

// parse a definition file into an array('constant' => 'value')
function parse_defines($content) {
	$defines = array();
	//TODO: use same code in admin.language.dklang.php
//	if (preg_match_all("/(?i:define)\\s*\(\\s*('|\")([A-Z_0-9]+)('|\")\\s*,\\s*('|\")(.*)('|\")\\s*\);/U", $content, $m)) {
	if (preg_match_all("/^define\\s*\\(\\s*('|\")([A-Z0-9_]+)('|\")\\s*,\\s*('|\")(.*)('|\")\\s*\\)\\s*;\\s*/mU", $content, $m)) {
		foreach($m[2] as $k) {
			$defines[$k] = str_decode(current($m[5]));
			next($m[5]);
		}
		$m = null;
	}
	return $defines;
}

class GeloMail {
	var $bcc;		// array of Bcc email addresses
	var $cc;		// array of Cc email addresses
	var $html;
	var $attach; // {moved filename, original filename, mime type}
	var $_domain;
	var $_bots_loaded;
	
	function GeloMail($html = false, $domain = null) {
		$this->_bots_loaded = false;
		$this->_domain = $domain;
		$this->bcc = array();
		$this->cc = array();
		$this->attach = null;
		$this->html = $html;
		$this->_from = (string)@ini_get('sendmail_from');
		// dynamically defines an optimized sha1 hashing function which checks for zero leading bits
		// _hc_sha_valid will return true if the matching bytes are zero, false otherwise
		if ($GLOBALS['d_email_hashcash']) {
			if (function_exists('hash')) {
				function _hc_sha_valid($data, $zbytes) {
					$h = hash('sha1', $data, true);
					do {
						if ($h[$zbytes--])
							return false;
					} while ($zbytes);
					return true;
				}
			} else {
				if (strnatcmp(phpversion(), '5.0.0') < 0) {
					function _hc_sha_valid($data, $zbytes) {
						$h = sha1($data);
						do {
							if ($h[$zbytes--]!=='0')
								return false;
						} while ($zbytes);
						return true;
					}
				} else {
					function _hc_sha_valid($data, $zbytes) {
						$h = sha1($data, true);
						do {
							if ($h[$zbytes--])
								return false;
						} while ($zbytes);
						return true;
					}
				}
			}
		}
	}

	function GetRecipients($users, &$emails, &$langs) {
		global $conn;
		foreach ($users as $uid) {
			$row = $conn->SelectRow('#__users', 'email,lang', ' WHERE id='.$uid);
			if (empty($row)) {
				global $d;
				$d->log(4, sprintf("User #%d does not exist!", $uid));
				continue;
			}
			$emails[] = $row['email'];
			$langs[] = $this->ValidLang($row['lang']);
		}
	}
	
/*	function GetRecipientEmails($ids) {
		global $conn;
		$r = array();
		foreach ($ids as $id) {
			$row = $conn->SelectRow('#__users', 'email', ' WHERE id='.$id);
			$r[(int)$id] = $row['email'];
		}
		return $r;
	} */
	
	function ValidLang($lang) {
		global $my, $d_deflang;
		if (!strlen($lang) || !$my->IsValidLang($lang))
			return $d_deflang;
		return $lang;
	}
	
	function _get_recp_by_gid($gid, $max) {
		global $conn,$my;
		$sql = "SELECT email,lang FROM #__users WHERE gid$gid AND published=1";
		if (!$max)
			$rows=$conn->GetArray($sql);
		else {
			$rs=$conn->SelectLimit($sql, $max);
			$rows = $rs->GetArray(); $rs = null;
		}
		$recp=array();
		$langs=array();

		// do not send email to the author himself
		foreach ($rows as $row) {
			if ($row['email'] != $my->email) {
				$recp[] = $row['email'];
				$langs[] = $this->ValidLang($row['lang']);
			}
		}
		return array($recp, $langs);
	}
	
	function admin_recp($max = 0) {
		return $this->_get_recp_by_gid('=5', $max);
	}

	// retrieve the correct notification list (email, lang)
	function notify_list($max = 0) {
		switch ($GLOBALS['d_event']) {
			case '0':
				return array();
			case '1':
				return $this->admin_recp($max);
			case '2':
				return $this->_get_recp_by_gid('=4', $max);
		} // case '3':
		return $this->_get_recp_by_gid('>=4', $max);
	}

	function _rfc2045_safe($data) {
		$p = strpos($data, "\n");
		if ($p!==false)
			$data = substr($data,0,$p);
		$p = strpos($data, "\r");
		if ($p!==false)
			$data = substr($data,0,$p);
		return $data;
	}
	
function _need_qp($ascii) {
	switch ($ascii) {
		case 61:
		// parenthesis are added experimentally
		case 40:
		case 41:
			return true;
		case 9:
			return false;
//		default:
	}
	return (($ascii<33) || ($ascii>126));
}

//Q-encoding compliant to RFC2047
function _qp_encode($string) {
       $len = strlen($string);
       $result = '';
	$enc = false;
	for($i=0;$i<$len;++$i) {
		$c = $string[$i];
		// replace spaces with underscore
		if ($c==' ')
			$result.='_';
		else if (!$this->_need_qp(ord($c)))
			$result.=$c;
		else {
			$result.=sprintf("=%02X", ord($c));
			$enc = true;
		}
       }
	if (!$enc) {
		// check the trailing character, which must not be a space
		$ch = ord($string[$len-1]);
		if ($ch==9)
			$string = substr($string, 0, $len-1).'=09';
		else if ($ch==32)
			$string = substr($string, 0, $len-1).'_';
		else return $string;
		$result = $string;
	}
	global $d;
	return '=?'.raw_strtoupper($d->Encoding()).'?Q?'.$result.'?=';
}

// old B encoding
/*	$l=strlen($s);
		for ($i=0;$i<$l;++$i) {
			if (ord($s{$i})>127)
				return '='.raw_strtoupper($d->Encoding()).'?B?'.base64_encode($s).'?=';
		}
		return $s; */

	function craft_headers($from_name,$from_email) {
		global $my,$d_website,$conn,$d_email_split;
		//$headers = 'Received: from user'.$my->id.' ('.$my->GetIP().	') by '.$_SERVER["SERVER_NAME"].' via '.$d_website.$_SERVER['SCRIPT_NAME'].'; '.date('r')."\n";
		$from_name = $this->_rfc2045_safe($from_name);
		$from_email = $this->_rfc2045_safe($from_email);
		$headers = "From: ".$this->_qp_encode($from_name)." <".$from_email.">\n";
		$c=count($this->bcc);
		if ($c && !$d_email_split) {
			$headers.='Bcc: ';
			for($i=0;$i<$c-1;$i++)
				$headers .= $this->bcc[$i].', ';
			$headers .= $this->bcc[$c-1]."\n";
		}
		//EXPERIMENTAL Cc headers support
		/*$c=count($this->cc);
		if ($c && !$d_email_split) {
			$headers.='Cc: ';
			for($i=0;$i<$c-1;$i++)
				$headers .= $this->cc[$i].', ';
			$headers .= $this->cc[$c-1]."\n";
		} */
		// set return-path to values specified during installation
		// useful for returned emails
		global $d_email_from, $d_email_name;
		$headers .= 'Reply-To: '.$this->_qp_encode($d_email_name).' <'.$d_email_from.">\n";
		$headers .= 'Return-Path: '.$this->_qp_encode($d_email_name).' <'.$d_email_from.">\n";
		$headers .= "Message-ID: <".md5($my->id.time())."@".$this->_domain.">\n";
		if ($my->id!==false)
			$headers .= 'X-Authenticated: #'.(encode_userid($my->id))."\n";
		//$headers .= "X-Priority: 3\nX-MSMail-Priority: Low\n";
		$headers .= 'X-Mailer: Lanius CMS v'.cms_version()."\n";
//		$headers .= 'Date: '.gmdate('r')."\n";	//can cause invalid date spam marking!
		return $headers;
	}
	
	function Send($recipient, $subject, $message, $from_email=null, $from_name=null) {
		if(!isset($from_email))	//note: an empty string was used in older Lanius CMS versions
			$from_email = $GLOBALS['d_email_from'];
		// required for compatibility
		if(!isset($from_name))
			$from_name = $GLOBALS['d_email_name'];
		//else $from_name = entity_decode($from_name);
		
		$this->_from = $from_email;
		if (!isset($this->_domain))
			$this->_domain = substr($this->_from, strpos($GLOBALS['d_email_from'], '@')+1);
//		$subject = entity_decode($subject);
		
		if (isset($this->attach)) {
			$boundary = '------------'.random_string(24);
			$intro = "This is a multi-part message in MIME format\n\n".
						'--'.$boundary."\n";
		} else $intro='';

		$headers = $this->craft_headers($from_name, $from_email);

		global $d, $d_force_text_email;
		if ($this->html && $d_force_text_email) {
			$html = false;
			$message = html_to_text($message);
		} else $html = $this->html;
		$ctype = 'Content-Type: text/'.($html?'html':'plain').'; charset="'.$d->Encoding().'"'.
			"\nContent-Transfer-Encoding: 7bit\n";

		if ($html) {
			$message = "\n<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html><head><meta content=\"text/html;charset=".$d->Encoding()."\"\n http-equiv=\"Content-Type\"></head><body>\n".$message;
		}

		if (isset($this->attach)) {	// if this is an attachment the content-type will be inline
			$headers .= "MIME-Version: 1.0\n";
			$headers .= 'Content-Type: multipart/mixed;'."\n\t".
								"boundary=\"$boundary\"\n";
			$message = $ctype."\n".$message;
		} else {	// if this is not an attachment the content type will precede the message in headers
			$headers .= $ctype;
			$message = "\n".$message;
		}

//		else $message = entity_decode($message);

		$message = $intro.$message;
		if ($html)
			$message .= "</body></html>";

		$message.="\n\n";

		if (isset($this->attach)) {
			global $d_root, $mime;
			include_once $d_root.'includes/download.php';
			if (!in_array($this->attach[2], $mime))
				$this->attach[2] = $mime['exe'];
			
			$message = $message.'--'.$boundary."\n".
						'Content-Type: '.$this->attach[2].";\n\tname=\"".$this->attach[1]."\"\n".
						'Content-Transfer-Encoding: base64'."\n".
						'Content-Disposition: attachment;'."\n\t".
						'filename="'.$this->attach[1].'"'."\n\n".
						chunk_split(base64_encode(file_get_contents($this->attach[0]))).
						"\n\n".'--'.$boundary."\n\n";
		}
		return $this->_actual_send($recipient,
						$this->_qp_encode($this->_rfc2045_safe($subject)), $message, $headers);
	}
	
	function _hc_next_suffix() {
		return dechex($this->_hc_base++);
	}
	
	function _hashcash($recp, $bits = 20) {
//		$hcg = mt_float();
		$hdr = '1:'.$bits.':'.gmstrftime('%y%m%d', $GLOBALS['time']).':'. // global GMT $time is used here
				$recp.'::'.random_string(8).':';
		$b = (int)($bits / 8);
		$this->_hc_base = mt_rand();
		$suffix = $this->_hc_next_suffix();
		while (!_hc_sha_valid($hdr.$suffix, $b))
			$suffix = $this->_hc_next_suffix();
//		echo sprintf("Hashcash generation required %.2f seconds\n", mt_float()-$hcg);
		return 'X-Hashcash: '.$hdr.$suffix."\n";
	}
	
	// send a message to multiple recipients
	function _batch_send(&$recipients, &$encoded_subject, &$message, &$headers) {
		global $d_email_hashcash, $d;
		$done = 0;
		foreach($recipients as $recp) {
			if (!@$this->mail($recp, $encoded_subject, $message, $headers.
				($d_email_hashcash ? $this->_hashcash($recp) : ''),
				$this->_from, $this->cc, $this->bcc)) {
					$d->log(4, 'Could not send email');
			} else ++$done;
		}
		// return true if at least one message was delivered
		return ($done!=0);
	}
	
	function _actual_send($recipient, $encoded_subject, &$message, $headers) {
		global $d_email_split;
		// if $recipient was not set, set it to the notification recipients
		if (!isset($recipient)) {
			// get the recipients list only
			$recipients = current($this->notify_list());
			if (!count($recipients)) { // erroneous condition!
				return 0;
			}
			if (!$d_email_split)
				$recipient = implode(', ', $recipients);
			else
				$recipient = current($recipients);
		} else
			$recipients = array($recipient);
		
		// if email splitting is enabled, send (separately) to each recipient)
		if ($d_email_split) {
			return ($this->_batch_send($recipients, $encoded_subject, $message, $headers) &&
					$this->_batch_send($this->bcc, $encoded_subject, $message, $headers));
		}

		// email splitting is disabled, send email in the usual way (eventually adding the hashcash header)
		global $d_email_hashcash;
		switch($d_email_hashcash) {
			case 1:
				// if there are no Bcc recipients and the recipient is a single one, then add the header
				// this condition very rarely applies in generated sent emails
				if (!count($this->bcc) && (count($recipients)==1))
					$headers .= $this->_hashcash($recipient);
			break;
			case 2:
				foreach($this->bcc as $recp) {
					$headers .= $this->_hashcash($recp);
				}
				foreach($recipients as $recp) {
					$headers .= $this->_hashcash($recp);
				}
				foreach($this->cc as $recp) {
					$headers .= $this->_hashcash($recp);
				}
			break;
		}

		if (@$this->mail($recipient, $encoded_subject, $message, $headers, $this->_from, $this->cc, $this->bcc))
			return true;
		global $d;
		$d->log(4, 'Could not send email');
		return false;
	}
	
	// used to create an internationalized email message based on the provided sprintf arrays
	// returns an array with the subject and the body
	function _craft_email($subject, $message, $lang, $component) {
		$defines = parse_defines(file_get_contents(com_lang($lang, $component.'.notify')));
		$subject[0] = $defines[$subject[0]];
		$message[0] = $defines[$message[0]];
		$defines = null;
		$r = array(
			call_user_func_array('sprintf', $subject),
			call_user_func_array('sprintf', $message),
		);
		$message = $subject = null;
		return $r;
	}
	
	// used to send internationalized messages to multiple recipients
	function I18NSend($emails, $langs, $subject, $message, $component) {
		$c=count($emails);
		// no recipients, exit
		if ($c==0) return 0;
		// create an array of emails with the same body (depending on the language)
		if ($c>1) {
			// sort the languages
			asort($langs);
			$bodies = array();
			$dest = array();
			$last_lang = '';
			$i = 0;
			foreach($langs as $index => $lang) {
				// check if the same-language chain has ended
				if ($last_lang!=$lang) {
					// create a new body
					$bodies[] = $this->_craft_email($subject, $message, $lang, $component);
					// append a new empty array (index is already $i)
					$dest[] = array();
					$last_lang = $lang;
				}
				$dest[$i][] = $emails[$index];
				// increment index if language has changed
				if ($last_lang != $lang) ++$i;
			} $subject = $message = null;
		} else {
			$bodies = array ($this->_craft_email($subject, $message, $langs[0], $component));
			$dest = array($emails);
			$i=1;
		}
		return $this->_raw_i18n_send($i, $bodies, $dest);
	}
	
	function I18NSendOne($email, $lang, $subject, $message, $component) {
		$bodies = array(
			$this->_craft_email($subject, $message, $lang, $component)
		);
		$dest = array(
			array($email)
		);
		return $this->_raw_i18n_send(1, $bodies, $dest);
	}
	
	function I18NSendNotify($subject, $message, $component) {
		$recp = $this->notify_list();
		return $this->I18NSend($recp[0], $recp[1], $subject, $message, $component);
	}
	
	// given $total couples of $bodies and $dest recipients, send them
	function _raw_i18n_send($total, &$bodies, &$dest) {
		$sent = 0;
		// now actually send the emails, eventually grouping some in Bcc'ed emails
		for($i=0;$i<$total;++$i) {
			// single destination recipient
			if (count($dest[$i])==1)
				$sent += $this->Send($dest[$i][0], $bodies[$i][0], $bodies[$i][1].$this->_trail_msg());
			else { // multiple destination recipient (but with same subject/body
				$this->bcc = $dest[$i];
				$sent += $this->Send(null,  $bodies[$i][0], $bodies[$i][1].$this->_trail_msg());
				$this->bcc = array();
			}
		}
		$bodies = null;
		return $sent;
	}
	
	function _trail_msg() {
		$s = "\n\n"._NO_ANSWER_MSG;
		if ($this->html)
			return '<sub><pre>'.$s.'</pre></sub>';
		return $s;
	}
	
	function mail($recipient, $subject, $message, $headers = '', $from = '', $cc = array(), $bcc = array()) {
		global $_DRABOTS;
		if (!$this->_bots_loaded) {
			$_DRABOTS->loadCoreBotGroup('mail');
		}
		$r = $_DRABOTS->trigger('onMail', array($recipient, $subject, $message, $headers, $from, $cc, $bcc));
		// will return true also if no mailer did anything, this is correct
		$ov = true;
		// check that all mailers have successfully finished their processes
		foreach($r as $v) {
			$ov &= $v;
			if (!$ov)
				return false;
		}
		return $ov;
	}

} // GeloMail class

?>
