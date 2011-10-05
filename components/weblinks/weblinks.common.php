<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function functionJS()
{?>
	<script language="javascript" type="text/javascript">
	
	function getSelectedValue( srcList ) {
		i = srcList.selectedIndex;
		if (i != null && i > -1) {
			return srcList.options[i].value;
		} else {
			return null;
		}
	}
	
	function submit_link() {
		var form = document.adminForm;
		// do field validation
		if (form.link_title.value == ""){
			alert( '<?php echo js_enc(_WEBLINKS_NEED_TITLE); ?>' );
		} else if (!getSelectedValue(form.link_catid)) {
			alert( "You must select a category." );
		} else if (form.link_url.value == ""){
			alert( "Your weblink must have a url." );
		} else {
			return true;
		}
		return false;
	}
	</script><?php }
	
?>	