/*	Midas/MSHTML common javascript
	@author legolas558

	contains parts of code by
*/

/*    function aggiungiLink(){
      var linkURL = prompt("Inserisci il link da aggiungere:", "");
      AttivaFrame("editArea").execCommand("createLink", false, linkURL);
    }    
	 function vediCodice(){
	   var contenuto = AttivaFrame("editArea").body.innerHTML;
      document.getElementById("codice").innerHTML = contenuto.replace(/</g,"&lt;");
    }
*/

function PulisciCodice(contenuto){
		contenuto = contenuto.replace(/<br\>/gi,"<br/>");
		contenuto = contenuto.replace(/(<p\>)(.*)(<\/p\>)/gi,"<p>$2</p>");
		contenuto = contenuto.replace(/<\a/gi,"<a");
		contenuto = contenuto.replace(/<\/a\>/gi,"</a>");
		contenuto = contenuto.replace(/<\div\>/gi,"<div>");
		contenuto = contenuto.replace(/<\/div\>/gi,"</div>");
		contenuto = contenuto.replace(/(<strong\>)(.*)(<\/strong\>)/gi,"<span style=\"font-style: bold;\">$2</span>");
		contenuto = contenuto.replace(/(<ul\>)(.*)(<\/ul\>)/gi,"<ul>$2</ul>");
		contenuto = contenuto.replace(/(<li\>)(.*)(<\/li\>)/gi,"<li>$2</li>");
		//internet explorer	
      contenuto = contenuto.replace(/(<font )(color)(=)(#?([A-Fa-f0-9]){3}(([A-Fa-f0-9]){3})?)(>)(.*)(<\/font\>)/gi,"<span style=\"$2:$4;\">$9</span>"); 
		contenuto = contenuto.replace(/(<p )(align)(=)([A-Za-z]*)(>)(.*)(<\/p\>)/gi,"<div style=\"text-align: $4;\">$6</div>");
		contenuto = contenuto.replace(/(<em\>)(.*)(<\/em\>)/gi,"<span style=\"font-style: italic;\">$2</span>");
		//opera
		contenuto = contenuto.replace(/(<font )(color)(=\")(#?([A-Fa-f0-9]){3}(([A-Fa-f0-9]){3})?)(\")(>)(.*)(<\/font\>)/gi,"<span style=\"$2:$4;\">$10</span>"); 
		contenuto = contenuto.replace(/(<div )(align)(=\")([A-Za-z]*)(\")(>)(.*)(<\/div\>)/gi,"<div style=\"text-align:$4;\">$7</div>");		
		contenuto = contenuto.replace(/(<i\>)(.*)(<\/i\>)/gi,"<span style=\"font-style: italic;\">$2</span>");

		return contenuto;
}

function NativeSave(ctl_id, cleanup) {
	var fi = document.getElementById(ctl_id);
	var html;
	
	var contenuto = AttivaFrame(ctl_id).body.innerHTML;
	if (cleanup)
		fi.value = PulisciCodice(contenuto);
	else
		fi.value = contenuto;
}

function viewsource(ctl_id, source) {
	var html;
//	var ctl = document.getElementById("if_"+ctl_id);
	var ctl = AttivaFrame(ctl_id);
	var contenuto = ctl.body.innerHTML;
	if (source) {
		document.getElementById("toolbar1").style.visibility="hidden";
		document.getElementById("toolbar2").style.visibility="hidden";	
		document.getElementById("toolbar3").style.visibility="hidden";
		ctl.body.innerHTML = contenuto.replace(/>/g, "&gt;").replace(/</g,"&lt;");
	} else {
		document.getElementById("toolbar1").style.visibility="visible";
		document.getElementById("toolbar2").style.visibility="visible";	
		document.getElementById("toolbar3").style.visibility="visible";	
		ctl.body.innerHTML = contenuto.replace(/&lt;/g,"<").replace(/&gt;/g, ">");
	}
}

var command = "";

function tbmousedown(e)
{
	var evt = e ? e : window.event; 

	this.firstChild.style.left = 2;
	this.firstChild.style.top = 2;
	this.style.border="inset 2px";
	if (evt.returnValue) {
		evt.returnValue = false;
	} else if (evt.preventDefault) {
		evt.preventDefault( );
	} else {
		return false;
	}
}

function tbmouseup()
{
	this.firstChild.style.left = 1;
	this.firstChild.style.top = 1;
	this.style.border="outset 2px";
}

function tbmouseout()
{
	this.style.border="solid 2px #C0C0C0";
}

function tbmouseover()
{
	this.style.border="outset 2px";
}

	function insertNodeAtSelection(win, insertNode)
	{
			// get current selection
			var sel = win.getSelection();

			// get the first range of the selection
			// (theres almost always only one range)
			var range = sel.getRangeAt(0);

			// deselect everything
			sel.removeAllRanges();

			// remove content of current selection from document
			range.deleteContents();

			// get location of current selection
			var container = range.startContainer;
			var pos = range.startOffset;

			// make a new range for the new selection
			range=document.createRange();

			if (container.nodeType==3 && insertNode.nodeType==3) {

				// if we insert text in a textnode, do optimized insertion
				container.insertData(pos, insertNode.nodeValue);

				// put cursor after inserted text
				range.setEnd(container, pos+insertNode.length);
				range.setStart(container, pos+insertNode.length);

			} else {


				var afterNode;
				if (container.nodeType==3) {

					// when inserting into a textnode
					// we create 2 new textnodes
					// and put the insertNode in between

					var textNode = container;
					container = textNode.parentNode;
					var text = textNode.nodeValue;

					// text before the split
					var textBefore = text.substr(0,pos);
					// text after the split
					var textAfter = text.substr(pos);

					var beforeNode = document.createTextNode(textBefore);
					afterNode = document.createTextNode(textAfter);

					// insert the 3 new nodes before the old one
					container.insertBefore(afterNode, textNode);
					container.insertBefore(insertNode, afterNode);
					container.insertBefore(beforeNode, insertNode);

					// remove the old node
					container.removeChild(textNode);

				} else {

					// else simply insert the node
					afterNode = container.childNodes[pos];
					container.insertBefore(insertNode, afterNode);
				}

				range.setEnd(afterNode, 0);
				range.setStart(afterNode, 0);
			}

			sel.addRange(range);
	};

function getOffsetTop(elm) {

	var mOffsetTop = elm.offsetTop;
	var mOffsetParent = elm.offsetParent;

	while(mOffsetParent){
		mOffsetTop += mOffsetParent.offsetTop;
		mOffsetParent = mOffsetParent.offsetParent;
	}
 
	return mOffsetTop;
}

function getOffsetLeft(elm) {

	var mOffsetLeft = elm.offsetLeft;
	var mOffsetParent = elm.offsetParent;

	while(mOffsetParent){
		mOffsetLeft += mOffsetParent.offsetLeft;
		mOffsetParent = mOffsetParent.offsetParent;
	}
 
	return mOffsetLeft;
}

function general_tbclick(ctl_id, obj) {
	if ((obj.id == "forecolor") || (obj.id == "hilitecolor")) {
		parent.command = obj.id;
		buttonElement = document.getElementById(obj.id);
		document.getElementById("colorpalette_"+ctl_id).style.left = getOffsetLeft(buttonElement);
		document.getElementById("colorpalette_"+ctl_id).style.top = getOffsetTop(buttonElement) + buttonElement.offsetHeight;
		document.getElementById("colorpalette_"+ctl_id).style.visibility="visible";
	} else if (obj.id == "createlink") {
		var szURL = prompt("Enter a URL:", "http://");
		if ((szURL != null) && szURL.length) {
			applicaComando(ctl_id, "CreateLink",false,szURL);
		}
	} else if (obj.id == "createimage") {
		imagePath = prompt('Enter Image URL:', 'http://');
		if ((imagePath != null) && (imagePath != "")) {
			applicaComando(ctl_id, "InsertImage", false, imagePath);
		}
	} else if (obj.id == "createtable") {
		if (!_has_ctdoc) {
			alert("Not supported by your browser");
			return;
		}
		e = document.getElementById("if_"+ctl_id);
		rowstext = prompt("enter rows", 2);
		colstext = prompt("enter cols", 3);
		rows = parseInt(rowstext);
		cols = parseInt(colstext);
		if ((rows > 0) && (cols > 0)) {
			table = e.contentWindow.document.createElement("table");
			table.setAttribute("border", "1");
			table.setAttribute("cellpadding", "2");
			table.setAttribute("cellspacing", "2");
			tbody = e.contentWindow.document.createElement("tbody");
			for (var i=0; i < rows; i++) {
				tr =e.contentWindow.document.createElement("tr");
				for (var j=0; j < cols; j++) {
					td =e.contentWindow.document.createElement("td");
					br =e.contentWindow.document.createElement("br");
					td.appendChild(br);
					tr.appendChild(td);
				}
				tbody.appendChild(tr);
			}
			table.appendChild(tbody);			
			insertNodeAtSelection(e.contentWindow, table);
		}
	} else {
		applicaComando(ctl_id, obj.id, false, null);
	}
}

function NativeSelect(ctl_id, selectname) {
	var cursel = document.getElementById(selectname).selectedIndex;
	/* First one is always a label */
	if (cursel != 0) {
		var selected = document.getElementById(selectname).options[cursel].value;
		applicaComando(ctl_id, selectname, false, selected);
		document.getElementById(selectname).selectedIndex = 0;
	}
	document.getElementById("if_"+ctl_id).contentWindow.focus();
}

function dismisscolorpalette(ctl_id)
{
	document.getElementById("colorpalette_"+ctl_id).style.visibility="hidden";
}

function InitToolbarButtons(thediv, ctl_id) {
	var kids = thediv.getElementsByTagName("DIV");
	for (var i=0; i < kids.length; i++) {
		if (kids[i].className == "ne_imagebutton") {
			kids[i].onmouseover = tbmouseover;
			kids[i].onmouseout = tbmouseout;
			kids[i].onmousedown = tbmousedown;
			kids[i].onmouseup = tbmouseup;
			kids[i].onclick = eval('tbclick_'+ctl_id);
		}
	}
}

// false for IE
var _has_ctdoc = null;

function AttivaFrame(iFrameID){
	// initialize on demand
	iFrameID = "if_"+iFrameID;
	if (_has_ctdoc === null) {
		if (document.getElementById(iFrameID).contentDocument)
			_has_ctdoc = true;
		else
			_has_ctdoc = false;
	}
	if (_has_ctdoc){  
		//Mozilla
		return document.getElementById(iFrameID).contentDocument;
	} else {
		//Internet Explorer
		return document.frames[iFrameID].document;
	}
}

function applicaComando(ctl_id, cmdStr, secparm, valCmdStr){
		if (!_has_ctdoc){
		   switch(valCmdStr){
			   case "h1":
				   valCmdStr = "heading 1";
					break;
			   case "h2":
				   valCmdStr = "heading 2";
					break;
			   case "h3":
				   valCmdStr = "heading 3";
					break;
			   case "p":
				   valCmdStr = "paragraph";
					break;
			}
		}
      AttivaFrame(ctl_id).execCommand(cmdStr,secparm,valCmdStr);
    } 

function NativeStart(ctl_id) {
	AttivaFrame(ctl_id).designMode = "On";
	try {
		applicaComando(ctl_id, "undo", false, null);
//		document.getElementById("if_"+ctl_id).contentWindow.document.execCommand("undo", false, null);
	}	catch (e) {
		alert("Native Editor not supported by your browser.");
		return;
	}

	InitToolbarButtons(document.getElementById("div_"+ctl_id), ctl_id);
	var ctl2 = document.getElementById(ctl_id);
		try {
			AttivaFrame(ctl_id).body.innerHTML = ctl2.value;
			return;
		} catch(e) { }
		alert("Please click OK to allow your browser load the text into area "+ctl_id+((AttivaFrame(ctl_id).body!=null) ? '' : '')+"\n\nThis is a browser bug");
		AttivaFrame(ctl_id).body.innerHTML = ctl2.value;

//TODO: fix the below
/*	if (document.addEventListener) {
		document.addEventListener("mousedown", dismisscolorpalette, true);
		document.getElementById("if_"+ctl_id).contentWindow.document.addEventListener("mousedown", dismisscolorpalette, true);
		document.addEventListener("keypress", dismisscolorpalette, true);
		document.getElementById("if_"+ctl_id).contentWindow.document.addEventListener("keypress", dismisscolorpalette, true);
	} else if (document.attachEvent) {
		document.attachEvent("mousedown", dismisscolorpalette, true);
		document.getElementById("if_"+ctl_id).contentWindow.document.attachEvent("mousedown", dismisscolorpalette, true);
		document.attachEvent("keypress", dismisscolorpalette, true);
		document.getElementById("if_"+ctl_id).contentWindow.document.attachEvent("keypress", dismisscolorpalette, true);
	} */
}
