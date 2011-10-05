<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Wrapper common functions
# @author legolas558
#
# contains request_wrapper() and displayWrap()


function request_wrapper($id, $inline = false) {
	global $conn, $access_sql, $d, $pathway;
	if ($inline) {$e='';$a=',params'; }else {$a='';$e=$access_sql;}
	$row=$conn->SelectRow('#__menu', 'name'.$a, " WHERE id=$id $e");
	if (!count($row)){	// should never happen
		if (!$inline)
			CMSResponse::Unauthorized();
		return false;
	}

	if (!$inline) {
		$pathway->add($row['name']);
		$params =& $GLOBALS['params'];
	} else
		$params = new param_class($row['params']);
	
	// auto height control
	if ( $params->get( 'height_auto' ) )
		$auto_h = true;
	else
		$auto_h = false;
	
	displayWrap( $params->get('url'), $row['name'], $auto_h, $params);
	return true;
}

## splits an url into the URL part and the querystring part (dirty)
function split_url($url) {
	$p=strpos($url, '?');
	if ($p!==false) {
		$qs = substr($url, $p+1);
		$url = substr($url, 0, $p);
	} else $qs = '';
	return array('url' => $url, 'qs' => $qs);
}

function displayWrap( $link, $name, $auto_h, $params) {

	switch ($params->get('use_iframe', 'auto')) {
		default:
		case 'auto':
			$go_iframe = is_url($link);
			break;
		case 'no':
			$go_iframe = false;
			break;
		case 'yes':
			$go_iframe = true;
	}

	?>	
                <div class="dk_content <?php echo $params->get( 'custom_class','' ); ?>">
                <?php
                if ( $params->get( 'page_title','1' ) ) {
				?>
                        <div class="dk_header <?php echo $params->get( 'custom_class','' ); ?>">
						<h2><?php echo $name; ?></h2>						
                        </div>
                        <?php
                }
				
				if ($go_iframe) {
					global $d;
					$uid = mt_rand();
				    $d->add_raw_js('function iFrameHeight_'.$uid.'() {
				                        var h = 0;
							var ident="wrapper_'.$uid.'";
				                        if ((navigator.appName).indexOf("Microsoft")!=-1) {
				                                h = document.frames(ident).document.body.scrollHeight + 20;
				                                document.all.wrapper_'.$uid.'.style.height = h.toString() + "px";
				                        } else if( document.getElementById ) {
								var doc = document.getElementById(ident);
				                                h = doc.contentDocument.height + 60;
				                                doc.style.height = h.toString() + "px";
				                        }
				                }');
					if (!$auto_h)
						$d->add_js_onload('iFrameHeight_'.$uid);
					
					$width = $params->get( 'width');
					$height = $params->get( 'height');
					
                ?>
                <iframe title="title" src="<?php echo $link; ?>"
				<?php if ($auto_h) echo ' onload="iFrameHeight_'.$uid.'"'; ?>
                id="wrapper_<?php echo $uid; ?>"
                <?php if ($width) echo ' width="'.$width.'"';
				if ($height) echo ' height="'.$height.'"'; ?>
                scrolling="<?php echo $params->get( 'scrolling' ,'auto'); ?>"
                align="top"
                frameborder="0"
                class="dkcom_wrapper<?php echo $params->get( 'custom_class' ,''); ?>">
				<p align="center">
				<a href="<?php echo $link; ?>"><?php echo $link; ?></a>
				</p>
                </iframe><?php } else { 
				// show non-iframe output using a container div
				$width = $params->get( 'width');
				if ($width && strpos('%', $width)===false)
					$width.='px;';
				$height = $params->get( 'height');
				if ($height && strpos('%', $height)===false)
					$height.='px;';
				
				?><div style="<?php if ($width) echo 'width:'.$width; if ($height) echo ' height:'.$height; ?>"><?php
					$im = $params->get('include_mode', 'auto');
					switch ($im) {
						case 'auto':
							extract(split_url($link));
							$go_php = (file_ext($url) == 'php');
							break;
						case 'php':
							$go_php = true;
							extract(split_url($link));
							break;
						case 'raw':
						case 'pre':
							$go_php = false;
					}
					if ($go_php) {
						parse_str($qs, $_GET);
						include $url;
					} else {
						if (!is_url($link)) {
							// show an error
							if (!is_readable($link)) {
								global $my;
								if ($my->is_admin())
									$msg = sprintf(_WRAPPER_NOT_FOUND_P,
											'<br />'.xhtml_safe(fix_root_path($link)));
								else
									$msg = _WRAPPER_NOT_FOUND;
								CMSResponse::Unavailable($msg);
								return;
							}
						}
						if ($im=='raw') {
							safe_readfile($link);
						} else {
							echo '<pre>';
							// this code will output the file from ISO-8859-1 or UTF-8
							// UTF-8 is preferred, all wrapped files should be UTF-8
							$ct = file_get_contents($link);
							if (strlen($ct)) {
								$sct = xhtml_safe($ct);
								if (!strlen($sct))
									echo xhtml_safe(utf8_encode($ct));
								else
									echo $sct;
								$sct = null;
							} $ct = null;
							echo '</pre>';
						}
					}
					?></div>
				<?php } ?>
                </div>
                <?php
        }

?>