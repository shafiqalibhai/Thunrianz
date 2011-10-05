<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script language="javascript" type="text/javascript">
    <!--
    function MM_reloadPage(init) {  //reloads the window if Nav4 resized
      if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
        document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
      else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
    }
    MM_reloadPage(true);
    //-->
	
  </script>
<?php   
  $d->ShowHead();
  ?>
</head>
<body>
<center>

 
  <div id="container"> <!-- main container -->
 
    
    <div id="header"><!-- header block -->
<table cellpadding="0" cellspacing="0" >
  <tr>
    <td>
		 <div id="logo"></div>
		 <div id="date"><?php echo $d->CurrentDate(); ?></div>
		<?php if ($d->CountModules('inset')) { ?>
			<div id="insetbox"><?php $d->LoadModules ( 'inset'); ?></div>
		<?php } ?> 
		<?php if ($d->CountModules('banner')) { ?>
			<div id="bannerbox"><?php $d->LoadModules ( 'banner'); ?></div>
		<?php } ?>
	</td>
    <td>
		<?php if ($d->CountModules('user4')) { ?><div id="user4box"><?php $d->LoadModules ( 'user4'); ?></div><?php } ?>
		<?php if ($d->CountModules('user3')) { ?><div id="user3box"><?php $d->LoadModules ( 'user3'); ?></div><?php } ?>
		<div id="titlebox"><?php echo $d_title; ?></div>
		
	</td>
  </tr>
</table>
    </div><!-- end header block -->
<div id="pathway"><?php //$d->PathWay(); ?></div>
    
    <div id="middle"><!-- middle block -->
      <table border="0" cellpadding="0" cellspacing="0" >
        <tr>
          <td valign="top"  id="td_left" >
		  <?php if ($d->CountModules('left')) { ?>
            <div id="leftbox">
              <?php $d->LoadModules ( 'left' ); ?>
            </div>
          <?php } ?>
          </td>
          <td width="100%" valign="top" id="td_center" > 
            <?php if ($d->CountModules('top')) { ?>
            <div id="topbox">
		      <?php $d->LoadModules ( 'top' ); ?>
		    </div>
		  <?php } ?>
				<table cellpadding="0" cellspacing="0" >
				<tr>
				<td>
				<?php if ($d->CountModules('user1')) {?>
				<div id="user1box">
				<?php $d->LoadModules ( 'user1' ); ?>
				</div>
				 <?php } ?>
				</td>
				<td>
				<?php if ($d->CountModules('user2')) {?>
				<div id="user2box">
				<?php $d->LoadModules ( 'user2' ); ?>
				</div>
				<?php } ?>
				</td>
				</tr>
			</table>
					<div id="content">
					  <?php	$d->MainBody();	  ?>
					</div>
				<table cellpadding="0" cellspacing="0" >
				<tr>
				<td>
				<?php if ($d->CountModules('user5')) {?>
				<div id="user5box">
				<?php $d->LoadModules ( 'user5' ); ?>
				</div>
				 <?php } ?>
				</td>
				<td>
				<?php if ($d->CountModules('user6')) {?>
				<div id="user6box">
				<?php $d->LoadModules ( 'user6' ); ?>
				</div>
				<?php } ?>
				</td>
				</tr>
			</table>
		    <?php if ($d->CountModules('bottom')) { ?>
            <div id="bottombox">
		      <?php $d->LoadModules ( 'bottom' ); ?>
		    </div>
		  <?php } ?>
		  </td>
          <td valign="top"  id="td_right" > 
            <?php if ($d->CountModules('right')) { ?>
            <div id="rightbox">
              <?php $d->LoadModules ( 'right' ); ?>
            </div>
            <?php } ?>
          </td>
        </tr>
      </table>
    </div><!-- end middle block -->
   
    
    <div  id="footer"><!-- footer block -->
      <table cellpadding="0" cellspacing="0" >
        <tr>
          <td>
		  <div id="copyright">Copyright &copy; 
		  <?php echo lc_strftime('%Y').' '.$GLOBALS['d_title']; ?>
		  </div>
		  </td>
          <td>
		  <div id="powered">Powered by
		  <a href="http://shafiq.in" target="_blank">Shafiq Issani</a>
		  </div>
		  </td>
        </tr>
      </table>
    </div><!-- end footer block -->
  </div><!-- end main container -->
  <?php $d->LoadModules('debug'); ?>
</center>
</body>
</html>
