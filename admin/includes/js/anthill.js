
/*
function submitbutton(pressbutton){
	submitform(pressbutton)
} */

function submitform(pressbutton){
	lcms_st(pressbutton);
}

// Lanius CMS submit task
function lcms_st(task){
	var afrm=document.getElementById(lcms_data_form);
	afrm.task.value=task;
	try { afrm.onsubmit();	}
	catch(e){}
	afrm.submit();
}

/* check if something is selected */
function isChecked(isitchecked){
var afrm=document.getElementById(lcms_data_form);
	if (isitchecked == true){
		afrm.boxchecked.value++;
	}
	else {
		afrm.boxchecked.value--;
	}
}

/* ordering of items */
function listItemTask( id, task ) {
	var afrm=document.getElementById(lcms_data_form);
	cb = eval( 'afrm.' + id );
	if (cb) {
		cb.checked = true;
		lcms_st(task);                  
	}
	return false;
}

function listItemCheck( id ) {
	var afrm=document.getElementById(lcms_data_form);
	cb = eval( 'afrm.' + id );
	if (cb) {
		cb.checked = true;
		Highlight(cb);    
		afrm.boxchecked.value++;         
	}
	return false;
}

    function Toggle(e)
    {
	if (e.checked) {
	Highlight(e);
	}
	else {
	Unhighlight(e);
	}
    }

    function ToggleAll(e)
    {
	if (e.checked) {
	    CheckAll();
	}
	else {
	    ClearAll();
	}
    }

    function Check(e)
    {
	e.checked = true;
	isChecked(e.checked);
    }

    function Clear(e)
    {
	e.checked = false;
	isChecked(e.checked);
    }

    function CheckAll()
    {
var afrm=document.getElementById(lcms_data_form);
	var len = afrm.elements.length;
	for (var i = 0; i < len; i++) {
	    var e = afrm.elements[i];
	    if (e.name == "cid[]") {
		Check(e);
		Highlight(e);
	    }
	}
    }

    function ClearAll()
    {
var afrm=document.getElementById(lcms_data_form);
	var len = afrm.elements.length;
	for (var i = 0; i < len; i++) {
	    var e = afrm.elements[i];
	    if (e.name == "cid[]" ) {
		Clear(e);
		Unhighlight(e);
	    }
	}
    }
	
	    function Highlight(e)
    {
	var r = null;
	if (e.parentNode && e.parentNode.parentNode) {
	    r = e.parentNode.parentNode;
	}
	else if (e.parentElement && e.parentElement.parentElement) {
	    r = e.parentElement.parentElement;
	}
	if (r) {
	    if (r.className == "wbg") {
		r.className = "wbgs";
	    }
		if (r.className == "gbg") {
		r.className = "wgbg";
	    }
	}
    }

    function Unhighlight(e)
    {
	var r = null;
	if (e.parentNode && e.parentNode.parentNode) {
	    r = e.parentNode.parentNode;
	}
	else if (e.parentElement && e.parentElement.parentElement) {
	    r = e.parentElement.parentElement;
	}
	if (r) {
	    if (r.className == "wbgs") {
		r.className = "wbg";
	    }
		if (r.className == "wgbg") {
		r.className = "gbg";
	    }
	}
    }
