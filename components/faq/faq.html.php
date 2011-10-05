<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function view_items($catid) {
	global $conn,$Itemid,$d_website,$params, $d, $d_subpath, $access_sql, $d_root;

	include_once $d_root.'classes/pagenav.php';
	$pn = new PageNav($params->get('show_count',10));
	
	if(!isset($catid)) { // show categories
	 ?>

<div class="dk_header">
  <h2><?php echo _FAQ_FAQH; ?></h2>
</div>


<?php
			$rsa = $pn->Slice('#__categories', 'id,name,description,count', "WHERE section = 'com_faq' $access_sql", "ORDER BY ordering ASC");
			if( isset($rsa[0]) ) {
				?>

	<table width="100%" border="0">
		<tr>
		  <td  class="dkcom_tableheader" width="80%"><?php echo _CAT; ?>:</td>
		  <td  class="dkcom_tableheader" align="center" width="20%"><?php echo _FAQ_QUESTIONS; ?></td>
		</tr>
		<?php
					$desc = $keyw = $catname='';
					foreach($rsa as $row) { ?>
		<tr>
		  <td><a href="index.php?option=faq&amp;catid=<?php echo $row['id']; ?>&amp;Itemid=<?php 
				echo $Itemid.content_sef($row['name']); ?>" ><?php echo $row['name']; ?></a><br />
			<?php echo$row['description']; ?></td>
		  <td align="center"><?php echo $row['count']; ?></td>
		</tr>
		<?php
					$desc.= ' '.$row['description'];
					$keyw .= ' '.$row['name'].' '.$row['description'];
					}
					$d->add_meta(content_description($desc), content_keywords($keyw)); $desc = $keyw = null;
					$navbar = $pn->NavBar('option=faq&Itemid='.$Itemid);
					if (strlen($navbar)) { ?>
		<tr>
		  <td><?php echo $navbar; $navbar = null;  ?></td><td>&nbsp;</td>
		</tr>
		</table>
		
		
	
    <?php
				}
			}
	} else {
	// show the questions 
	$row=$conn->SelectRow('#__categories', '*', " WHERE id = $catid");
	global $pathway;
	$pathway->add($row['name'], 'option=faq&catid='.$row['id']."&Itemid=$Itemid");
	$desc_empty = (strlen($row['description'])==0);
	$d->add_meta($desc_empty?'':content_description($row['description']), content_keywords($row['name'].' '.$row['description']));
	?>
    <a id="faq"></a>
    <div class="dk_header">
      <h2><?php echo $row['name']; ?></h2>
    </div>
    <table width="100%"  cellpadding="4" cellspacing="0" class="dk_content">
    
	  <tr>
        <td colspan="2"><?php 
		  if($row['image']!='')echo '<img src="'.$GLOBALS['d_subpath'].'media/icons/'.$row['image'].'" align="'.$row['image_position'].'" alt="'.$row['name'].'" />';
		  echo $row['description'];
		?>
        </td>
      </tr>
   
      <?php global $my; if ($my->can_submit()) { ?>

      <tr>
        <td><a href="index.php?option=faq&amp;Itemid=<?php echo $Itemid;?>"><strong><?php echo _FAQ_INDEX;?></strong></a>&nbsp;&nbsp;&nbsp;<a href="index.php?option=faq&amp;task=new&amp;Itemid=<?php echo $Itemid;?>"><strong><?php echo _FAQ_SUBMIT;?></strong></a></td>
       
      </tr><?php } ?>
  
  
      <tr>
      <td >
        <?php
				$rsa = $pn->Slice('#__faq', '*', "WHERE catid=$catid AND published=1", 'ORDER BY ordering DESC');
				if (isset($rsa[0])) {
				echo "<strong>"._FAQ_QUESTIONS."</strong><br /><ul>";
				$keyw = '';
				foreach($rsa as $row) { ?>
        <li><a href="#faq<?php echo $row['id'];?>"><?php echo $row['question'];?></a></li>
        <?php
					 $keyw .= ' '.$row['question'].' '.$row['answer'];
				}
				$d->add_meta($desc_empty ? content_description($keyw) :'', content_keywords($keyw)); $keyw = null;
				?>
        </ul>
        <br />
	   </td>
       </tr>
        <tr>
        <td>
		  <table width="100%" cellpadding="5" cellspacing="1">
              <tr>
			  <td>&nbsp;</td>
              <td align="right"><?php echo $pn->NavBar("option=faq&amp;catid=$catid&amp;Itemid=$Itemid", ''); ?></td>
              </tr>
			  <tr>
              <td colspan="2" class="dkcom_tableheader"><strong><?php echo _FAQ_QANDA; ?></strong>
			  </td>
              </tr>
     
	          <?php
				$color=0;
				foreach($rsa as $row) {
					$color++;?>
              <tr>
              <td valign="top" align="left" colspan="2" class="dkcom_tablerow<?php echo ($color%2)?1:2;?>"><a id="faq<?php echo $row['id'];?>"></a> <strong><?php echo $row['question']; ?></strong>
			  </td>
              </tr>
            
			  <tr>
              <td width="10%" align="center" class="dkcom_tablerow<?php echo ($color%2)?1:2;?>"><a href="#faq"><img src="<?php echo $d_subpath; ?>components/faq/images/up.png" border="0" alt="[up]" /></a></td>
              <td valign="top" align="left" class="dkcom_tablerow<?php echo ($color%2)?1:2;?>"><?php echo $row['answer']; ?>
			  </td>
              </tr>
			  
              <?php } ?>
            </table>
			
			
			</td>
        </tr>
        <?php
				}
				?>
        <?php }	?>
      </table>
    <?php
}

function new_question() {
	global $conn,$my,$access_sql,$d;
	
	$js = 'function val_faq()
	{
	var frm=document.faqform;
	if(';
	
	if ($my->gid==0) $js.='frm.faq_name.value=="" || ';
	$js .= 'frm.faq_catid.value=="" || frm.faq_question.value==""){
	alert ("'.js_enc(_FORM_NC).'");
	return false;
	}
	frm.submit();
	}';
	$d->add_raw_js($js); $js = null;
	?>
    <div class="dk_header">
      <h2><?php echo _FAQ_FAQH; ?></h2>
    </div>
    <form name="faqform" method="post" action="index.php?option=faq">
      <table width="100%" border="0" cellpadding="4" cellspacing="0" class="dk_content">
        <?php if ($my->gid==0) { ?>
        <tr>
          <td colspan="2">* <?php  echo _YOUR_NAME; ?>
            <br />
            <input name="faq_name" type="text" class="dk_inputbox"  value="" size="30" />
          </td>
        </tr>
        <tr>
          <td colspan="2">* <?php echo _EMAIL_PROMPT;?><br />
            <input name="faq_email" type="text" class="dk_inputbox" value="" size="30" />
          </td>
        </tr>
        <?php } ?>
        <tr>
          <td></td>
        </tr>
        <tr>
          <td colspan="2">* <?php echo _CAT; ?>:<br />
            <select  class="dk_inputbox" name="faq_catid">
              <option value=""><?php echo _SELECTCAT; ?></option>
              <?php
			$rsa=$conn->SelectArray('#__categories', 'id,name', " WHERE section='com_faq' $access_sql");
			foreach($rsa as $row)echo "<option value='".$row['id']."'>".$row['name']."</option>";
	?>
            </select>
          </td>
        </tr>
        <tr>
          <td colspan="2">* <?php echo(_FAQ_QUESTION);?><br />
	<input name="faq_question" type="text" class="dk_inputbox" value="" size="60" />
	</td>
        </tr>
        <tr>
          <td colspan="2"><input type="button" class="dk_button"  value="<?php echo(_FAQ_SUBMIT);?>" onclick="val_faq()" />
            <input type="reset" class="dk_button" />
            <input type="hidden" name="task" value="newfaq" />
          </td>
        </tr>
      </table>
    </form>
    <?php
}
?>