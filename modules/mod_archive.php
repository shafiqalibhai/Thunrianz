<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## News archive module
#
# main module output
#

global $conn, $access_sql,$d_website;

$count = $params->get( 'count' ,'10');

$cdates = $conn->SelectColumn('#__content', 'created', " WHERE published = 4 $access_sql ORDER BY created DESC");

$months=false;
$listm=false;
$loopcount =0;

  // added handling to only display the amount of archive
  //  records specified by the count parameter.

  // Set our starting point of 1 record, since if it's null it defaults to 5
  $loopcount = 1;
  foreach ($cdates as $cdate) {
    // check our count first if set to 0 don't display anything
    if($loopcount <= $count)
    {
      $tlist=array("date"=>lc_strftime("%B, %Y",$cdate), "link"=>"&amp;month=".date("n",$cdate)."&amp;year=".date("Y",$cdate) );

      if(!$listm)
      {
        $listm[]=$tlist;
        $plist=$tlist;
        // increment for the first array element insert
        $loopcount++;
        continue;
      }
      if($plist['date']!=$tlist['date'])
      {
        $listm[]=$tlist;
        $plist=$tlist;
        // increment on each successive insert
        $loopcount++;
      }
    }
    else // we have reached the defined limit so stop processing the results
      break;
  }
  
global $d_type;

if($d_type=="html") {
	if (!$listm) {
		
		$module = $module['module'];
		global $my;

		$path = mod_lang($my->lang, $module);
		include_once $path;
		
		echo _NO_ARCHIVED_NEWS;
		return;
	}
?>
  <ul>
  <?php
  
  // get the default component instance
  $inst = module_instance($module['instance'], 'content');
  
  foreach ($listm as $row) {
  ?>
    <li>
      <a href="index.php?option=content&amp;task=archive<?php echo $row['link'].$inst; ?>"><?php echo $row['date'];?></a>
    </li>
  <?php
  }
  ?>
  </ul>
<?php
}
?>