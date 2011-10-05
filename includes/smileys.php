<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Smileys table
# @author legolas558
#
# plain include file
# must have included bbcode.php before

global $d_subpath, $sm_img, $sm_chars, $sm_desc;
	
?><table style="width:100px" border="0" cellpadding="0" cellspacing="5">
      <tbody>
	<?php
		$c=count($sm_img);
		$sm_per_row = 12;
		$sm_rows = (int)($c / $sm_per_row);
		if ($c % $sm_per_row>0)
			$sm_rows++;
		for ($ir=0;$ir<$sm_rows;++$ir) {
	?><tr align="center" valign="middle"><?php
			$top=min($c-$ir*$sm_per_row, $sm_per_row)+$ir*$sm_per_row;
			for ($i=$ir*$sm_per_row;$i<$top;++$i) {
			if ($top % $sm_per_row)
				$colspan=' colspan="'.($top % $sm_per_row).'"';
			else $colspan='';
          ?><td<?php echo $colspan; ?>>
			<a href="javascript:smile('<?php echo $sm_chars[$i]; ?>')"><img alt="<?php echo xhtml_safe($sm_desc[$i]); ?>" border="0"
            src="<?php echo $d_subpath; ?>media/common/smilies/<?php echo $sm_img[$i]; ?>.png" title="<?php echo xhtml_safe($sm_desc[$i]); ?>" /></a>
          </td><?php } // next smiley ?>
        </tr><?php } // next row ?>
      </tbody>
    </table>