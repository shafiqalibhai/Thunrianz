/** Automatic keywords generation script
 Version 0.1
  Copyright (c) 2007 legolas558
 Released under GNU GPL license

 This script is part of Lanius CMS core
*/
	var max_keywords_length = 250;
	var max_description_length = 250;

	function trim(s) {
		return s.replace(/^\s+/, '').replace(/\s+$/, '');
	}
	
	function _ctak_normalize(s) {
		return trim(
			// remove all tags and whitespaces
			s.replace(/<[^>]*>/g, " ").replace(/&nbsp;/g, " ").replace(/&#160;/g, " ")
			// remove URLs
			.replace(/\w{3,5}:\/\/[^\s]+/g, " ")
			// remove redundant spaces
			.replace(/ +/g, " "))
			// encode highbits
/*			.replace(/[\xc0-\xff]/g, function (str) {
			return '&#'+str.charCodeAt(0)+';';
			})*/ ;
	}
	
	function _auto_getsource(frm) {
		var it=frm.content_introtext;
		var bt=frm.content_bodytext;
		var source=_ctak_normalize(bt.value);
		if (source.length==0)
			source = _ctak_normalize(it.value);
		return source;
	}

	function ak_fill(frm) {
		var k=frm.content_metakey;
		k.value = auto_keywords(_auto_getsource(frm));
	}
	
	function ad_fill(frm) {
		var d=frm.content_metadesc;
		var source = _auto_getsource(frm);
		if (source=="") return;
		d.value = source.substring(0,max_description_length);
	}

// proper autokeywords generation functions begin here

function sortN(a,b)
{return b.w - a.w}

var isie = (navigator.appVersion.indexOf("MSIE")!=-1);

function ArrayFind( a, v, b, s ) {
	for( var i = +b || 0, l = a.length; i < l; i++ ) {
		if( a[i]===v || s && a[i]==v ) { return i; }
	}
	return -1;
}	

function auto_keywords(source) {
	if (source.length==0) return "";
	var words = [];
	source.replace(/[\w\d&;#\xc0-\xff]{2,}/g, function (str) {
		words[words.length] = str;
	});
	if (!words.length) return "";
	var nu_words = [];
	var density = [];
	var wp=0;
	for(var i=0;i<words.length;i++) {
		if (words[i].length==0)
			continue;
		if (isie)
			cond = (ArrayFind(common_words, words[i].toLowerCase())<0);
		else
			cond = (common_words.indexOf(words[i].toLowerCase())<0);
		if (cond) {
			if (isie)
				wp = ArrayFind(nu_words, words[i]);
			else
				wp = nu_words.indexOf(words[i]);
			if (wp < 0) {
				nu_words = nu_words.concat(new Array(words[i]));
				density[nu_words.length-1] = {"i":nu_words.length-1, "w":1};
			} else
				density[wp].w = density[wp].w + 1;
		}
	}
	if (!density.length) return "";
	words = [];
	var keywords = "", nw = "";
	density = density.sort(sortN);
	var ol=0;
	for(i=0;i<density.length;i++) {
		nw = nu_words[density[i].i];
		if (ol+nw.length>max_keywords_length)
			break;
		keywords = keywords+","+nw;
		ol+=nw.length;
	}
	return keywords.substr(1);
}
