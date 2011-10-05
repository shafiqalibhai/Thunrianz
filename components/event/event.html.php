<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

function event_lc_date($t) {
	return lc_strftime(_EVENT_DATE_FORMAT, $t);
}

function view_events($month, $year, $sorting) {
	global $conn, $time, $d;
	$date = lc_mktime(0,0,0,$month,1,$year);
	$this_m = lc_date('n');
	if (!isset($month)) {
		$month_start = lc_mktime(0,0,0,1,1, $year);
		// the 0th day of the next year is the last day of the previous year
		$month_end = lc_mktime(0,0,0,1,0, $year+1);
		$title = _EVENTS_IN.' '.$year;
	} else {
	//	$m = lc_date('n',$date);
		$m = $month;
		$month_start = lc_mktime(0,0,0,$m,1, $year);
		// the 0th day of the next month is the last day of the previous month
		if ($m<12)
			$month_end = lc_mktime(0,0,0,$m+1,1, $year);
		else
		// this seems to be the only trick that makes days of 31 December work
			$month_end = lc_mktime(0,0,0,12,32, $year);
		$title = _EVENTS_IN.' '.lc_strftime("%B %Y",$date);
	}

	// validate input data
	if (!$month_start || !$month_end) {
		CMSResponse::BadRequest();
		return;
	}

?>
<div class="dk_header"><h2><?php echo $title; ?></h2></div>
<div class="dk_content">
  <?php echo _EVENTS_DESC;?>
   <form name="form1" method="get" class="dk_form" action="index.php">
   <input type="hidden" name="option" value="event" />
   <div class="dk_content">
     <span>
       <select name="month" class="dk_inputbox">
	<option value="" <?php if(!isset($month))echo "selected=\"selected\"";	?>>-- <?php echo _EVENT_ALL_MONTHS; ?></option>
         <?php for($i=1;$i<=12;$i++)
         { ?>
         <option value="<?php echo $i; ?>" <?php if($i==$month)echo "selected=\"selected\"";
	 //TODO: use a class instead
	 if ($this_m==$i) echo ' style="font-weight:bold"';
	 ?> > <?php echo lc_strftime("%B",lc_mktime (0,0,0,$i,1,2003), 'ucfirst');?></option>
   	     <?php }
	     //TODO: bold for the current year too
	     ?>
	   </select>
   	   <select name="year" class="dk_inputbox">
         <?php
		global $time;
		$this_year = lc_date("Y",$time);
		for ($i=$year-4;$i<=$year+4;$i++){ ?>
         <option value="<?php echo $i; ?>" <?php if($i==$year)echo"selected=\"selected\""; ?> > <?php echo $i;?></option>
         <?php } ?>
       </select>
       <select name="event_sorting" class="dk_inputbox">
       <option value="ASC"<?php if ($sorting == 'ASC') echo ' selected="selected"'; ?> ><?php echo _SORT_ASC; ?></option>
       <option value="DESC"<?php if ($sorting == 'DESC') echo ' selected="selected"'; ?> ><?php echo _SORT_DESC; ?></option>
       </select>
       <input type="submit" class="dk_button" value="<?php echo _SUBMIT; ?>" /></span>
  </div>
  </form>
  <div class="dkcom_evententry">
<?php

// it must be less or equal (>=) month_start and less month_end. See definition of both variables
global $access_sql;
$rsa=$conn->SelectArray('#__event', '*', " WHERE edate >= $month_start AND sdate < $month_end AND published=1 $access_sql ORDER BY sdate ".$sorting);
if (isset($rsa[0])) {
$color=1;
foreach($rsa as $row) {
	$date_start=event_lc_date($row['sdate']);
  	$date_end=event_lc_date($row['edate']);
	++$color;
	?>
    <div class="dkcom_evententryitem<?php echo ($color%2)?2:1;?>">
      <a name="ev<?php echo $row['id'];?>"></a>
      <div>
        <div class="dkcom_evententryitemcolumn1"><strong><?php echo _EVENTS_TITLE;?></strong></div>
        <div class="dkcom_evententryitemcolumn2"><strong><?php echo $row['title'];?></strong></div>
      </div>
      <div>
        <div class="dkcom_evententryitemcolumn1"><strong><?php echo _EVENTS_DATE;?></strong></div>
        <div class="dkcom_evententryitemcolumn2"><?php echo $date_start.' '._EVENTS_TO.' '.$date_end; ?></div>
      </div>
<?php  if($row['description']!=="") { ?>
      <div>
        <div class="dkcom_evententryitemcolumn1"><strong><?php echo _DESC;?></strong></div>
        <div class="dkcom_evententryitemcolumn2"><?php echo $row['description'];?></div>
      </div>
<?php } ?>
    <div class="dkcom_eventspacer">
       &nbsp;
    </div>
  </div>
	<?php
	}
}else {
?>
    <div align="center"><?php if (isset($month)) echo _EVENTS_NONE; else echo _EVENT_NONE_YEAR; ?></div>
<?php
}
?>

  </div>
</div>
<?php

}
?>