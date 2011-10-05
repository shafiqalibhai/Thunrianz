<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}
## Event calendar module
# @author legolas558
# original @author Vikas Patial
#
# main module file

include_once $d_root.'modules/mod_eventcal.common.php';

global $time;

$month = in_session('eventcal-month');
$year = in_session('eventcal-year');

$content = "";

// Gather variables from
// user input and break them
// down for usage in our script

if(!isset($month))$month=lc_date("m",$time);
if(!isset($year))$year=lc_date("Y",$time);

$date = ec_time($month,1,$year);

$day = lc_date('j', $date);
$month = lc_date('n', $date);
$year = lc_date('Y', $date);

// Get the first day of the month
$month_start = ec_time($month, 1, $year);
$month_end = ec_time($month+1,1, $year);

// Get friendly month name
$month_name = lc_strftime('%b', $month_start, 'ucfirst');

// Figure out which day of the week
// the month starts on
$month_start_day = lc_strftime('%a', $month_start, 'ucfirst');

for ($offset=0;$offset<7;$offset++) {
	if (dow($offset)==$month_start_day)
		break;
}

// determine how many days are in the last month
if($month == 1){
   $num_days_last = cal_days_in_month(0, 12, ($year -1));
} else {
   $num_days_last = cal_days_in_month(0, ($month -1), $year);
}
// determine how many days are in the current month
$num_days_current = cal_days_in_month(0, $month, $year);

// Build an array for the current days
// in the month
for($i = 1; $i <= $num_days_current; $i++){
	$num_days_array[] = $i;
}

// Build an array for the number of days
// in last month
for($i = 1; $i <= $num_days_last; $i++){
	$num_days_last_array[] = $i;
}

// If the $offset from the starting day of the
// week happens to be Sunday, $offset would be 0,
// so don't need an offset correction.

if($offset > 0){
	$offset_correction = array_slice($num_days_last_array, -$offset, $offset);
	$new_count = array_merge($offset_correction, $num_days_array);
	$offset_count = count($offset_correction);
}

// The else statement is to prevent building the $offset array.
else {
	$offset_count = 0;
	$new_count = $num_days_array;
}

// count how many days we have with the two
// previous arrays merged together
$current_num = count($new_count);

// Since we will have 5 HTML table rows (TR)
// with 7 table data entries (TD)
// we need to fill in 35 TDs
// so, we will have to figure out
// how many days to append to the end
// of the final array to make it 35 days.


if($current_num > 35){
   $num_weeks = 6;
   $outset = (42 - $current_num);
} elseif($current_num < 35){
   $num_weeks = 5;
   $outset = (35 - $current_num);
}
if($current_num == 35){
   $num_weeks = 5;
   $outset = 0;
}
// Outset Correction
for($i = 1; $i <= $outset; $i++){
   $new_count[] = $i;
}

// Now let's "chunk" the $all_days array
// into weeks. Each week has 7 days
// so we will array_chunk it into 7 days.
$weeks = array_chunk($new_count, 7);

///Get all the event for the current month
global $conn,$access_sql;

$event_array=array();
$rsa=$conn->SelectArray('#__event', 'id,title,sdate,edate,published', " WHERE edate >= $month_start AND sdate <= $month_end AND published=1 $access_sql");
foreach($rsa as $res) {
	// get the event row id
	$eventid=$res['id'];

	// if year matches in start date
	if (lc_date("Y",$date)==lc_date("Y",$res['sdate']))
		// get day of the year
		$date_start=lc_date("z",$res['sdate']);
	else
		$date_start=0;
	// if year matches in end date
	if (lc_date("Y",$date)==lc_date("Y",$res['edate']))
		// get day of the year
		$date_end=lc_date("z",$res['edate']);
	else
		$date_end=365;
		
//	var_dump(lc_strftime('%Y-%m-%d %H:%M:%S', $res['sdate']));
//	var_dump(lc_strftime('%Y-%m-%d %H:%M:%S', $res['edate']));
//	die;

	for($ke=$date_start;$ke<=$date_end;$ke++) {
		if (!isset($event_array[$ke])) {
			$event_array[$ke]['title']=$res['title'];
			$event_array[$ke]['id']=$eventid;
		} else
			$event_array[$ke]['title'].="\n".$res['title'];
	}
}

$inst = module_instance($module['instance'], 'event');

if (!$params->get('redirect', 0))
	$burl = 'index2.php?option=event&amp;no_html=1&amp;redirect=1';
else	$burl = 'index.php?option=event';
// Build Previous and Next Links
$previous_link = "<a class=\"movelink\" href=\"$burl&amp;month=";
if($month == 1){
   $previous_link .= lc_date("n",ec_time(12,$day,($year -1)))."&amp;year=".lc_date("Y",ec_time(12,$day,($year -1)));
} else {
   $previous_link .= lc_date("n",ec_time(($month -1),$day,$year))."&amp;year=".lc_date("Y",ec_time(($month -1),$day,$year));
}
$previous_link .= $inst."\">"._PREV_ARROW._PREV."</a>";

$next_link = "<a class=\"movelink\" href=\"$burl&amp;month=";
if($month == 12){
   $next_link .= lc_date("n",ec_time(1,$day,($year + 1)))."&amp;year=".lc_date("Y",ec_time(1,$day,($year + 1)));
} else {
   $next_link .= lc_date("n", ec_time(($month +1),$day,$year))."&amp;year=". lc_date("Y", ec_time(($month +1),$day,$year));
}
$next_link .= $inst."\">"._NEXT._NEXT_ARROW."</a>";

// Build the heading portion of the calendar table
$content.= "<table cellpadding=\"0\" cellspacing=\"2\" width=\"100%\">\n".
	"<tr>\n".
	"<td colspan=\"7\">\n".
	"<table class=\"header\" align=\"center\" width=\"100%\">\n".
	"<tr>\n".
	"<td colspan=\"2\"  align=\"left\" nowrap=\"nowrap\">$previous_link</td>\n".
	"<td colspan=\"3\"  align=\"center\" nowrap=\"nowrap\"><span class=\"monthyear\">$month_name $year</span></td>\n".
	"<td colspan=\"2\"  align=\"right\" nowrap=\"nowrap\">$next_link</td>\n".
	"</tr>\n".
	"</table>\n".
	"</td>\n".
	"</tr>\n".
	"<tr align=\"center\">\n".
	"<td class=\"sunday\">".dowi(0)."</td>
	<td class=\"monday\">".dowi(1)."</td>
	<td class=\"tuesday\">".dowi(2)."</td>
	<td class=\"wednesday\">".dowi(3)."</td>
	<td class=\"thursday\">".dowi(4)."</td>
	<td class=\"friday\">".dowi(5)."</td>
	<td class=\"saturday\">".dowi(6)."</td>\n".
	"</tr>\n";

// Now we break each key of the array
// into a week and create a new table row for each
// week with the days of that week in the table data

$i = 0;
foreach($weeks as $week){
	   $content.= "<tr align=\"center\">\n";
	   foreach($week as $d){
		 if($i < $offset_count){
			$content.= "<td>&nbsp;</td>\n";

		 }
		 if(($i >= $offset_count) && ($i < ($num_weeks * 7) - $outset)){
			$daynum=lc_date("z",ec_time($month,$d,$year));
			$event='';
			$day_link = $d;
			if (isset($event_array[$daynum])) {
				$day_link = "<a class=\"daylink\" href=\"index.php?option=event".$inst.
				"&amp;month=".$month."&amp;year=".$year."\" title=\"".$event_array[$daynum]['title']."\">$d</a>";
			}
		   if(ec_time(lc_date("n", $time),lc_date("j", $time),lc_date("Y", $time) ) == ec_time($month,$d,$year)){
			   $content.= "<td><strong>$day_link</strong></td>\n";
		   } else {
			   $content.= "<td>$day_link</td>\n";
		   }
		} elseif(($outset > 0)) {
			if(($i >= ($num_weeks * 7) - $outset)){
			  $content.= "<td>&nbsp;</td>\n";
		   }
		}
		$i++;
	  }
	  $content.= "</tr>\n";
}

// Close out your table and that's it!
$content.= '<tr><td colspan="7" class="days"></td></tr>';
$content.= '</table>';

echo $content;

?>