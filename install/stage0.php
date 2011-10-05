<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Lanius CMS EZInstall script
#
# Stage 0 - license is shown

?>
<script language="javascript" type="text/javascript">
function check_agreement(redir) {
	var cb = document.getElementById('gpl_agree');
	if (cb.checked) {
		if (redir) {
			document.location = 'index.php?stage=1';
			return;
		}
		return true;
	}
	alert('<?php echo js_enc(_INSTALL_GPL_AGREE_MSG); ?>');
	if (redir)
		return;
	return false;
}
</script>

<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0">
  <tr>
    <td width="40%"><img src="../media/common/logo.png" alt="Logo"></td>
    <td width="60%"><p><strong><?php echo _INSTALL_PRECHECK_WELCOME; ?></strong></p>
      <p><?php echo _INSTALL_PRECHECK_INSTALL; ?></p>
      <p align="right">[ <a target="_blank" href="http://www.laniuscms.org"><?php echo _INSTALL_TEAM; ?></a> ]</p></td>
  </tr>
  <tr>
    <td colspan="2" bgcolor="#F7F7F7" class="menuheader"><strong><?php echo _INSTALL_GPL; ?></strong> </td>
  </tr>
  <tr>
    <td colspan="2"><?php echo _INSTALL_READ_LICENSE; ?></td>
  </tr>
  <tr>
    <td colspan="2" class="gpl"><hr/>
	<?php
		// show proper license
		$lic = 'lang/'.$my->lang.'/docs/gpl.htm';
		if (!is_file($d_root.$lic))
			$lic = 'lang/en/docs/gpl.htm';
		$lic = file_get_contents($d_root.$lic);
		$p=strpos($lic, '<body>');
		$lic = substr($lic, $p+6);
		$p=strpos($lic, '</body>');
		$lic = substr($lic, 0, $p);
		echo $lic;
		$lic = null;
	?>
    </td>
  </tr>
  <tr>
    <form onsubmit="return check_agreement(false)" name="stager" id="stager" method="GET" action="index.php"><td width="60%">
      <input type="hidden" value="1" name="stage" />
      <label for="gpl_agree">
      <input type="checkbox" name="gpl_agree" id="gpl_agree" />
      <?php echo _INSTALL_LICENSE_AGREE; ?></label>
      </td>
      <td align="right"><a href="javascript:check_agreement(true)" class="menulink"><?php echo _INSTALL_CONTINUE; ?></a>
        <noscript>
        <input type="submit" value="Submit" />
        </noscript>
    
    </td>
</form>  </tr>
</table>
