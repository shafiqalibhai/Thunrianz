<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Mozilla Midas / IE MSHTML native integration
# @author legolas558
#
# an embedded HTML editor available with Firefox and IE compatible browsers

$_DRABOTS->registerFunction( 'OnEditor', 'botNativeEditor' );

$_DRABOTS->registerFunction( 'OnEditorSaveJS', 'botNativeEditorSaveJS' );

global $d;
$d->add_raw_css('
.ne_imagebutton {height: 22; width: 23; border: solid 2px #C0C0C0; background-color: #C0C0C0}
.ne_image {position: relative; left: 1; top: 1; height:20; width:21; border:none;}
.ne_toolbar {height: 30; background-color: #C0C0C0;}');

$d->add_js('editor/native/native.js');

function botNativeEditor($area_name, $area_content, $rows, $cols, $extra) {
//	if (strstr(CMSRequest::UserAgent(), 'IE'))		return null;
//	if (!strstr(CMSRequest::UserAgent(), 'Mozilla'))		return null;
	global $d, $d_subpath;
	$fn = 'startfn_'.random_string(4);
	$d->add_raw_js('
	function '.$fn.'() {
		NativeStart(\''.$area_name.'\');
	}
	
	function tbclick_'.$area_name.'() {
		general_tbclick(\''.$area_name.'\', this);
	}
	');
	$d->add_js_onload($fn);
	return '<div id="div_'.$area_name.'"><table bgcolor="#C0C0C0" id="toolbar1" class="ne_toolbar">
<tr>
<td>
<div class="ne_imagebutton" id="cut"><img class="ne_image" src="'.$d_subpath.'editor/native/images/cut.png" alt="Cut" title="Cut"></div>
</td>
<td>
<div class="ne_imagebutton" id="copy"><img class="ne_image" src="'.$d_subpath.'editor/native/images/copy.png" alt="Copy" title="Copy"></div>
</td>
<td>
<div class="ne_imagebutton" id="paste"><img class="ne_image" src="'.$d_subpath.'editor/native/images/paste.png" alt="Paste" title="Paste"></div>
<td>
</td>
<td>
</td>
<td>
<div class="ne_imagebutton" id="undo"><img class="ne_image" src="'.$d_subpath.'editor/native/images/undo.png" alt="Undo" title="Undo"></div>
</td>
<td>
<div class="ne_imagebutton" id="redo"><img class="ne_image" src="'.$d_subpath.'editor/native/images/redo.png" alt="Redo" title="Redo"></div>
</td>
<td>
</td>
<td>
<div style="left: 10;" class="ne_imagebutton" id="createlink"><img class="ne_image" src="'.$d_subpath.'editor/native/images/link.png" alt="Insert Link" title="Insert Link"></div>
</td>
<td>
<div style="left: 10;" class="ne_imagebutton" id="createimage"><img class="ne_image" src="'.$d_subpath.'editor/native/images/image.png" alt="Insert Image" title="Insert Image"></div>
</td>
<td>
</td>
<td>
<div style="left: 10;" class="ne_imagebutton" id="createtable"><img class="ne_image" src="'.$d_subpath.'editor/native/images/table.png" alt="Insert Table" title="Insert Table"></div>
</td>
</tr>
</table>
<br>
<table bgcolor="#C0C0C0" id="toolbar2" class="ne_toolbar">
<tr>
<td>
<select id="formatblock" onchange="Select(this.id);">
  <option value="<p>">Normal</option>
  <option value="<p>">Paragraph</option>
  <option value="<h1>">Heading 1 <H1></option>
  <option value="<h2>">Heading 2 <H2></option>
  <option value="<h3>">Heading 3 <H3></option>
  <option value="<h4>">Heading 4 <H4></option>
  <option value="<h5>">Heading 5 <H5></option>
  <option value="<h6>">Heading 6 <H6></option>
  <option value="<address>">Address &lt;ADDR&gt;</option>
  <option value="<pre>">Formatted <PRE></option>
</select>
</td>
<td>
<select id="fontname" onchange="NativeSelect(\''.$area_name.'\', this.id);">
  <option value="Font">Font</option>
  <option value="Arial">Arial</option>
  <option value="Courier">Courier</option>
  <option value="Times New Roman">Times New Roman</option>
</select>
</td>
<td>
<select unselectable="on" id="fontsize" onchange="Select(this.id);">
  <option value="Size">Size</option>
  <option value="1">1</option>
  <option value="2">2</option>
  <option value="3">3</option>
  <option value="4">4</option>
  <option value="5">5</option>
  <option value="6">6</option>
  <option value="7">7</option>  
</select>
</td>
<td>
<div class="ne_imagebutton" id="bold"><img class="ne_image" src="'.$d_subpath.'editor/native/images/bold.png" alt="Bold" title="Bold"></div>
</td>
<td>
<div class="ne_imagebutton" id="italic"><img class="ne_image" src="'.$d_subpath.'editor/native/images/italic.png" alt="Italic" title="Italic"></div>
</td>
<td>
<div class="ne_imagebutton" id="underline"><img class="ne_image" src="'.$d_subpath.'editor/native/images/underline.png" alt="Underline" title="Underline"></div>
</td>
<td>
</td>
</tr>
</table>
<table bgcolor="#C0C0C0" id="toolbar3" class="ne_toolbar">
<tr>
<td>
<div style="left: 10;" class="ne_imagebutton" id="forecolor"><img class="ne_image" src="'.$d_subpath.'editor/native/images/forecolor.png" alt="Text Color" title="Text Color"></div>
</td>
<td>
<div style="left: 40;" class="ne_imagebutton" id="hilitecolor"><img class="ne_image" src="'.$d_subpath.'editor/native/images/backcolor.png" alt="Background Color" title="Background Color"></div>
</td>
<td>
</td>
<td>
<div style="left: 10;" class="ne_imagebutton" id="justifyleft"><img class="ne_image" src="'.$d_subpath.'editor/native/images/justifyleft.png" alt="Align Left" title="Align Left"></div>
</td>
<td>
<div style="left: 40;" class="ne_imagebutton" id="justifycenter"><img class="ne_image" src="'.$d_subpath.'editor/native/images/justifycenter.png" alt="Center" title="Center"></div>
</td>
<td>
<div style="left: 70;" class="ne_imagebutton" id="justifyright"><img class="ne_image" src="'.$d_subpath.'editor/native/images/justifyright.png" alt="Align Right" title="Align Right"></div>
</td>
<td>
</td>
<td>
<div style="left: 10;" class="ne_imagebutton" id="insertorderedlist"><img class="ne_image" src="'.$d_subpath.'editor/native/images/orderedlist.png" alt="Ordered List" title="Ordered List"></div>
</td>
<td>
<div style="left: 40;" class="ne_imagebutton" id="insertunorderedlist"><img class="ne_image" src="'.$d_subpath.'editor/native/images/unorderedlist.png" alt="Unordered List" title="Unordered List"></div>
</td>
<td>
</td>
<td>
<div style="left: 10;" class="ne_imagebutton" id="outdent"><img class="ne_image" src="'.$d_subpath.'editor/native/images/outdent.png" alt="Outdent" title="Outdent"></div>
</td>
<td>
<div style="left: 40;" class="ne_imagebutton" id="indent"><img class="ne_image" src="'.$d_subpath.'editor/native/images/indent.png" alt="Indent" title="Indent"></div>
</td>
</tr>
</table>
</div>
<p><label for="switch_source_view_'.$area_name.'"><input id="switch_source_view_'.$area_name.'" type="checkbox" onclick="viewsource(\''.$area_name.'\', this.checked)" />
View HTML Source</label></p>
<iframe src="" name="if_'.$area_name.'" id="if_'.$area_name.'" width="600" height="400px"></iframe>
<iframe width="250" height="170" id="colorpalette_'.$area_name.'" src="'.$d_subpath.'editor/native/images/colors.html" style="visibility:hidden; position: absolute;"></iframe>
<textarea name="'.$area_name.'" id="'.$area_name.'" style="visibility: hidden">'.$area_content.'</textarea>';
}

function botNativeEditorSaveJS($area_name) {
	global $_DRABOTS;
	$params = $_DRABOTS->GetBotParameters('editor', 'native_editor');
	return 'NativeSave(\''.$area_name.'\', '.$params->get('cleanup', 1).');';
}

?>