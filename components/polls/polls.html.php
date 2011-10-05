<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function view_polls($pollid = null) {
    global $conn, $d_website, $d_subsite, $Itemid, $access_sql;
    
    $tabclass_arr = array("dkcom_tablerow1", "dkcom_tablerow2");
    $tabcnt = 0;
    
    if (!isset($pollid)) {
	global $params;
	$pollid = $params->get('poll_id', 0);
    }

	global $d;
	$d->add_raw_js("
function jumpMenu(targ,selObj,restore){ //v3.0
  if(selObj.options[selObj.selectedIndex].value=='')return;
	  eval(targ+\".location='index.php?option=polls&Itemid=$Itemid&pollid=\"+selObj.options[selObj.selectedIndex].value+\"'\");
  if (restore) selObj.selectedIndex=0;
}");
?><div class="dk_header"><h2><?php echo _POLLS_TITLE; ?></h2></div>
    <div>&nbsp;</div>
    <div class="dk_aligncenter">
      <span>&nbsp;<?php echo _SEL_POLL; ?>&nbsp;&nbsp;</span>
      <span>
        <select name="menu1" id="menu1" onchange="jumpMenu('parent',this,0)" class="dk_inputbox">
          <option value="">
            <?php echo _SEL_POLL_LIST; ?>
          </option>
		  <?php
             $rsa = $conn->SelectArray('#__categories', 'id,name,section', " WHERE section='com_polls' $access_sql ORDER BY ordering ASC");
			foreach($rsa as $row) {
				echo "<option value='" . $row['id'] . "' ".(($row['id']==$pollid)?"selected=\"selected\"":"").">" . $row['name'] . "</option>";
			}
           ?>
        </select>
      </span>
    </div>
    <div class="dk_content">
      <div class="dk_aligncenter">
        <br/>
	  <?php if ($pollid) {
        $rsar = $conn->SelectRow('#__categories', 'id,name,section', " WHERE id=$pollid $access_sql");
        if ($rsar) {
            $poll_data = $conn->SelectArray('#__polls_data', 'id,polloption,hits'," WHERE pollid=$pollid ORDER BY id ASC");

            $sumval = 0;
            foreach($poll_data as $row) {
                $sumval += $row['hits'];
            }
            ?>
        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="dk_content">
          <tr class="dkcom_tableheader">
            <td colspan="2" ><?php echo $rsar['name']; ?> </td>
          </tr>
          <?php
            $maxval = 0;
            if ($maxval < $sumval) {
                $maxval = $sumval;
            }
            $polls_graphwidth = 200;
            $polls_barheight = 2;
            $polls_maxcolors = 5;
            $polls_barcolor = 0;

            for ($i = 0, $n = count($poll_data); $i < $n; $i++)
            {
                $text = $poll_data[$i]["polloption"];
                $hits = $poll_data[$i]["hits"];

                if ($maxval > 0 && $sumval > 0)
                {
                    $width = ceil($hits * $polls_graphwidth / $maxval);
                    $percent = round(100 * $hits / $sumval, 1);
                }
                else
                {
                    $width = 0;
                    $percent = 0;
                }
          ?>
          <tr class="<?php echo $tabclass_arr[$tabcnt];?>">
            <td class="dk_alignleft" width="100%" colspan="2">
              <?php echo $text; ?>
            </td>
          </tr>
          <tr class="<?php echo $tabclass_arr[$tabcnt]; ?>">
            <td>
              <table border="0" align="left" cellpadding="0" cellspacing="0" >
                <tr class="<?php echo $tabclass_arr[$tabcnt]; ?>">
                  <td align="right" width="25"> <strong><?php echo $hits;?></strong> </td>
                  <td align="left" width="2">&nbsp; </td>
                  <td width="30" align="left"> <?php echo $percent;?>% </td>
                  <?php
                $tdclass = '';
                $colorx = 0;
                if ($polls_barcolor == 0) {
                    if ($colorx < $polls_maxcolors) {
                        $colorx = ++$colorx;
                    } else {
                        $colorx = 1;
                    }
                    $tdclass = "pollscolor" . $colorx;
                } else {
                    $tdclass = "pollscolor" . $polls_barcolor;
                }
                ?>
                  <td align="left">
                      &nbsp;
                      <img src='<?php echo $d_subsite;?>media/common/spacer.png' class='<?php echo $tdclass;?>' height='<?php echo $polls_barheight;?>' width='<?php echo $width;?>' alt="&nbsp;" />
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <?php
                $tabcnt = 1 - $tabcnt;
            }

          ?>
        </table>
        <br />
          <span class="dk_small"><?php echo _NUM_VOTERS;?></span>
          <span class="dk_small">&nbsp;:&nbsp;<?php echo $sumval; ?></span>
          <?php
        }
    } ?>
      </div>
	</div>
	<?php
}
?>