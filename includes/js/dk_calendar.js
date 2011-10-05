
/*** Custom Lanius CMS section ***/
//L: might need some testing

var mycalendar = null;

function c_click(cal, date) {
	cal.sel.value = date; 
}

function c_close(cal) {
	cal.hide();
	Calendar.removeEvent(document, "mousedown", checkCalendar);
}

function checkCalendar(ev) {
	var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
	for (; el != null; el = el.parentNode)
	if (el == mycalendar.element || el.tagName == "A") break;
	if (el == null) {
		mycalendar.callCloseHandler();
		Calendar.stopEvent(ev);
	}
}

function showCalendar(id, dateFmt) {
	var el = document.getElementById(id);
	mycalendar.parseDate(el.value); 
	mycalendar.setDateFormat(dateFmt);
	mycalendar.sel = el;		
	mycalendar.showAtElement(el);

	Calendar.addEvent(document, "mousedown", checkCalendar);
	return false;
}

// JS Calendar
mycalendar = new Calendar(Calendar._FD, null, c_click, c_close);
mycalendar.setRange(1900, 2100);	
mycalendar.create();		
mycalendar.showsTime = true;
