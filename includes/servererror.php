<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function service_msg($title, $msg, $explanation = '', $image = 'logo') {
global $d_title, $d_subpath;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>
<?php echo $d_title.' - '.$title ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
.border {
	border: 1px dashed #cccccc;
	padding: 20px;
}
.big {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 36px;
	font-weight: bold;
	color: #000000;
}
.big1 {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 24px;
	font-weight: bold;
	color: #999999;
}
td {
	font-size: 12px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
}
</style>
</head>
<body>
<table style="height:100%;" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center" valign="top" class="border">
		<h1><?php echo $d_title; ?></h1>
		<img src="<?php echo $d_subpath; ?>media/common/<?php echo $image; ?>.png" alt="<?php echo ucfirst($image); ?>" border="0" />      
		<h2><?php echo $title; ?></h2>
	    <pre class="big1"><?php echo $msg; ?></pre>
		<p style=""><?php echo $explanation; ?></p>
	</td>
  </tr>
  <tr>
    <td align="center" valign="top">Site Powered By <a href="<?php echo $GLOBALS['d__server']; ?>" target="_blank">Lanius CMS</a></td>
  </tr>
</table>
</body>
</html>
<?php

}

?>