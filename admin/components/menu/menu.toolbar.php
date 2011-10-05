<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$d__help_context = 'Administration/Menu';

$menutype = in_raw('menutype', $_REQUEST, false);
$task = in_raw('task', $_REQUEST, false);

if(!$menutype) {
    switch($task) {
        case "new":
            $toolbar->add("create");
            $toolbar->add("cancel");
            break;
    
        default : 
		$toolbar->add("new");
		$toolbar->add( "delete");
            break;
    }
    
} else {
    switch($task) {
        case "new":
            if (isset($_REQUEST['item_type']))
				$toolbar->add("create");
            $toolbar->add("cancel");
            break;
        case "edit":
            $toolbar->add("save");
            $toolbar->add("cancel");
            break;
        default : 
/*            $toolbar->add("publish");
            $toolbar->add("unpublish");
            $toolbar->add_split();	*/
            $toolbar->add("new");
	    $toolbar->add('edit');
//            $toolbar->add_custom(_TB_EDIT, 'edit', 'javascript:postitem()');
            $toolbar->add("reorder");
            $toolbar->add_custom_list(_DELETE, "delete", "if (!confirm('".sprintf(js_enc(_IFC_CONFIRM),
			constant('_IFC_OP_DELETE')."'+"./*_instance_notice(frm.boxchecked.value)+*/"'")."')) return; else submitform('delete');");
            break;
    }

}
?>