// Modified by legolas558 for Lanius CMS Project
//
// A password quality meter
// Originally written by Gerd Riesselmann
// http://www.gerd-riesselmann.net
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// This program uses code from mozilla.org (http://mozilla.org)

// On start up, initialize the qualiy meter
//addEvent(window, "load", initQualityMeter);

function pb_addEvent(obj, evType, fn) {
	if (obj.addEventListener) {
   		obj.addEventListener(evType, fn, false);
   		return true;
	} else if (obj.attachEvent) {
   		var r = obj.attachEvent("on"+evType, fn);
   		return r;
 	}
   	return false;
}

var G_pb_box_o = null;
var G_pb_o = null;

// chiamare questa funzione in onLoad
function initQualityMeter(box_id, div_id) {
	G_pb_box_o = box_id;
	G_pb_o = div_id;
//	$("#"+div_id).progressBar(1, { showText: false, barImage: 'admin/templates/default/images/but_m.png'} );

//	$("#"+div_id).fadeIn();
	var _pwd = document.getElementById(box_id);
	pb_addEvent(_pwd, "keypress", updateQualityMeter);
	updateQualityMeter();
}

function updateQualityMeter() {
	var quality = getPasswordStrength(document.getElementById(G_pb_box_o));
	setProgressBarValue(quality);
}

// originally based on Mozilla Seamonkey's function
function getPasswordStrength(box_obj) {

	var pw = box_obj.value;
	if (!pw.length) return 0;

	// length of the password, 9 is enough to get full rating
	var pwlength=pw.length;
	if (pwlength>8)
		pwlength=1.0;
	else
		pwlength = pwlength/9;

	// use of numbers in the password, 30% numbers is best
	var numeric_f = pw.replace (/[0-9]/g, "");
	numeric_f = (pw.length - numeric_f.length) / (0.3 * pw.length);

	// use of symbols in the password, 70% is best
	var sym_f = pw.replace (/\W/g, "");
	sym_f = (pw.length - sym_f.length) / (0.7 * pw.length);

	// use of uppercase in the password, 50% is best
	var upper_f = pw.replace (/[A-Z]/g, "");
	upper_f = (pw.length - upper_f.length)/ (0.5 * pw.length);

	var pwstrength = Math.ceil(50 * pwlength + 10 * numeric_f + 15 * sym_f + 25 * upper_f);

	return pwstrength;
}
