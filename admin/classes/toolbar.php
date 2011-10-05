<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Toolbar generation class
# @author legolas558
# Released under GNU/GPLv2 license
# This component is part of Lanius CMS core
#
# toolbar class
#

// fast reverse-look (action,label)
global $TB_fast_m;
$TB_fast_m = array('new' => _TB_NEW,
				'upload' => _TB_UPLOAD,
				'upload_gui' => _TB_UPLOAD_GUI,
				'edit' => _TB_EDIT,
				'delete' => _DELETE,
				'reorder' => _TB_REORDER,
				'publish' => _TB_PUBLISH,
				'unpublish' => _TB_UNPUBLISH,
				'create' => _TB_CREATE,
				'save' => _TB_SAVE,
				'install' => _INSTALL,
				'uninstall' => _UNINSTALL);

class Toolbar {
	var $buttons=array();
	var $alt_buttons = array();
	
	// whether this toolbar is managing lists or not
	var $has_lists = false;
	
	function add($btn_name) {
		global $TB_fast_m;
		if (!isset($TB_fast_m[$btn_name])) {
			switch ($btn_name) {
				case "back" :
					$this->add_custom(_TB_BACK,'back', "window.history.back()");
				break;
				case "cancel" :
					$this->add_custom(_CANCEL,'cancel',"history.go(-1)");
				break;																
				default:
					trigger_error('Specified button is not recognized as default!');
			}
			return;
		}
		
		if ($btn_name == 'upload') {
			$onclick = '';
			//FIXME: directory check missing!
			for ($i=1;$i<5;++$i)
					$onclick .= '&& (!f.upload_file'.$i.'.value.length) ';
			$onclick = 'var f=document.getElementById(lcms_data_form);if ('.substr($onclick, 2).') { alert(\''.js_enc(_UPLOAD_NONE_SELECTED).'\'); return; } else ui_lcms_st(\'upload\');';
		}else
			$onclick = null;
		$this->add_custom($TB_fast_m[$btn_name], $btn_name, $onclick);
	}
	
	var $_split_index = 0;
	function add_split() {
		++$this->_split_index;
		$this->add_custom('', '_split_'.$this->_split_index);
	}

	//TODO: check calls to add_custom using old syntax
	function add_custom($title, $task, $onclick=null, $enabled = true) {
		// for debug purposes
		if (isset($this->buttons[$task]))
			trigger_error('A toolbar button for task '.$task.' has already been defined');
		$this->buttons[$task] = array($title, $onclick, $enabled);
	}
	
	function add_alt($title, $task) {
		if (isset($this->alt_buttons[$task]))
			trigger_error('A toolbar button for task '.$task.' has already been defined');
		$this->alt_buttons[$task] = $title;
	}
	
	function add_custom_list($title, $task, $onclick = null, $enabled = true) {
		$this->has_lists = true;
		$js = "if (!lcms_list_check()) return;";
		if (isset($onclick))
			$js .= $onclick;
		else
			$js .= 'ui_lcms_st(\''.$task."');";
		$this->add_custom($title, $task, $js, $enabled);
	}
	
	var $_cached = null;
	function generate()	{
		if (!isset($this->_cached)) {
			if ($this->has_lists) {
				global $d;
				$d->add_unique_js('lcms_list_check', 	"function lcms_list_check() {
				var afrm=document.getElementById(lcms_data_form);
				if (afrm.boxchecked.value == 0) {
					alert('".js_enc(_IFC_LIST_ERR)."');
					return false;
				}
				return true;
				}");
			}
			$this->_cached = '';
			$ns = '';
			foreach($this->buttons as $task => $props) {
				list($title, $onclick, $enabled) = $props;
				if (strpos($task, '_split')===0) {
					$this->_cached .= "&nbsp;&nbsp;&nbsp;&nbsp;";
					continue;
				}
				//$type = $onclick ? 'button':'submit';
				$type = 'button';
				$this->_cached .= '<input name="btn_'.$task.'" type="'.$type.'" value="'.
					xhtml_safe($title).'"';
				if (!isset($onclick))
					$onclick = 'ui_lcms_st(\''.$task.'\');';
				$this->_cached .= ' onclick="'.$onclick.'" ';
				if (!$enabled)
					$this->_cached .= ' disabled="disabled"';
				else { // compile the noscript version of this button
					$ns .= '<option value="'.$task.'">'.xhtml_safe($title)."</option>\n";
				}
				$this->_cached .= " />\n";
			}
			foreach ($this->alt_buttons as $task => $title) {
				$ns .= '<option value="'.$task.'">'.xhtml_safe($title)."</option>\n";
			}
			if (strlen($ns))
				$ns = "<noscript>\n<p> If you have no javascript support, then ignore the above buttons and use this combo box.</p>\n<select name=\"alt_task[]\">\n".
				"<option value=\"\">--</option>\n".
				$ns."</select>\n".
				"<input type=\"submit\" value=\"Go\" />".
				"</noscript>\n";
			$this->_cached .= $ns;
		}
		echo $this->_cached;
	}
	
	function is_button($button) {
		return isset($this->buttons[$button]);
	}
	
	function HasItems() { return (count($this->buttons)!=0); }
	
	function HelpIcon() {
		// enable for debugging help contexts
		global $d__help_context;
		if (!isset($d__help_context))
			trigger_error('Undefined help context for '.$GLOBALS['com_option'].'/'.$GLOBALS['task'].':'.$GLOBALS['option']);
		if (!strlen($d__help_context))
			return;
		// after each button, show help context icon
		echo '&nbsp;&nbsp;'.
			'<a target="_blank" href="'.create_context_help_url($d__help_context).'">'.
			'<img src="'.admin_template_pic('help.png').'" border="0" alt="Help" /></a>';
	}

	function GetTask() {
		$alt_task = in_arr('alt_task', __RAW, $_POST);
		if (isset($alt_task)) {
			// take the 1st toolbar task available, if any
			foreach($alt_task as $task) {
				if (strlen($task))
					return $task;
			}
		}
		return in_raw('task');
	}
}

?>