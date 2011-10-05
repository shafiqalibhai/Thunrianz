<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## User interface scripter
#
# allows the creation of simple user interfaces through PHP code calls
# should be refactored

define('_UPL_FILE', 1);
define('_UPL_URL', 2);
define('_UPL_DIR', 4);
define('_UPL_ALL', _UPL_FILE|_UPL_URL|_UPL_DIR);
define('_UPL_INSTALL', /*(function_exists('gzopen')?*/_UPL_FILE | _UPL_URL /*:0)*/ | _UPL_DIR);

function _finish_ui($row_num, $colspan, &$f_nav, &$c_nav) {
	if (!$row_num)
		echo '<tr><td align="center" colspan="'.$colspan.'">- <em>'.
			_UI_NO_ROWS.'</em> -</td></tr>';
	echo '</tbody></table>';
	
	if (strlen($f_nav)) {
		echo '<table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>';
		echo '<td>'.$f_nav.'</td>';
		echo '<td align="right">'.$c_nav.'</td>';
		echo '</tr></table>';
	}
}

function _js_assertion($cond, $msg, $label) {
	return "\tif ($cond) { alert(\"".
			js_enc($msg).'\n\n'.js_enc($label).'");return false;'."\n}\n";
}

function _create_select($name, $rows, $multiple=false, $extra='') {
//	$select_drop='<select name="'.$name.'" class="tf"'.($multiple?' multiple="multiple"':'').' '.$extra.'>'."\n";
	$items = array();
	$sel = array();
	foreach($rows as $row) {
		if (isset($items[$row['value']])) {
//			var_dump($rows);die;
			trigger_error('Value '.$row['value'].' already set in array');
		}
		$items[$row['value']] = $row['name'];
		if (isset($row['selected']))
			$sel[] = $row['value'];
/*		if (isset($row['selected']))
			$sel=' selected="selected"'; else $sel='';
		$select_drop.='<option value="'.$row['value'].'"'.$sel.'>'.
					$row['name'].'</option>'."\n"; */
	
	}
//	return $select_drop.'</select>'."\n";
	return _create_select_raw($name, $items, $sel, $multiple, $extra);
}

function _create_select_raw($name, $items, $selected = array(), $multiple = false, $extra = '') {
	if (strlen($extra)) $extra = ' '.$extra;
	$select_drop='<select id="'.$name.'" name="'.$name.'" class="tf"'.$extra.($multiple?' multiple="multiple"':'').'>'."\n";
	foreach($items as $val => $iname) {
		if (in_array($val, $selected))
			$sel=' selected="selected" style="color: grey"';else $sel='';
		$select_drop.='<option value="'.$val.'"'.$sel.'>'.
					$iname.'</option>'."\n";
	}
	return $select_drop.'</select>'."\n";
}

## class which provides client-side validation information
class	ScriptedUI_Validation {
	var $not_empty;
	var $max = null;
	var $min = null;
	var $max_value = null;
	var $min_value = null;
	var $email = false;
	var $digits = false;
	var $required;
	
	function ScriptedUI_Validation($req = true) {
		$this->not_empty = $this->required = $req;
	}
}

class ScriptedUI {
	var $gui_array=array();
	var $form_type="simple";
	var $form_url='';
	var $form_name;
	var $order=1;
	var $enable_filter;
	var $filter_ord = 'ASC';
	var $filter_str = '';
	var $_editors = array();
	//FIXME: some components should set this to false
	var $has_ordering;
	var $has_owner;
	var $has_access;
	//CUSTOM
	var $has_frontpage;
	var $admin_access;

function ScriptedUI($admin_access = false) {

	global $d, $d_atemplate;
	$d->add_css('admin/templates/'.$d_atemplate.'/css/ui.style.css');
	$d->add_js('admin/includes/js/anthill.js');

	$this->has_ordering = false;
	$this->has_owner = false;
	$this->has_access = false;
	$this->has_frontpage = false;
	$this->admin_access = $admin_access;
	
	$task = $GLOBALS['option'];
	if (!isset($task))
		$task = '';
	
	//FIXME: maybe no more working with new toolbar
	$reset =  (in_raw('task', $_POST) == 'disable_filter');
	
	// retrieve the view filter
	global $d_view_filter, $my;

	if ($d_view_filter) {
		global $conn;
		$row = $conn->SelectRow('#__view_filter', 'id,params', ' WHERE userid='.$my->id.' AND component=\''.
			sql_encode($GLOBALS['com_option']).'\' AND task=\''.sql_encode($task)."'");
		if (empty($row)) {
			$this->filter = new param_class();
			$row_id = null;
		} else {
			if ($reset)
				$this->filter = new param_class();
			else
				$this->filter = new param_class($row['params']);
			$row_id = $row['id'];
		}
		$orig_params = $this->filter->params;
	} else {
		$this->filter = new param_class();
		$row_id = null;
	}
	
	//FIXME: filter should work much better (?) - enabled ordering filter for publisher/manager
	$this->enable_filter = ($my->gid>=3);
	$this->filter_ord = in_sql('filter_ord', $_REQUEST, $this->filter->get('ord', 'ASC'));
	$this->filter->params['ord'] = $this->filter_ord;
	$this->table_page = in_num('table_page', $_REQUEST, 1);
	$this->insert_top = $this->filter->get('insert_top', 0);
	//FIXME
	//$this->table_page = $this->filter->get('table_page', 1);
	$this->filter->params['table_page'] = $this->table_page;
	if ($this->enable_filter && !$reset) {
		$this->filter_var = in_sql('filter_var', $_REQUEST, $this->filter->get('var'));
		$this->filter_str = in_sql('filter_str', $_REQUEST, $this->filter->get('str', ''));
		$this->filter_type = in_sql('filter_type', $_REQUEST, $this->filter->get('type'));
		$this->filter->params['var'] = $this->filter_var;
		$this->filter->params['str'] = $this->filter_str;
		$this->filter->params['type'] = $this->filter_type;
	} else
		$this->filter->params = array('table_page' => $this->table_page, 'filter_ord' => $this->filter_ord,
			'insert_top' => 0);
	if ($d_view_filter) {
		if (!isset($row_id)) {
			// only start writing something when strictly necessary
			if (($this->table_page!=1) || ($this->filter_ord != 'ASC') || strlen($this->filter_str)) {
			$conn->Insert('#__view_filter', '(userid,component,task,params)',
					$my->id.',\''.sql_encode($GLOBALS['com_option']).'\',\''.sql_encode($task).'\', '.
					'\''.sql_encode($this->filter->save())."'");
			}
		} else {
			if ($reset || ($orig_params != $this->filter->params))
				$conn->Update('#__view_filter', 'params=\''.sql_encode($this->filter->save())."'", ' WHERE id='.$row_id);
		}
	}
}

function put_toolbar() {
	global $toolbar;
//	if (isset($toolbar))
	echo '<div class="toolbar" align="center">';
	$toolbar->generate();
	echo '</div>';
}

function &add($tag, $name='',$desc='',$value='', $validation=null,$extra='') {
	global $showhtmlarea;
	$t_array['tag']=$tag;
	$t_array['name']=$name;
	$t_array['desc']=$desc;
	$t_array['value']=$value;
	$t_array['extra']=$extra;
	$t_array['validation']=$validation;

	$this->gui_array[] =& $t_array;
	/* hook function */
	if($t_array['tag']=='form') {
		// add the task, even if form name is not 'adminform'
		// this field is not used by direct toolbar actions
		$this->add('hidden','task', '', $validation);
		global $d;
		$d->add_raw_js('var lcms_data_form=\''.js_encode($t_array['name'])."';");
		$this->form_name = $t_array['name'];
		$this->form_url=$value;
	}
	if ($t_array['tag']=='file') $this->form_type='file';
	if ($t_array['tag']=='htmlarea' /*&& !$showhtmlarea*/) {
		if (!$showhtmlarea) {
			global $d;
			$d->SetupAdvancedEditor();
			$showhtmlarea=true;
		}
		$this->_editors[] = $t_array['name'];
	}
	
	return $t_array;
}

function row($col1,$col2='',$class='',$extra='') {
	if(!strlen($col2))
		return "<tr><td colspan='2' class=\"$class\" $extra>$col1</td></tr>\n";
	else
		return "<tr><td class=\"$class\" valign=\"top\" nowrap=\"nowrap\">$col1</td><td class=\"$class\" $extra>$col2</td></tr>\n";
}

// by legolas558
function tab_combo_header($name, $selected = '') {
	$ret_str= '<select id="'.$name.'_combo" name="'.$name.'_combo" onchange="dhtml.cycleTab(\'page\'+this.value)">';
	$i=1;
	foreach($this->gui_array as $row) {
		if(isset($row['value']) && $row['value']==$name) {
			$ret_str.= '<option value="'.$i.'"';
			if ($i==$selected)
				$ret_str .= ' selected="selected"';
			$ret_str .= '>'.$row['name']."</option>\n";
			++$i;
		}
	}
	if ($i == 1)
		return '';
	$ret_str.= "</select>\n";
	return $ret_str;
}

function tab_header($name) {
	$ret_str= '';
	$tab=1;
	foreach($this->gui_array as $row) {
		if(isset($row['value']) && $row['value']===$name) {
			$ret_str.= '<td id="tab'.($tab).'" class="offtab" onclick="dhtml.cycleTab(\'tab'.($tab).'\')">'.
						$row['name']."</td>\n";
			++$tab;
		}
	}
	if (strlen($ret_str))
		return "<table border='0' cellpadding='0' cellspacing='2'><tr>\n".$ret_str."</tr></table>\n";
	return '';
}

function get_order_field($id,$row_id,$order) {
	$order_select="<input type='text' id='oid$id' name='oid$id' value='$order' class=\"tf\" size='2' onchange=\"listItemCheck('cb$row_id');\" />";
	// a wrongfully closed /select was here
	$order_select.="<input type='hidden' id='ooid$id' name='ooid$id' value='$order' />\n\n";
	return $order_select;
}

// by legolas558
// client-side javascript checks
function cs_check(&$row) {
	$validation = $row['validation'];
	// debug code
	if (!is_object($validation)) {
		echo '<pre>';var_dump($row);echo '</pre>';
		trigger_error('$validation is not an object');
	}

//	if ($validation->not_empty) {
		if (!strlen($row['name'])) {
			trigger_error("Invalid form name: ".xhtml_safe(var_export($row, true)));
		}
		echo "\tfield_value=frm.".$row['name'].".value;\n";
		if ($validation->not_empty) {
			echo _js_assertion('!field_value.length', _IFC_ERR, $row['desc']);
			$nes = $nese = '';
		} else {
			// check the field value if there is some content
			$nes = '(field_value.length!=0) && (';
			$nese = ')';
		}
		if ($validation->digits) {
			echo _js_assertion($nes.'isNaN(field_value)'.$nese, _IFC_DIGIT_ERR, $row['desc']);
			if (isset($validation->max_value))
				echo _js_assertion($nes.'field_value>'.$validation->max_value.$nese,
						"Maximum allowed value is ".$validation->max_value,
						$row['desc']);
			if (isset($validation->min_value))
				echo _js_assertion($nes.'field_value<'.$validation->min_value.$nese,
						"Minimum allowed value is ".$validation->min_value,
						$row['desc']);
		}
		if (isset($validation->max))
			echo _js_assertion($nes.'field_value.length>'.$validation->max.$nese,
					"Maximum allowed length is ".$validation->max,
					$row['desc']);
		if (isset($validation->min))
			echo _js_assertion($nes.'field_value.length<'.$validation->min.$nese,
					"Minimum allowed length is ".$validation->min,
					$row['desc']);
	
	return;
// evaluate through a client-side javascript expression, variable name is field_value (stands for FIeld VALue) in javascript
/*
	if ($row['cs'][0]=='§') {
		switch ($row['cs']{1}) {
		case '0': {
			echo 'if (!frm.'.$row['name'].'.checked){alert("You have to check before continuing\n\n'.js_enc(html_to_text($row['desc'])).'"); return false;}';
			return;
		}
		case 'k':
		$max = (int)substr($row['cs'], 3); ?>
		var delta=document.getElementById('<?php echo $row['name']; ?>').value.length-<?php echo $max;?>;
		if (delta>0) {
			alert("<?php echo js_enc($row['desc']); ?>\n\nExceeded "+delta.toString()+' characters');
			return false;
		} <?php break;
		default:
		$set=array((int)$row['cs']{1},(int)$row['cs']{3}); ?>
		fnd=0;
		for (i=0;i<<?php echo $set[0];?>;i++){
			obj=document.getElementById('<?php echo $row['name'].'_';?>'+i.toString());
			if (obj.checked) {
				fnd++;
			<?php if ($set[1]==1) echo 'break;'; ?>
			}
		}
		if (fnd<<?php echo $set[1];?>) { alert("<?php
		echo js_enc($row['desc']).'\n\n'.
			 sprintf(_PLEASE_SELECT, $set[1]);?>");
			return false;}
			<?php
		}
	} else {
		echo "\tfield_value=frm.".$row['name'].".value;\n";
		echo "\tif (".$row['cs'].') { alert("'.js_enc(_IFC_ERR).'\n\n'.js_enc($row['desc']).'");return false;'." }\n";
	}
*/
}

function client_side_checks($form_name) {
	echo "\nvar frm=document.getElementById('$form_name');\n";
	foreach($this->gui_array as $row) {
		if (isset($row['validation']))
			$this->cs_check($row);
	}
}

function _save_check($btn) {
	global $d, $showhtmlarea;
	echo "\tif ( pressbutton == '$btn' ) {\n\t\t";
	echo $d->EditorSaveJSMultiple($this->_editors);
	$this->client_side_checks($this->form_name);
	echo "\n\t}";
}

function _sel_check($btn) {
	echo "\tif ( pressbutton == '$btn' ) { \n ";
	echo "\t\tif( frm.boxchecked.value==0 ){ \nalert('".js_enc(_IFC_LIST_ERR)."');return; }\n";
	echo "\n}";
}

function _confirm($btn, $action) {
	echo "\tif ( pressbutton == '$btn' ) { \n ";
	echo "\t\tif( frm.boxchecked.value==0 ){ \nalert('".js_enc(_IFC_LIST_ERR)."');return; }\n";
	echo "\telse if (!confirm('".
		js_enc(sprintf(_IFC_CONFIRM,
			constant('_IFC_OP_'.raw_strtoupper($btn))
		))."')) return; \n  }";
}

function generatescript() {
	global $toolbar;
	
	ob_start();

	echo "function ui_lcms_st(pressbutton){\nvar frm=document.getElementById(lcms_data_form);\n";

	if (isset($toolbar)) {
		//FIXME: dirty trick for com_language
		if($toolbar->is_button('clone'))
			$this->_save_check('clone');

	//	if($toolbar->is_button('upload'))		$this->_save_check('upload');

		if($toolbar->is_button('save'))
			$this->_save_check('save');
		
		if($toolbar->is_button('send'))
			$this->_save_check('send');

		if($toolbar->is_button('create'))
			$this->_save_check('create');

		/* In case of publish check for submit */
		$a = array('publish', 'unpublish', 'edit');
		foreach ($a as $btn)
			if($toolbar->is_button($btn))
				$this->_sel_check($btn);

		$a = array('delete', 'uninstall', 'reorder');
		$m = array(_DELETE, _UNINSTALL, _REORDER);
		$c = count($a);

		// confirmation javascript for each button
		for($i=0;$i<$c;$i++) {
			if($toolbar->is_button($a[$i]))
				$this->_confirm($a[$i],$m[$i]);
		}

		if($toolbar->is_button('cancel') ) {
			echo "\tif ( pressbutton == 'cancel' ) {\n";
			echo "\t\tdocument.location.href=frm.action; return;}\n";
		}
	}

	echo "\n\tlcms_st(pressbutton);\n}\n";

	$s = ob_get_clean();
	global $d;
	$d->add_raw_js($s);
}

function Generate() {
	$this->generatescript();
	$this->generate_simple();
}

var $in_table = false;
var $_has_files = false;

function generate_simple() {
	global $my,$task,$d_root;
	$com_header="";
	$tab_div=false;
	/* end the table we started */
	$_gclen = count($this->gui_array);
	for($i=0;$i<$_gclen;$i++) {
	
	echo "\n";
	
	$row=$this->gui_array[$i];

	// derty!
	extract($row);
	// replace newlines with line breaks
	$desc = text_to_html($desc);

	if (!isset($validation)) {
		$req_star='';
		$required=false;
	} else {
		if (!is_object($validation)) {
			echo '<pre>';var_dump($row);echo '</pre>';
			trigger_error('$validation is not an object');
		}
		
		if ($validation->required)
			$req_star = '<span style="color:red">*</span>';
		else $req_star='';
		
		// add description for minimum and maximum length
		if (isset($validation->min))
			$desc.= sprintf(' (min %d characters', $validation->min);
		if (isset($validation->max)) {
			if (isset($validation->min))
				$desc .= ', ';
			else
				$desc .= ' (';
			$desc .= sprintf('max %d characters', $validation->max);
		}
		if (isset($validation->min) || (isset($validation->max)))
			$desc .= ')';
	}
	
	switch($row['tag']) {
        case 'textfield':
		//DEBUG
		if (is_object($value)) {
			echo '<pre>';var_dump($row);echo '</pre>';die;
		}
		if (isset($validation) && isset($validation->max))
			$extra .= ' maxlength="'.$validation->max.'"';
		echo $this->row($req_star.' '.$desc, '<input type="text" id="'.$name.'" name="'.$name.'" value="'.$value.'" class="tf" '.$extra.' size="40" />');
		break;
        case 'file':
		$this->_has_files = true;
		//TODO: should use class 'tf' instead of class dk_inputbox
		echo $this->row($req_star.' '.$desc, file_input_field($name)
		//'<input type="file" name="'.$name.'" value="'.$value.'" class="tf" size="40" />'
		);break ;
        case 'text':
	        echo $this->row($req_star.' '.$desc, $value);
	        break ;
        case 'hidden':
		// output an hidden field
		if ($this->in_table)
			echo $this->row("<input type=\"hidden\" id=\"$name\" name=\"$name\" value=\"$value\" />");
		else
			echo "<input type=\"hidden\" id=\"$name\" name=\"$name\" value=\"$value\" />";
		break;
        case 'password': echo $this->row("$req_star $desc","<input type=\"password\" id='$name' name='$name' value='$value' class=\"tf\" size='40' $extra />");break ;
        case 'textarea':
			$area_str="<textarea ";
			if (isset($validation)) {
				if (isset($validation->max))
					$area_str.=' maxlength="'.$validation->max.'" ';
			}
//			if (!empty($row['cs']) and $row['cs'][1]=='k') $area_str.='id="'.$name.'" ';
			$area_str.="name='$name' cols='38' rows='5' class=\"tf\" $extra>$value</textarea>\n";
			echo $this->row("$req_star $desc",$area_str);
		break;
        case 'textarea_big':
			$area_str="<textarea name='$name' cols='80' rows='20' class=\"tf\" $extra>$value</textarea>\n";
			echo $this->row($req_star.' '.$desc, $area_str);
		break;
        case 'htmlarea':
		global $d;
			$area_str = $d->AdvancedEditor($name,$value,20,80,"class=\"tf\"");
			echo $this->row("$req_star $desc",$area_str);
			break;
        case 'boolean':	// edited by legolas558
			$y_s='';$n_s='';
			if (!$required) {
				if ($value==1)
					$y_s='selected="selected"';
				else $n_s='selected="selected"';
					$bool_str='';
			} else
				$bool_str='<option value=\'none\' selected>'._PLEASE_SELECT.'</option>';
			$bool_str = "<select name='$name' class=\"tf\">".$bool_str.
			"<option value='1' $y_s>"._YES."</option>\n<option value='0' $n_s>"._NO."</option>\n</select>";
			echo $this->row($req_star.' '.$desc, $bool_str);
		break;
		case 'radio':
                                $radio_arr='';
								$c=count($value);
								for($a=0;$a<$c;$a++){
									$sel='';
									if(isset($row['selected']))$sel='checked="checked"';
									$radio_arr.='<input id="'.$name.'_'.$a.'" type="radio"  name="'.$name.'" value="'.$value[$a]['value'].'" '.$sel.' /><label for="'.$name.'_'.$a.'">'.$value[$a]['name'].'</label>&nbsp;&nbsp;'."\n";
                                }
                                echo $this->row("$req_star $desc",$radio_arr);
                                break;
				case 'select':
					$select_drop = _create_select($name, $value, false, $extra);
					echo $this->row("$req_star $desc",$select_drop);
                                break;
				
		case 'access':
			echo $this->row("$req_star $desc", access_select($name, $value, false, false, $extra));
			break;
		case 'admin_access':
			echo $this->row("$req_star $desc", access_select($name, $value, true, false, $extra));
			break;
		case 'listm':
                case 'list':
			$multiple = ($row['tag']=='listm');
			// $extra -> size=""
			$select_drop = _create_select($name, $value, $multiple, $extra);
			echo $this->row("$req_star $desc",$select_drop);
			break;
		case 'list_image':
			include_once $d_root.'includes/dil.php';
			global $d_subpath;
			echo $this->row($req_star.' '.$desc, dynamic_image_list($name, $value,$d_subpath.'images/common/noimage.png', $extra ));
		break;
        case 'buttons':
			$buttons='';
			foreach($value as $row) {
				$jsa = $row['onclick'];
				$buttons.='<input type="button" id="'.$row['name'].'" name="'.$row['name'].'" value="'.$row['name'].'" onclick="'.$jsa.'" class = \'tf\' />&nbsp;&nbsp;';
			}
			echo $this->row($desc,$buttons);
		break;
        case 'date':
			global $date_js;
			if (!isset($date_js)) {
				//include js files
				global $d;
				$d->add_css('includes/js/calendar/calendar-system.css');
				$d->add_js('includes/js/calendar/calendar.js');
				$d->add_js('lang/'.$my->lang.'/js/calendar.js');
				$d->add_body_js('includes/js/dk_calendar.js');
				$date_js=true;
			}
			$date_val = lc_strftime(_DATE_FORMAT_EXTENDED, $value);
			$date_field='<input class=\'tf\' type="text" id="'.$name.'" name="'.$name.'" size="25" value="'.$date_val.'" />';
			$date_field.='<input id=\'reset\' name=\'reset\' type="reset" onclick="return showCalendar(\''.$name.'\', \''._DATE_FORMAT_EXTENDED.'\');" value="...">';
			echo $this->row("$req_star $desc",$date_field);
			break;
        case 'form':
			// add the table_page to the form url
			if (strlen($value)) { //FIXME: for legacy, should be deprecated
				if (isset($this->table_page) && ($this->table_page!=1))
					// URL-encoding not necessary here
					$value .= '&table_page='.$this->table_page;
			}
			if ($this->form_type == 'file')
				$extra = " enctype='multipart/form-data'";
			else if($this->form_type=='simple' ) $extra = '';
			$value = xhtml_safe($value);
			echo "<form id='$name' name='$name' method='post' action='$value' $extra>";
			if ($this->_has_files)
				echo "<input id='MAX_FILE_SIZE' name='MAX_FILE_SIZE' type=\"hidden\" value='".return_bytes($GLOBALS['d_max_upload_size'])."' />";

			// put the toolbar in the start area
			echo '<div class="toolbar-header">';
				$this->put_toolbar();
			echo '</div>';

			// output surrounding table
			echo "<table border='0' cellpadding='0' cellspacing='0' width='100%' align='center'>";
//			echo "<tr><td width='100%' valign='top'>";
			//L: one table less please
//			echo "<table border='0' cellpadding='0' cellspacing='0' width='100%' align='center'>";
			$this->in_table = true;
			break;
        case 'end_form':
		// add the mass operations right before the closing form tag
		$this->mass_operations();
		//L: one table less please
//		echo "</table>";
//		echo "</td><td valign='top'></td></tr>";
		echo '</table>';
		// put the toolbar in the same form
		echo '<br />';
		echo '<div class="toolbar-footer" style="text-align: left">';
			$this->put_toolbar();
		echo '</div>';

		echo '</form>';
		$this->in_table = false;
        break;
        case 'table':
	//L: is this used?
			echo "<table $extra border='0' cellpadding='0' cellspacing='0'>";
			break;
        case 'end_table':
			echo "</table>";
			break;
        case 'spacer':
		echo $this->row('&nbsp;','','');
		break;
        case 'com_header':
			$com_header=$name;
			global $pathway;
			if (!$pathway->Count())
				$pathway->add($name);
			echo $this->row($name,'','header1');
			break;
        case 'com_info': //TODO: check header2 style
			echo $this->row($name,'','header2');
			break;
		case 'submit':
			echo $this->row('<input type="submit" value="'._SUBMIT.'" />','',"' align='center");
			break;
        /* tab based */
        case 'tab_link':
			echo $this->row($this->tab_header($name),'','');
			$$name=1;
			break;
        case 'tab_combo_link':
			echo $this->row($this->tab_combo_header($name, $value),'','');
//				global $d;
//				echo $d->script('dhtml.cycleTab("page'.$selected.'");');
			$$name=1;
			break;
        case 'tab_selc':$cookie=true;
        case 'tab_sel':
			global $d;
			echo $d->script("dhtml.cycleTab".(isset($cookie)?'C':"")."('tab$value')",'','');
		break;
        case 'tab_combo_sel':
			global $d;
			echo $d->script("dhtml.cycleTab".(isset($cookie)?'C':"")."('page$value')",'','');
		break;
        case 'tab_head':
			echo "<tr><td colspan=\"2\">";
			break;
        case 'tab_tail':
			echo "</td></tr>";break;
        case 'tab':
        echo '<div id="page'.$$value.'" class="pagetext">
                  <table width="100%" border="0" cellpadding="5" cellspacing="2" >
          <tr><td class="tabtitle">'.$desc.'</td></tr>
                  <tr><td class="tabbody">
                  <table width="90%" border="0" align=\'center\' cellpadding="2" cellspacing="0">
                  <tr><td width="200">&nbsp;</td><td>&nbsp;</td></tr>';
				$$value++;
                  $this->in_table = $tab_div=true;
                break;
        case 'tab_simple':
			echo '<table width="100%" border="0" cellpadding="5" cellspacing="2" >
          <tr><td class="tabtitle">'.$desc.'&nbsp;</td></tr><tr>
          <td class="tabbody">
                  <table width="90%" border="0" align="center" cellpadding="2" cellspacing="0">
                   <tr><td width="200">&nbsp;</td><td>&nbsp;</td></tr>';
		   $this->in_table = true;
		break;
        case 'tab_end':
			echo "</table></td></tr></table>";
			$this->in_table = false;
			if($tab_div) {
				echo "</div>";
				$tab_div=false;
			}
		break;
        case 'html':
		echo $value;
	break;
	case 'instancem':
	case 'instance':
		// about $value: first parameter contains the values, second parameter contains the component
		$multiple = ($row['tag']=='instancem');
		global $conn;
		// used to cache menus availability
		$menucache = array();
		// the items to build the <select />
		$items = array();
		// split the source array
		$component = $value[1];
		// default element
		$e = array('value' => 0, 'name' => isset($component) ? _ADMIN_DEFAULT_INSTANCE:_ADMIN_ALL_INSTANCES);
		$value = $value[0];
		if ((!$multiple && !$value) || !$value)
			$e['selected'] = true;
		$items[] = $e;
		// get the component id for this group
		if (isset($component)) {
			$row = $conn->SelectRow('#__components', 'id', ' WHERE option_link=\'com_'.$component.'\'');
			$component = ' AND componentid='.$row['id'];
		} else $component='';
		// get all the menu items which are suitable
		$rsa = $conn->SelectArray('#__menu', 'id,menutype,name', ' WHERE access<='.$my->gid.
		// URLs have no Itemid, so they cannot be used here (content items, ci, are OK)
		$component.' AND link_type<>\'url\' ORDER BY menutype,ordering');
/*
	//$rsam=$conn->GetArray("SELECT id,name,parent,ordering FROM #__menu WHERE parent=0 AND menutype='mainmenu' ORDER BY ordering ASC");
	
	$rsam=$conn->GetArray("SELECT id,name,parent,ordering FROM #__menu WHERE parent=0 ORDER BY ordering ASC");
	$parent[]=array("name"=>_MODULES_ALL,"value"=>"0");
	foreach($rsam as $rowm)$parent[]=array("name"=>$rowm['name'],"value"=>$rowm['id']);
	if($rsar['showon']=="")$parent=select($parent,0);
	else {
		$show_arr=explode("_",$rsar['showon']);
		$sel_link=false;
		foreach($show_arr as $enable) {
			if($enable=="") continue;
			else $sel_link[]=$enable;
		}
		$parent=select($parent,$sel_link);	
	}
*/

		foreach($rsa as $row) {
			// process the menu category
			$mc = $row['menutype'];
			if (!isset($menucache[$mc])) {
				$crow = $conn->SelectRow('#__categories', 'id',
						' WHERE section=\'com_menu\' AND name=\''.sql_encode($mc).'\' AND access<='.$my->gid);
				$menucache[$mc] = (count($crow)!=0);
			}
			// this category is not available
			if (!$menucache[$mc])
				continue;
			// reset the array
			$e = array();
			$e['value'] = $row['id'];
			$e['name'] = $mc.' &gt; '.$row['name'];
			if ($value && in_array($row['id'], $value))
				$e['selected'] = true;
			// add the menu item
			$items[] = $e;
		}
//		var_dump($menucache);die;

		$selbox = _create_select($name, $items, $multiple);
		echo $this->row($desc, $selbox, '', $extra);
		break;
		case 'data_table_arr' :
			// DO NOT use include_once as there can be multiple data_table_arr
			include $d_root.'admin/classes/ui.dta.php';
		break;
		case 'insert_where':
			// insert a spacer
			echo $this->row('&nbsp;','','');
			//TODO: offer customization capabilities for insert order id
			$where = array(
				array('name' => _MODULES_POS_TOP, 'value' => '1'),
				array('name' => _MODULES_POS_BOTTOM, 'value' => '0')
			);
			$where = select($where, $this->insert_top ? 1 : 0);
			$selbox = _create_select('insert_top', $where);
			echo $this->row(_INSERT_POSITION, $selbox, '', $extra);
		break;
		default:
			trigger_error($row['tag'].' is not a valid ScriptedUI element');
			break;
	}
}
}

// added by legolas558
function computexmlparams($file) {
	if (!is_readable($file)) return 0;
	$xmlDoc = new AnyXML();
	$xmlDoc->fromString(file_get_contents($file));
	$xmlDoc = $xmlDoc->getElementByPath('components/component');
	$xml_params = $xmlDoc->getElementByPath('params');
	if (!isset($xml_params))
		return 0;
	return count($xml_params->getAllChildren());
}

function &addxmlparams($file,$params,$default,$title, $path) {
	global $d;
	$sparams=new param_class($params);

	function return_defaults(&$me, &$sparams, $title) {
		$me->add('html','','','<tr><td colspan="2"><br /><br />');
		$me->add('tab_simple',"",$title);
		$me->add('textfield','param_custom_class',_CUSTOM_CLASS,$sparams->get('custom_class',''));
		$me->add('tab_end');
		$me->add('html', '', '', '</td></tr>');
	}

	if(!is_file($file)) {
		if($default)
			return_defaults($this, $sparams,$title);
		$n = null;
		return $n;
	}
	
	$xmlDoc = new AnyXML();
	$xmlDoc->fromString(file_get_contents($file));
	$xmlDoc = $xmlDoc->getElementByPath($path);
	
	//TODO: load i18n files here
	
	$xml_params = $xmlDoc->getElementByPath('params');
	if (!isset($xml_params)) {
		if($default)
			return_defaults($this,$sparams,$title);
		return $xmlDoc;
	}
	// process any additional labels sharing attribute
	$dklangshare = $xml_params->attributes('dklangshare');
	if (isset($dklangshare)) {
		$pf = substr($dklangshare, 0, 3);
		global $my, $d_root;
		$lang_inc = $d_root.'lang/'.$my->lang.'/';
		switch ($pf) {
			case 'com':
				$lang_inc .= 'admin/components/';
				$dklangshare = substr($dklangshare, 4);
			break;
			case 'mod':
				$lang_inc .= 'modules/';
			break;
			case 'dra':
				$lang_inc .= 'drabots/';
			break;
		}
		require_once $lang_inc.$dklangshare.'.php';
	}
	$params = $xml_params->getAllChildren();
	if (!count($params)) {
		if($default)
			return_defaults($this,$sparams,$title);
		return $xmlDoc;
	}
	
	$this->add('html',"","",'<tr><td colspan="2"><br /><br />');
	$this->add('tab_simple',"",$title);
	
	$str_v = new ScriptedUI_Validation();
//	$str_v->not_empty = true;
	$int_v = new ScriptedUI_Validation();
	$int_v->digits = true;
//	$int_v->min = 1;

	foreach($params as $param) {
		$options=array();
		$name = $param->attributes('name');
		if ($param->attributes('type')!='spacer') { //L: debug code
			if (!defined($param->attributes('label'))) {
				trigger_error('Undefined constant "'.$param->attributes('label').'" for parameter "'.$name.'"');
			}
		}
		
		$desc = $param->attributes('description');
		if (isset($desc)) {
			$desc = constant($desc);
			$this->add('text', '', $desc);
		}
		$v = null;
		$param_type = $param->attributes('type');
		switch ($param_type) {
			case 'integer':
				$v = $int_v;
				$min = $param->attributes('min');
				if (!isset($min) || !lcms_ctype_digit($min))
					$v->min_value = null;
				else $v->min_value = (int)$min;
				$max = $param->attributes('max');
				if (!isset($max) || !lcms_ctype_digit($max))
					$v->max_value = null;
				else $v->max_value = (int)$max;
				$this->add('textfield', 'param_'.$name,
						constant($param->attributes('label')),
						$sparams->get($name,$param->attributes('default')), $v);
				break;
			case 'text':
				if ($param->attributes('required') == 1)
					$v = $str_v;
				$this->add('textfield', 'param_'.$name,
						constant($param->attributes('label')),
						$sparams->get($name,$param->attributes('default')), $v);
				break;
			case 'password': //TODO: implement password input field
				if ($param->attributes('required') == 1)
					$v = $str_v;
				$this->add('password', 'param_'.$name,
						constant($param->attributes('label')),
						$sparams->get($name,$param->attributes('default')), $v);
				break;
			case 'textarea':
				$this->add('textarea','param_'.$name,constant($param->attributes('label')),$sparams->get($name,$param->attributes('default')));
				break;
//			case 'bool':	// deprecated
			case 'boolean':
				$this->add('boolean','param_'.$name,constant($param->attributes('label')),$sparams->get($name,$param->attributes('default')));
				break;
			case 'access':
				$this->add('access', 'param_'.$name,constant($param->attributes('label')),$sparams->get($name,$param->attributes('default')));
				break;
			case 'radio':
				$cparams = $param->getAllChildren();
				foreach($cparams as $cparam) {
			        $options[]=array('name' => constant($cparam->getValue()), 'value' => $cparam->attributes('value'));
		        }
				$options=select($options,$sparams->get($name,$param->attributes('default')));
				$this->add('radio','param_'.$name,constant($param->attributes('label')),$options);
				break;
			case 'list':
				$cparams = $param->getAllChildren();
				foreach($cparams as $cparam) {
					$options[]=array('name' => constant($cparam->getValue()), 'value' => $cparam->attributes('value'));
				}
				$options=select($options,$sparams->get($name,$param->attributes('default')));
				$this->add('select','param_'.$name,constant($param->attributes('label')),$options);
				break;
			case 'spacer':
				$this->add('spacer');
			break;
			case 'category':
				global $conn, $access_acl, $access_sql;
				$rsa=$conn->GetArray("SELECT id,title FROM #__sections WHERE ".$access_acl);
				$options = array();
				foreach($rsa as $row) {
					$rows = $conn->GetArray('SELECT id,name FROM #__categories WHERE section='.$row['id'].' '.$access_sql);
					foreach($rows as $catrow) {
						$options[]=array('name' => $row['title'].' &gt; '.$catrow['name'], 'value' => $catrow['id']);
					}
					
				}
				$options=select($options,$sparams->get($name,$param->attributes('default')));
				$this->add('select','param_'.$name,constant($param->attributes('label')),$options);
			break;
			case 'section':
				global $conn, $access_acl, $access_sql;
				$rsa=$conn->GetArray("SELECT id,title FROM #__sections WHERE ".$access_acl);
				$options = array(
					array('name' => _NONE, 'value' => 0)
				);
				foreach($rsa as $row) {
					$options[]=array('name' => $row['title'], 'value' => $row['id']);
				}
				$options=select($options,$sparams->get($name,$param->attributes('default')));
				$this->add('select','param_'.$name,constant($param->attributes('label')),$options);
			break;
			case 'instance':
				$this->add('instance', 'param_'.$name, constant($param->attributes('label')),
					array(false, $param->attributes('component')));
			break;
			default:
				trigger_error('Unrecognized UI XML item: '.$param->attributes('type'));
		}

	}
		$this->add('tab_end');
		$this->add('html', '', '', '</td></tr>');
		return $xmlDoc;
	}
	
	## Create an interface for multiple source selection (file upload, remote url, local directory)
	# $msg contains the labels for each section
	# @author legolas558
	function dtabs_interface($flags, $msg, $valid_ext = null, $type = 'both', $web_path='http://', $dir_path='', $dtab = 1) {
		
		$this->add("tab_link","dtab");
		$this->add("tab_head");
		
		if ($flags & _UPL_FILE) {
				$this->add("tab",_FILE_UPLOAD,_FILE_UPLOAD_DESC,"dtab");
	            $this->add('file', "package_file", $msg[0]);
				$this->add('tab_end');
		}
		if ($flags & _UPL_URL) {
				$this->add("tab",_REMOTE_URL,_REMOTE_URL_DESC,"dtab");
	            $this->add('textfield', "package_url",$msg[1], $web_path);
				$this->add('tab_end');
		}
		if ($flags & _UPL_DIR) {
			$this->add("tab",_LOCAL_DIR,_LOCAL_DIR_DESC,"dtab");
			$this->file_browser("package_dir",$msg[2], $dir_path, $valid_ext, $type, false);
//	        $this->add('textfield', "package_dir", $msg[2], $d_root);
			$this->add('tab_end');
		}
		$this->add("tab_tail");
		$this->add('hidden',"tab_num");
		$this->add("tab_sel","dtab","",$dtab);
    }
	
	function file_browser($name, $desc, $start_dir = '', $ext = null, $type = 'both', $required = false,
		$allow = array()) {
		$browse_field='<table border="0" cellpadding="0" cellspacing="0"><tr><td>';
		$browse_field.='<input class="tf" type="text" id="'.$name.'" name="'.$name.'" size="40" value="'.xhtml_safe($start_dir).'" />';
		global $d, $d_root, $d_private;
//		$js = $d->popup_js("'admin2.php?com_option=system&option=browse&type=$type&fname=$name&value=".
//				rawurlencode($start_dir).(isset($ext) ? '&ext[]='.implode('&ext[]=', $ext) : '')."'", 450, 250);
		if (isset($ext))
			out_session('fb_ext', $ext);
		out_session('fb_root_path', '');
		$denied = array(
			'admin/',
			'components/',
			'includes/',
			$d_private.'backup/',
			$d_private.'cache/',
			$d_private.'downloads/',
			$d_private.'temp/',
			$d_private.''.$GLOBALS['d_db'],
			$d_private.'config.php',
			$d_private.'custom_',
			$d_private.'log.php',
//			$d_private.'',
			'classes/',
			'docs/',
			'drabots/',
			'editor/',
			'install/',
			'lang/',
			'modules/',
			'templates/',
		);
		foreach($allow as $s) {
			if (in_array($s, $denied)) {
				$nd = array();
				foreach($denied as $e) {
					if ($e!==$s)
						$nd[] = $e;
				}
				$denied = $nd;
			}
		}
		out_session('fb_excluded_dirs', $denied);
		$start_dir = rawurlencode($start_dir);
		switch ($type) {
			case 'both':
				$a = '&files=1&dirs=1';
				break;
			case 'dir':
				$a = '&files=0&dirs=1';
				break;
			default:
				$a = '';
		}
		$js = $d->popup_js_ref("'index2.php?option=fb&preview=0&file_upload=0&fi=0&fi_name=$name&onplink=0&path=".$start_dir.$a."'", 520, 350);
		$browse_field.='<input type="button" onclick="javascript:'.$js.'" value="'.xhtml_safe(_BROWSE).'" />';
		$browse_field.='</td></tr></table>';
		if ($required) {
			$v = new ScriptedUI_Validation();
			$v->not_empty = true;
		} else $v=null;
//		var_dump($browse_field);die;
		$this->add('text', $name, $desc, $browse_field, $v);
	}
	
	function Ordering() {
		return ' ORDER BY ordering '.$this->filter_ord;
	}
	
	var $_nav_select = array();
	
	function mass_operations() {
		// access operations
		if ($this->has_access) {
			echo $this->row('&nbsp;','','');
			if ($this->admin_access)
				echo $this->row('<div class="massop">'._MOP_ACCESS.'</div>', access_select('mo_access', null, true, true));
			else
				
				echo $this->row('<div class="massop">'._MOP_ACCESS.'</div>', access_select('mo_access', null, false, true));
		}
		// add the ordering tools
		if ($this->has_ordering) {
			echo $this->row('<div class="massop"><label for="mo_invert_order">'._MOP_INVERT_ORDER_DESC.'</label></div>',
				'<input id="mo_invert_order" name="mo_invert_order" type="checkbox" /><label for="mo_invert_order">'._MOP_INVERT_ORDER.'</label>');
			$tmp = $this->_nav_select;
			$tmp[''] = _MOP_NO_CHANGE;
			echo $this->row('<div class="massop"><label for="mo_reorder">'.'Re-order by: '.
			'</label></div>', _create_select_raw('mo_reorder', $tmp, array('')));
			$tmp = null;
		}
		// ownership operations
		global $my;
		if ($this->has_owner && $my->is_admin()) {
			// forces cache update
			username_by_id(1);
			//$usr = array_merge(array('' => _MOP_NO_CHANGE), $GLOBALS['d__users']);
			$usr = $GLOBALS['d__users'];
			$usr[''] = _MOP_NO_CHANGE;
			echo $this->row('<div class="massop">'._MOP_OWNER_CHANGE.'</div>', _create_select_raw('mo_owner', $usr, array('')));
		} else $this->has_owner = false;	// else disable owner data
		// custom (content-only) option related to frontpage
		//TODO: security for frontpage editing please
		if ($this->has_frontpage) {
			$yn = array(
				'' => _MOP_NO_CHANGE,
				1 => _YES,
				0 => _NO
			);
			echo $this->row('<div class="massop">'._FRONTPAGE.'</div>', _create_select_raw('mo_frontpage', $yn, array('')));
		}
		
		// show the mass operations tools only if there are any
		if ($this->has_access || $this->has_ordering || $this->has_owner || $this->has_frontpage) {
			echo $this->row('&nbsp;','','');
			echo $this->row('<div class="massop">'._MOP_EXECUTE_DESC.'</div>', '&nbsp;<input type="button" onclick="dka_mo_exec()" value="'._MOP_EXECUTE.'" />');
			global $d;
			// this is the main javascript function submitting the 'massop' task
			$d->add_raw_js('function dka_mo_exec() {
				var frm=document.getElementById(lcms_data_form);
				if (frm.boxchecked.value==0) {
					alert(\''.js_enc(_IFC_LIST_ERR).'\');
					return;
				}
				lcms_st(\'massop\');
			}');
		}
	}
}

function access_select($name, $selected_val = null, $admin = false, $nc = false, $extra = '') {
	// create a copy of the array
	$al = $GLOBALS['access_level'];
	if ($nc)
		$al[] = array('name' => _MOP_NO_CHANGE,	'value' => '', 'selected' => true);
	if ($admin) {
//		$topsel = ($selected_val == 9);
		// remove the levels which are not valid in the admin backend
		array_shift($al);
		array_shift($al);
		array_shift($al);
		// remove 'Nobody' from candidates
		array_pop($al);
//		if ($topsel)$al[count($al)-1]['selected'] = true;
	}
	if (!$nc)
		$al = select($al, $selected_val);
	return _create_select($name, $al, false, $extra);
}

?>