<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

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

<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0" class="tpl_maintable">

  <tr>

    <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0" style="height:92px;" class="tpl_headerfill">

      <tr>

        <td ><table width="400" border="0" cellspacing="0" cellpadding="0" style="height:92px;" class="tpl_headerlogo">

            <tr>

              <td width="160" style="height:20px;"><?php $d->LoadModules ( 'user4'); ?></td>

              <td style="height:20px;"><div class="date"><?php echo $d->CurrentDate(); ?></div></td>

            </tr>

            <tr>

              <td colspan="2" style="height:72px;"><h1 class="title"><a href="#" title="<?php echo $d_title; ?>"><?php echo $d_title; ?></a></h1></td>

            </tr>

        </table></td>

        <td width="100%" align="center" style="height:92px;" ><?php $d->LoadModules( "banner" ); ?>

        </td>

      </tr>

    </table>

    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="height:25px;" class="tpl_toolbar">

        <tr>

          <td><span class="pathway"><?php //$d->PathWay(); ?></span></td>

          <td width="50%"><?php $d->LoadModules ( 'user3'); ?></td>

        </tr>

      </table>

      <table width="100%"  border="0" cellpadding="0" cellspacing="0">

        <tr>

          <td valign="top"  class="tpl_columnleft"><?php if ($d->CountModules('left')) { ?>

            <table width="178"  border="0" cellpadding="0" cellspacing="0">

              <tr>

                <td><?php $d->LoadModules ( 'left' ); ?>				</td>

			</tr>

            </table>

            <?php } ?>

          <br /></td>

          <td width="100%" valign="top" class="tpl_columncenter"><?php if ($d->CountModules('top')) { ?>

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

                      <td><?php $d->LoadModules ( 'user5' ); ?>                      </td>

                    </tr>

                  </table>

                  <?php } ?>                </td>

                <td width="50%" valign="top"><?php if ($d->CountModules('user6')) { ?>

                  <table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="tpl_modulecenter">

                    <tr>

                      <td><?php $d->LoadModules ( 'user6' ); ?>                      </td>

                    </tr>

                  </table>

                  <?php } ?>                </td>

              </tr>

            </table>

		    <?php if ($d->CountModules('bottom')) { ?>

            <table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0" class="tpl_modulecenter">

              <tr>

                <td><?php $d->LoadModules ( 'bottom' ); ?>

                </td>

              </tr>

            </table>

            <?php } ?>

          </td>

		  <td valign="top" class="tpl_columnright"><?php if ($d->CountModules('right')) { ?>

            <table width="178"  border="0" cellpadding="0" cellspacing="0">

              <tr>

                <td><?php $d->LoadModules ( 'right' ); ?>

                </td>

              </tr>

            </table>

 <?php } ?> <br />

          </td>

        </tr>

      </table>

      <table width="100%" border="0" cellpadding="0" cellspacing="0" style="height:20px;" class="tpl_footer">

        <tr>

          <td width="178">&nbsp;</td>

          <td align="center"><span class="copyright">Copyright &copy;  <?php echo lc_strftime('%Y').' '.$GLOBALS['d_title']; ?></span></td>

          <td width="178" align="center"><span class="copyright">Powered by <a href="http://shafiq.in" target="_blank">Shafiq Issani&nbsp;</a></span></td>

        </tr>

      </table></td>

  </tr>

</table>

<?php $d->LoadModules('debug'); ?>

</body>

</html>