<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## PHP error tracer utility functions
# @author legolas558
#
# functions used by main code in @see includes/errtrace.php

function echo_url(&$ref, $textonly) {
	if (strlen($ref)) {
		global $d_website;
		// when does this happen?
		if (!isset($d_website)) {
                        $p=strrpos($ref, '/');
                        if ($p===false) { echo '-'; $ref = ''; } else {
                        $ref = substr($ref, $p+1);
                        echo $ref;
                        }
                        return;
                }
		if (strpos(strtolower($ref), strtolower($d_website))===0) {
			$ref = substr($ref, strlen($d_website));
			echo $ref;
		} else {
			$ref = '';
			if (!$textonly)
				echo '<i>invalid</i>';
			else echo 'invalid';
			}
	} else { $ref ='';
		if ($textonly)
			echo ' -';
		else echo '&nbsp;-';
	}
}

function list_args($args, $textonly) {
	if (empty($args))
		return '';
	global $my;
	if (@$my->gid!=5)
		return count($args);
	$r = '';
	foreach ($args as $arg) {
		$t = gettype($arg);
		if ($t == 'object') $t = get_class($arg);
		elseif ($t == 'string') {
			$tmp = fix_string($arg, $len);
			if (!$textonly)
				$tmp = dbg_htmlspecialchars($tmp, false);
			$t.='<sub>['.$len.']</sub> = "'.$tmp.'"';
		} elseif (($t == 'integer') or ($t == 'double')) $t.=' = '.$arg;
		elseif ($t=='boolean') $t.=' = '.($arg?'true':'false');
		elseif ($t=='array') $t.='<sub>['.count($arg).']</sub>';
		$r.=$t.', ';
	}
	if (strlen($r))
		return substr($r, 0, -2);
	else
		return $r;
}

function advanced_report(&$dbg, &$lines, $ehash, $last, $error, $ref, $cur_url) {
	global $d__server;
	$lines[] = $last;	?>
	<big>Submit bug</big><small><small>
	<form name="error_form" method="post" action="<?php echo $d__server; ?>services/error.php">
		<input type="hidden" name="ehash" value="<?php echo $ehash; ?>" />
		<input type="hidden" name="edata" value="<?php echo xhtml_safe("\n==Previous page==\n$ref\n==Current page==\n$cur_url\n".$error); ?>" />
		<p>Specify the exact steps to perform in order to trigger this bug<?php
		if ($ref!=='') {?>(you came from page <a target="_blank" href="<?php echo $ref; ?>"><?php echo $ref; ?></a>)<?php }
		?>:</p>
		<p>&nbsp;<textarea name="comment" cols="55" rows="7" maxlength="1024">1. 

2. 

3. 
</textarea></p>
	<input style="height: 40px; width: 120px; font-weight: bold;" type="submit"/>
	</form>
	</small></small>
	<small><p align="left"><strong>NOTE: </strong>No information will be collected when clicking the submit button. You will be redirected to a page of the <a href="http://bugs.laniuscms.org/" target="_blank">Lanius CMS issue tracker</a> (click <a href="http://bugs.laniuscms.org/login_page.php" target="_blank">here to open its login page</a> in a new page) with pre-compiled fields for the above bug.</p>
	<p align="left">If the bug already exists, you will be able to add a bug note.</p></small>
	<?php 
}

function _cms_error_hash(&$hashlet) {
	$ehash = pack('H*', md5(strtolower($hashlet)));
	$l=strlen($ehash);
	$nhash='';
	for($i=0;$i<$l;$i=$i+4) {
		$sum = ord($ehash{$i})+ord($ehash{$i+1})+ord($ehash{$i+2})+ord($ehash{$i+3});
		$conv = base_convert($sum,10,36);
		if (strlen($conv)==1) $conv='0'.$conv;
		$nhash.=$conv;
	}
	function __raw_strtoupper_cb2($m) { return chr(ord($m[0])-32); }
	return preg_replace_callback('/[a-z]/', '__raw_strtoupper_cb2', $nhash);
}

?>