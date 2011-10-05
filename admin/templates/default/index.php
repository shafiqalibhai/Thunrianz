<?php if(!defined('_VALID_ADMIN')){header('Status: 404 Not Found');die;}
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title><?php echo $d->title.' ';
			if ($d_subpath!=='') echo 'Subsite ';
			echo 'Administration'; ?></title>
<?php echo $d->ShowMainHead(); ?>
<?php echo AdminMenu::Head(); ?>
<script language="javascript" src="<?php echo $d_subpath; ?>admin/includes/js/dhtml.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $d_subpath; ?>admin/templates/default/css/template.style.css" type="text/css" />
</head>
<body>
<?php echo $d->pre_body; ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="320" class="top-logo" >
		<!--<img src="<?php echo $d_subpath; ?>admin/templates/default/images/<?php if ($d_subpath!=='') { echo 'subsite_'; $alt = 'Subsite Administration'; } else { $alt = 'Administration'; } ?>header.png" alt="<?php echo $alt; ?>" />--> <p class="dlinks">Admin Panel - Thunrianz</p>
	</td>
    <td width="240" class="top-update" >
<!-- <a class="dlinks" title="<?php echo _UPDATE_WIZARD;?>" href="<?php echo $d_subpath; ?>admin.php?com_option=system&amp;option=autoupdate"><img border="0" src="<?php echo $d__server; ?>services/status.png.php?v=<?php echo urlencode(cms_version(true)); ?>" alt="<?php echo _UPDATE_WIZARD;?>"  /></a>-->	
	</td>
    <td align="right" class="top-logo" ><!-- <a href="index.php?option=login&amp;task=logout" class="wlink" style="color: #e5e5e5"><img src="<?php echo $d_subpath; ?>admin/templates/default/images/logout.png" border="0" alt="" />&nbsp;<?php echo _LOGOUT; ?></a>&nbsp;--></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr class="toolmenu">
                <td height="25"><?php echo AdminMenu::Content(); ?></td>
                <td align="right">
                    <table class="hotlinks" border="0" cellspacing="0" cellpadding="2">
                      <tr><td>&nbsp;</td>
						<?php if ($d_stats) { ?>
                        <td nowrap><font style="font-size: 11px; font-weight: bold;"><?php
	$srow=$conn->SelectRow('#__simple_stats', 'count', " WHERE id=1");
	echo $srow['count'];
?>
                          (
                          <?php
$c=$conn->SelectCount('#__simple_stats');
echo ($c ? $c : '0'); ?>
                          )</font></td><td>			  
                        <img src="<?php echo $d_subpath; ?>admin/templates/default/images/stats.png" alt="<?php echo _TOTAL_HITS; ?>" width="16" height="16" /></td><?php } ?>
                      </tr>
                    </table>
                </td>
                <td align="right"><a href="index.php?option=login&amp;task=logout" class="wlink" style="color: #000000"><img src="<?php echo $d_subpath; ?>admin/templates/default/images/logout.png" border="0" alt="" />&nbsp;<?php echo _LOGOUT; ?></a><?php //$toolbar->HelpIcon(); ?></td>
              </tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0">
<tr><td class="pathway-backend"><?php
	//echo $pathway;
?></td>
	</tr>
</table>
	<div class="dka_component"><?php
		// echoes the buffered component
		$d->DumpComponent();
	?></div>
	<div class="footer">
	</div>
<?php
	// small hack to use a frontend module in the backend
	//$d->_modules['debug'] = $d->PreloadPosition('debug');
	//$d->LoadModules('debug');
?>
</body>
</html>
