<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

global $$module['module'], $inst;

if(!isset($$module['module'])) {
  $$module['module']=true;
$inst = module_instance($module['instance'], 'polls');

	$path = mod_lang($my->lang, $module['module']);
	include_once $path;

function showpoll($pollid=0) {
	global $conn,$Itemid,$access_sql;
	$tabclass_arr=array('row1', 'row2');

	$tabcnt = 0;
	// randomly select a poll
	if(!$pollid) {
		$rsa = $conn->SelectArray('#__categories', 'id,name,ordering', " WHERE section='com_polls' $access_sql ORDER BY ordering ASC");
		if (isset($rsa[0])) {
			$pollid = mt_rand(0, count($rsa)-1);
			$rsar = $rsa[$pollid]; unset($rsa);
		} else $rsar = array();
	} else
		$rsar = $conn->SelectRow('#__categories', 'id,name,ordering', " WHERE id=$pollid AND section='com_polls' ".$access_sql);
	
	if (!count($rsar)) {
		global $my;
		$module = 'mod_polls';
		$path = mod_lang($my->lang, $module);
		include_once $path;
	
		echo _POLL_NO;
		return;
	}
	
	$pollid = $rsar['id'];
	global $inst;
?>
        <div class="polls_header"><?php echo $rsar['name']; ?></div>
		<form name="form2" method="post" action="<?php echo "index.php?option=polls".$inst; ?>">
        <table border="0" cellspacing="0" cellpadding="0">
        <?php
		$i = 0;
		$rsa = $conn->SelectArray('#__polls_data', 'id,pollid,polloption', " WHERE pollid=$pollid ORDER BY id");
		foreach($rsa as $row) {  ?>
          <tr>
            <td class="<?php echo $tabclass_arr[$tabcnt]; ?>">
              <input class="dk_radio" type="radio" id="<?php $id = 'poll'.$pollid.'_'.$i;echo $id; ?>" name="voteid" value="<?php echo $row['id'];?>" />
            </td>
            <td class="<?php echo $tabclass_arr[$tabcnt]; ?>">
              <label class="dk_label" for="<?php echo $id;	?>"><?php echo $row['polloption']; ?></label>
            </td>
          </tr>
        <?php
		if ($tabcnt == 1)
			$tabcnt = 0;
		else
			$tabcnt++;
		$i++;
		}
		?>
		</table>
        <div class="dk_aligncenter">
          <input type="submit" name="task_button" class="dk_button" value="<?php echo _BUTTON_VOTE; ?>" />&nbsp;&nbsp;
          <input type="button" class="dk_button" value="<?php echo _BUTTON_RESULTS; ?>" onclick="document.location.href='index.php?option=polls&amp;task=results&amp;pollid=<?php echo $pollid.$inst; ?>';" />
  	  <input type="hidden" name="pollid" value="<?php echo $pollid; ?>" />
	  <input type="hidden" name="task" value="vote" />
	  </div>
	</form>
  <?php
  }
}

showpoll((int)$params->get('pollid'));

?>