function x() {    return;  }

function DoPrompt(target, action) {  
var elem=document.getElementById(target);
var revisedMessage; 
var currentMessage = elem.value;  
if (action == "url") {    var thisURL = prompt("Enter the URL for the link you want to add.", "http://");   
var thisTitle = prompt("Enter the web site title", "Page Title");
if (thisTitle == null) return;
var urlBBCode = "[url="+thisURL+"]"+thisTitle+"[/url]";
revisedMessage = currentMessage+urlBBCode;   
elem.value=revisedMessage;   
 elem.focus();return;  }  
 if (action == "email") {    
 var thisEmail = prompt("Enter the email address you want to add.", "");
 if (thisEmail == null) return;
 var emailBBCode = "[email]"+thisEmail+"[/email]";
  revisedMessage = currentMessage+emailBBCode;
  elem.value=revisedMessage;
  elem.focus();return;  }  
  if (action == "bold") {    
  var thisBold = prompt("Enter the text that you want to make bold.", "");
  if (thisBold == null) return;
  var boldBBCode = "[b]"+thisBold+"[/b]";
  revisedMessage = currentMessage+boldBBCode;   
   elem.value=revisedMessage;   
    elem.focus();return;  }  
	if (action == "italic") {    
	var thisItal = prompt("Enter the text that you want to make italic.", "");
	if (thisItal == null) return;
	var italBBCode = "[i]"+thisItal+"[/i]";revisedMessage = currentMessage+italBBCode;   
	 elem.value=revisedMessage;
	 elem.focus();  
	   return;  }  if (action == "underline")
	    {  
	     var thisUndl = prompt("Enter the text that you want to be undelined.", "");   
		 if (thisUndl == null) return;
		  var undlBBCode = "[u]"+thisUndl+"[/u]";
		  revisedMessage = currentMessage+undlBBCode;   
		   elem.value=revisedMessage;   
		    elem.focus();return;  }  
			if (action == "image") {   
			 var thisImage = prompt("Enter the URL for the image you want to display.", "http://");
			 if (thisImage == null) return;
			 var imageBBCode = "[img]"+thisImage+"[/img]";   
			  revisedMessage = currentMessage+imageBBCode;   
			   elem.value=revisedMessage;  
			     elem.focus();return;  } 
				  if (action == "quote") {  
				    var quoteBBCode = "[quote]  [/quote]"; 
					   revisedMessage = currentMessage+quoteBBCode; 
					      elem.value=revisedMessage;
						      elem.focus();return;  }  
							  if (action == "code") {  
							    var codeBBCode = "[code]  [/code]";
								revisedMessage = currentMessage+codeBBCode; 
								   elem.value=revisedMessage; 
								      elem.focus();return;  } 
}
