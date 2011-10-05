<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## TinyMCE2 editor implementation
# @author legolas558
#

$_DRABOTS->registerFunction( 'OnEditor', 'botTinyMCE2' );
//$_DRABOTS->registerFunction( 'OnEditorSaveJS', 'botTinyMCE2EditorSaveJS' );
$_DRABOTS->registerFunction( 'OnEditorSaveAll', 'botTinyMCE2EditorSaveAll' );

global $d, $d_website;
$d->add_js('editor/tiny_mce2/tiny_mce.js');
$p = $d->GetComponentParamsRaw('fb');
$d->add_raw_js('
tinyMCE.init({
 entity_encoding : "raw",
 mode : "specific_textareas",
 theme : "advanced",
 width : "100%",'.
// height: "100%", commented because of IE6 bad behaviour
 'remove_linebreaks : true,
 plugins : "table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,zoom,flash,searchreplace,print,paste,directionality,fullscreen,noneditable,contextmenu",
 theme_advanced_buttons1_add_before : "fullscreen,print,separator",
 theme_advanced_buttons1_add : "fontselect,fontsizeselect",
 theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,zoom,separator,forecolor,backcolor,liststyle",
 theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
 theme_advanced_buttons3_add_before : "tablecontrols,separator",
 theme_advanced_buttons3_add : "emotions,iespell,flash,advhr,separator,ltr,rtl,separator",
 theme_advanced_toolbar_location : "top",
 theme_advanced_toolbar_align : "left",
 theme_advanced_statusbar_location : "bottom",
 content_css : "example_full.css",
 plugin_insertdate_dateFormat : "%Y-%m-%d",
 plugin_insertdate_timeFormat : "%H:%M:%S",
 extended_valid_elements : "hr[class|width|size|noshade]",
 external_link_list_url : "example_link_list.js",
 external_image_list_url : "example_image_list.js",
 flash_external_list_url : "example_flash_list.js",
 file_browser_callback : "fileBrowserCallBack",
 paste_use_dialog : false,
 theme_advanced_resizing : true,
 theme_advanced_resize_horizontal : false,
 theme_advanced_link_targets : "_something=MySomething;"
});

function fileBrowserCallBack(field_name, url, type, win) {
 // This is where you insert your custom filebrowser logic
 //alert("Filebrowser callback: field_name: " + field_name + ", url: " + url + ", type: " + type);
 
 var obj = win.document.forms[0].elements[field_name];
 var prev_path = obj.value;
 
 '.(isset($p) ? $d->popup_js_ref("'".$d_website."index2.php?option=fb&fi=0&fi_name='+escape(field_name)+'&path=' + escape(prev_path)" ,
	600, 400, 'win') :
	'alert(\''.js_enc(_CONTENTIMG_DISABLED).'\');').'
}

');

function botTinyMCE2($area_name, $area_content, $rows, $cols, $extra) {
	global $d;
	return $d->TextareaEditor($area_name, $area_content, $rows, $cols, 'mce_editable="true" '.$extra);
}

function botTinyMCE2EditorSaveAll() {
	return 'tinyMCE.triggerSave();';
}

?>