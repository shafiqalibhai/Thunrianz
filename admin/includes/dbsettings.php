<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## database settings script file
# @author legolas558
#
# form fields to configure the database

global $d_subpath;

function dbtest_form() {
	global $d_subpath; ?>
<form name="proxyform" action="<?php echo $d_subpath; ?>install/dbcheck.php" target="_blank" method="post">
<input name="cdb" type="hidden" />
<input name="cdbhost" type="hidden" />
<input name="cdbname" type="hidden" />
<input name="cdbusername" type="hidden" />
<input name="cdbpassword" type="hidden" />
<input name="cprefix" type="hidden" />
</form>
<?php }

require $d_root.'admin/includes/dbtest.php';

global $d;

$d->add_raw_css('
.db_avail0 {
	color: red;
	font-weight: normal;
}
.db_avail1 {
	color: red;
	font-weight: normal;
}
.db_avail2 {
	color: gray;
	font-weight:bold;
}
.db_avail3 {
	color: green;
	font-weight:bold;
}');

$d->add_raw_js('
function database_check() {
	var f1=document.forms[0];
	var f2=document.proxyform;
	f2.cdb.value=f1.cdb.value;
	f2.cdbhost.value=f1.cdbhost.value;
	f2.cdbname.value=f1.cdbname.value;
	f2.cdbusername.value=f1.cdbusername.value;
	f2.cdbpassword.value=f1.cdbpassword.value;
	f2.cprefix.value=f1.cprefix.value;
	f2.submit();
}');
?><tr>
      <td colspan="2" class="tip">
<p><?php echo _DBS_SUGGESTION; ?></p>
<p><?php echo _DBS_LEGEND; ?></p><ul style="color:black"><li><span style="color:green; font-weight:bold"><?php
	echo _DBS_GREEN_ITEMS ?></span> <?php echo _DBS_GREEN_ITEMS_DESC;
	?></li><li><span style="color:gray; font-weight:bold"><?php echo _DBS_GRAY_ITEMS;
	?></span> <?php echo sprintf(_DBS_GRAY_ITEMS_DESC,
		'<a href="'.create_context_help_url('Addon/adoDB_lite_drivers_pack').'" target="_blank">'._DBS_ADODB_PACKAGE.'</a>');
		?></li><li><span style="color:red"><?php echo _DBS_RED_ITEMS; ?></span> <?php
		echo sprintf(_DBS_RED_ITEMS_DESC, '<a href="'.create_context_help_url('Database').'" target="_blank">'._DBS_MODIFY_PHP.'</a>'); ?></li></ul>
	  </td>
    </tr>
    <tr>
      <td>* <?php echo _DBS_SYSTEM;?></td>
      <td><select id="cdb" name="cdb" onchange="return changedElem()">
	  <?php

			for($i=0;$i<count($databases);$i++) {
				$db =& $databases[$i];
				$class = ' class="db_avail'.$db[3].'"';
				echo '<option value="'.$db[0].'"';
				echo $class;
				if ($d__elect_db == $db[0]) { // we have an electable dbms and it is the current one
					echo ' selected="selected"';
				}
				echo '>'.$db[1].'</option>'."\r\n";
			}
			//unset($databases);
	  ?>
		  </select>
		  <script language="javascript" type="text/javascript">

function changedElem() {
	var par=document.getElementById('cdb');
	par.className = par.options[par.selectedIndex].className;
}

changedElem();

</script>
		  </td>
    </tr>
    <tr>
      <td>* <?php echo _DBS_DBNAME;?></td>
      <td><input name="cdbname" type="text" class="textboxgray" value="<?php echo (isset($d_dbname) ? $d_dbname : 'lcms'); ?>" size="50"/></td>
    </tr>
	<tr>
		<td colspan="2" class="tip"><?php echo _DBS_PREFIX_DESC; ?></td>
	</tr>
    <tr>
      <td>* <?php echo _DBS_PREFIX;?></td>
      <td><input name="cprefix" type="text" class="textboxgray" value="<?php
	  if (!isset($d_prefix)) echo 'ld';
	  else echo substr($d_prefix, 0, -1);?>" maxlength="10" size="5"/> _<i><?php echo _DBS_TABLENAME; ?></i></td>
    </tr>
    <tr>
      <td colspan="2" class="tip"><strong>* =</strong> <?php echo _DBS_NOT_MANDATORY;?>
	</td>
    </tr>
    <tr>
      <td><?php echo _DBS_HOST;?></td>
      <td><input name="cdbhost" type="text" class="textboxgray" value="<?php echo (isset($d_dbhost) ? $d_dbhost : 'localhost'); ?>"  size="50"/></td>
    </tr>
    <tr>
      <td><?php echo _DBS_USER;?></td>
      <td><input name="cdbusername" type="text" class="textboxgray" value="<?php echo (isset($d_dbusername) ? $d_dbusername : 'root');?>"  size="50"/></td>
    </tr>
    <tr>
      <td><?php echo _DBS_PASSWORD;?></td>
      <td><input name="cdbpassword" type="text" class="textboxgray" size="50" value="<?php echo $d_dbpassword; ?>"/></td>
    </tr>
	<?php if (isset($has_db)) { ?>
	<tr>
      <td colspan="2">
	  <label for="cdbmove"><input type="checkbox" name="cdbmove" id="cdbmove" checked="checked"/><?php echo _DBS_MOVE_DB; ?></label>
	</td>
	</tr>
<?php } ?>
	<tr><td colspan="2"><input type="button" value="<?php echo _ADMIN_TEST_DB; ?>" onclick="database_check()" />
	</td>
    </tr>