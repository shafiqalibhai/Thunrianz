<?php if(!defined('_VALID')){header('Status: 404 Not Found');die;}

$pathway->add_head(_EVENT_EVENTS);

$month = in_num('month', $_GET);
if (isset($month)) {
	if ($month>12) $month=12;
	else if ($month<1) $month=1;
}
    
$year = in_num('year', $_GET);
if (!isset($year))
	$year = lc_date("Y",$time);

$redirect = in_num('redirect', $_GET, 0);
if ($redirect) {
	out_session('eventcal-month', $month);
	out_session('eventcal-year', $year);
	CMSResponse::Back();
} else {
	// $d_type was used here
	include(com_path('html'));
	$sort = in_raw('event_sorting', $_GET, $params->get('default_sorting', 'ASC'), 4);
	if ( ($sort !== 'ASC') && ($sort !== 'DESC')) {
		CMSResponse::BadRequest();
		return;
	}
	view_events($month, $year, $sort);
}

?>