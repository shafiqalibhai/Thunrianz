<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
echo '<?xml version="1.0" encoding="'. $d->Encoding() .'"?' .'>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php $d->ShowHead(); ?>
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
</head>
<body>
<a name="up" id="up"></a> 
<table  width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class="tpl_maintable" >
  <tr> 
    <td valign="top"><table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="tpl_headercenter1" >
        <tr> 
          <td colspan="3" style="height:1px;"></td>
        </tr>
        <tr> 
          <td width="10" class="tpl_topleft1">&nbsp;</td>
          <td ><table style="width:100%;" border="0" cellspacing="0" cellpadding="0">
              <tr> 
                <td><h1 class="title"><?php echo $d_title; ?></h1></td>
                <td align="right" style="width:509px; height:71px;" class="tpl_headercenter3"> 
                  <?php $d->LoadModules( "banner" ); ?>
                </td>
              </tr>
            </table></td>
          <td width="10" height="10" class="tpl_topright1">&nbsp;</td>
        </tr>
        <tr> 
          <td width="10" rowspan="2" class="tpl_topleft2">&nbsp;</td>
          <td><table style="width: 100%; height:24px"  border="0" cellpadding="0" cellspacing="0">
              <tr> 
                <td width="50%"><span class="pathway"> 
                  <?php //$d->PathWay(); ?>
                  </span> </td>
                <td width="50%" align="right"><div class="date"><?php echo $d->CurrentDate(); ?></div></td>
              </tr>
            </table></td>
          <td rowspan="2" class="tpl_topright2">&nbsp;</td>
        </tr>
        <tr> 
          <td ><table width="100%"  border="0" cellpadding="0" cellspacing="0">
              <tr> 
                <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr> 
                      <td> 
                        <?php $d->LoadModules ( 'user3'); ?>
                      </td>
                      <td width="170" align="right"> 
                        <?php $d->LoadModules ( 'user4'); ?>
                      </td>
                    </tr>
                  </table></td>
              </tr>
            </table></td>
        </tr>
      </table>
      <table width="100%"  border="0" cellspacing="0" cellpadding="0" class="tpl_tablecenter" >
        <tr> 
          <td><table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" >
              <tr> 
                <td valign="top" class="tpl_columnleft"> 
                  <?php if ($d->CountModules('left')) { ?>
                  <table width="188"  border="0" cellpadding="0" cellspacing="0">
                    <tr> 
                      <td> 
                        <?php $d->LoadModules ( 'left' ); ?>
                      </td>
                    </tr>
                  </table>
                  <?php } ?>
                  <br /></td>
                <td  width="100%" valign="top" class="tpl_columncenter"><?php if ($d->CountModules('top')) { ?>
                  <table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="tpl_modulecenter">
                    <tr>
                      <td><?php $d->LoadModules ( 'top' ); ?>
                      </td>
                    </tr>
                  </table>
                  <?php } ?>
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="50%" valign="top"><?php if ($d->CountModules('user1')) { ?>
                          <table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="tpl_modulecenter">
                            <tr>
                              <td><?php $d->LoadModules ( 'user1' ); ?>
                              </td>
                            </tr>
                          </table>
                        <?php } ?>
                      </td>
                      <td width="50%" valign="top"><?php if ($d->CountModules('user2')) { ?>
                          <table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="tpl_modulecenter">
                            <tr>
                              <td><?php $d->LoadModules ( 'user2' ); ?>
                              </td>
                            </tr>
                          </table>
                        <?php } ?>
                      </td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellspacing="0" cellpadding="4">
                    <tr>
                      <td><?php
							$d->MainBody();
					  ?>
                      </td>
                    </tr>
                  </table>
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td width="50%" valign="top"><?php if ($d->CountModules('user5')) { ?>
                          <table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="tpl_modulecenter">
                            <tr>
                              <td><?php $d->LoadModules ( 'user5' ); ?>
                              </td>
                            </tr>
                          </table>
                        <?php } ?>
                      </td>
                      <td width="50%" valign="top"><?php if ($d->CountModules('user6')) { ?>
                          <table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="tpl_modulecenter">
                            <tr>
                              <td><?php $d->LoadModules ( 'user6' ); ?>
                              </td>
                            </tr>
                          </table>
                        <?php } ?>
                      </td>
                    </tr>
                  </table>
                  <?php if ($d->CountModules('bottom')) { ?>
                  <table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="tpl_modulecenter">
                    <tr>
                      <td><?php $d->LoadModules ( 'bottom' ); ?>
                      </td>
                    </tr>
                  </table>
                  <?php } ?></td>
                <td valign="top" class="tpl_columnright"> 
                  <?php if ($d->CountModules('right')) { ?>
                  <table style="width:188px; height:100%"  border="0" cellpadding="0" cellspacing="0">
                    <tr> 
                      <td> 
                        <?php $d->LoadModules ( 'right' ); ?>
                      </td>
                    </tr>
                  </table>
                  <?php } ?>
                  <br /></td>
              </tr>
            </table></td>
        </tr>
      </table>
      <table border="0" align="center" cellpadding="0" cellspacing="0" style="width: 100%;height:104px;" class="tpl_footercenter2">
        <tr> 
          <td width="10" class="tpl_footerleft3">&nbsp;</td>
          <td align="right" valign="bottom" ><table width="740" border="0" cellspacing="0" cellpadding="0" style="height:75px;" class="tpl_footercenter4">
            <tr>
              <td align="right"><a href="#up"><img src="<?php echo $d_subpath.'templates/'.$d_template.'/images/space.png'; ?>" border="0" width="20" height="20" alt="" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
          </table></td>
          <td width="10" class="tpl_footerright3">&nbsp;</td>
        </tr>
      </table>
      <table width="100%" border="0" align="center" cellpadding="0" cellspacing="10" >
        <tr> 
          <td align="center" ><span class="copyright">Powered by <a href="http://shafiq.in" target="_blank">Shafiq Issani</a></span></td>
        </tr>
      </table></td>
  </tr>
</table>
<?php $d->LoadModules('debug'); ?>
</body>
</html>
