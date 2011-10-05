<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Common download code
# @author legolas558
#
# these are the common functions of the download component
#

function download_flags($i) {
	$flags = array();
	$flags['protected'] = ($i & 1)>0;
	$flags['antileech'] = ($i & 2)>0;
	return $flags;
}

function mk_download_flags($flags) {
	$i = 0;
	if ($flags['protected'])
		$i |= 1;
	if ($flags['antileech'])
		$i |= 2;
	return $i;
}

## save the download id of downloadable items
function antileech_conf($id, $flags) {
	$flags = download_flags($flags);
	if ($flags['antileech']) {
		global $d_uid;
		if (!isset($_SESSION[$d_uid.'-downloads']))
			$_SESSION[$d_uid.'-downloads'] = array();
		if (!in_array($id, $_SESSION[$d_uid.'-downloads']))
			$_SESSION[$d_uid.'-downloads'][] = $id;
	}
}

function remote_filesize($url, $timeout=2) {
   $url = parse_url($url);
   if (!isset($url['path'])) $url['path'] = '/';
   $size=false;
   if ($fp = @fsockopen($url['host'], ($url['port'] ? $url['port'] : 80), $errno, $errstr, $timeout))
   {
     fwrite($fp, 'HEAD '.$url['path']." HTTP/1.0\r\nHost: ".$url['host']."\r\n\r\n");
      stream_set_timeout($fp, $timeout);
       while (!feof($fp))
       {
           $size = fgets($fp, 4096);
           if (stripos($size, 'Content-Length') !== false) {
               $size = trim(substr($size, 16));
               break;
           }
       }
       fclose ($fp);
   }
   return intval($size);
}

function showitem(&$row,$complete=false,$rowcolor=1,$list=0) {
	global $conn,$access_sql,$Itemid,$my,$d,$pathway,$d_subpath;

	if(!$row) {
		CMSResponse::Unauthorized();
		return;
	}
//	echo '<tr><td colspan="5">';
	if($complete) {
		$crow=$conn->SelectRow('#__categories', 'id,name,access', ' WHERE id='.$row['catid'].' '.$access_sql);
		if (!$crow) {
			CMSResponse::Unauthorized();
			return;
		}

		$pathway->add($crow['name'],"option=downloads&catid=".$row['catid'].'&Itemid='.$Itemid);
		$pathway->add($row['title']);
		$d->add_meta($row['description']);
      ?>
      <div class="dk_header"><h2><?php echo $row['title']; ?></h2></div>
      <table width="100%" border="0" cellpadding="4" cellspacing="0" class="dk_content">
      <?php
    } else {
	  $crow = $conn->GetRow('SELECT access,description FROM #__categories WHERE id='.$row['catid'].' '.$access_sql);
	  if (!$crow) {
		CMSResponse::Unauthorized();
		return;
	  }
	  $d->add_meta($crow['description']);
    }
	
	if ($list) { global $d_subpath; ?>
<tr class="dkcom_tablerow<?php echo $rowcolor;?>"><td ><a title="<?php echo _DOWNLOADS_INFO; ?>" href="index.php?option=downloads&amp;task=info&amp;id=<?php echo $row['id']; ?>&amp;Itemid=<?php echo $Itemid.content_sef($row['title']); ?>"><img src="<?php echo $d_subpath;?>components/downloads/info.png" border="0" alt="Info" />&nbsp;<?php echo $row['title']; ?></a></td><td><?php echo $d->DateFormat($row['add_date']);?></td>
    <td width="10%" ><?php echo $row['hits']; ?></td>
    <td width="15%"><?php if ($row['filesize'])	echo convert_bytes($row['filesize']);
	else echo _NA; ?>
                </td><td><a title="<?php echo _DOWNLOADS_DOWNLOAD; ?>" href="index2.php?option=downloads&amp;no_comp=1&amp;no_html=1&amp;task=download&amp;id=<?php echo $row['id'].'&amp;Itemid='.$Itemid.content_sef('download '.$row['title']); ?>"<?php if (is_url($row['url'])) echo ' target="_blank"'; ?>><img src="<?php echo $d_subpath;?>components/downloads/download.png" border="0" alt="Download" /></a></td>
</tr>
<?php
	antileech_conf($row['id'], $row['flags']);
} else {
    ?><tr><td colspan="5">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr class="dkcom_tablerow<?php echo $rowcolor;?>">
    <?php
	if(strlen($row['image_url'])>10)
	{
	?>
          <td class="dkcom_tablerow<?php echo $rowcolor;?>" valign="top" align="right"><img src="<?php echo $row['image_url']?>" hspace="4" vspace="4"></td>
    <?php
	}
	?>
          <td width="90%" valign="top" class="dkcom_tablerow<?php echo $rowcolor;?>">
<table width="100%" cellpadding="2" cellspacing="0">
              <tr>
                <td valign="top" colspan="2"><strong><?php echo $row['title']; ?></strong>
                  <hr /></td>
              </tr>
              <tr >
                <td width="30%"><strong><?php echo _DOWNLOADS_FILESIZE; ?>:</strong></td>
                <td width="70%">
                  <?php if ($row['filesize'])	echo convert_bytes($row['filesize']);
						else echo _NA; ?>
                </td>
              </tr>
              <?php if(strlen($row['author'])>1)
			  { ?>
              <tr>
                <td><strong><?php echo _AUTHOR; ?>:</strong></td>
                <td><?php echo $row['author']; ?></td>
              </tr>
              <?php } ?>
              <tr  >
                <td><strong><?php echo _DOWNLOADS_ADDED; ?>:</strong></td>
                <td><?php echo $d->DateFormat($row['add_date']);?></td>
              </tr>
              <?php if($complete & ($row['mod_date']!=$row['add_date'])) { ?>
              <tr >
                <td><strong><?php echo _DOWNLOADS_MODIFIED; ?>:</strong></td>
                <td><?php echo $d->DateFormat($row['mod_date']);?></td>
              </tr>
              <?php } ?>
              <tr>
                <td><strong><?php echo _DOWNLOADS_HITS; ?>:</strong></td>
                <td><?php echo $row['hits']; ?></td>
              </tr>
            </table>
<?php
	global $_DRABOTS;
	$_DRABOTS->trigger('onDownloadVote', array($row));
	?>
	</td>
        </tr>
        <tr>
          <td colspan="2">
            <a title="<?php echo _DOWNLOADS_DOWNLOAD; ?>" href="index2.php?option=downloads&amp;no_comp=1&amp;no_html=1&amp;task=download&amp;id=<?php echo $row['id'].'&amp;Itemid='.$Itemid.content_sef('download '.$row['title']); ?>" ><img src="<?php echo $d_subpath;?>components/downloads/download.png" border="0" alt="Download" />&nbsp;<?php echo _DOWNLOADS_DOWNLOAD.' '.$row['title'];?></a>
            &nbsp;
      <?php
      antileech_conf($row['id'], $row['flags']);
      if(!$complete)
      {
      ?>
<a title="<?php echo _DOWNLOADS_INFO; ?>" href="index.php?option=downloads&amp;task=info&amp;id=<?php echo $row['id']; ?>&amp;Itemid=<?php echo $Itemid.content_sef($row['title']); ?>"><img src="<?php echo $d_subpath;?>components/downloads/info.png" border="0" alt="Info" />&nbsp;<?php echo _DOWNLOADS_INFO;?></a>
      <?php
      }
	  if (strlen($row['website'])>8) {
      ?>
[&nbsp;<a href="<?php echo $row['website'];?>" target="_blank"><?php echo _DOWNLOADS_WEBSITE;?></a>&nbsp;]
<?php } ?></td>
        </tr>
      <?php
      if ($complete)
      {
      ?>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2"><?php
	global $_DRABOTS;
	$_DRABOTS->trigger('onDownloadVoteForm', array($row['id']));
        ?></td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr><?php if (strlen($row['description'])) { ?>
        <tr>
          <td colspan="2"><?php echo "<strong>"._DOWNLOADS_DESCRIPTION.":</strong> ".$row['description'];?></td>
        </tr>
      <?php
      }
      }
      ?>
      </table>
      <?php
      if($complete) { ?>
	</td></tr>
	<tr><td align="center" colspan="2"><a href="index.php?option=downloads&amp;catid=<?php echo $row['catid'].'&amp;Itemid='.$Itemid; ?>"><?php echo _DOWNLOADS_GO_BACK_TO.' '.$crow['name']; ?></a></td></tr>
	</table>
 	  <?php
 	  }
	  echo '</td></tr>';
	  } // else not $list
//	echo '</td></tr>';
}


?>