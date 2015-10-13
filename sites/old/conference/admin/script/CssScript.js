function loadStyleSheet (doc, sheetURL, mediaTypes) {
  	var link;
  	var x;
  
	document.body.style.background = "";
	document.body.style.color = "";
	
	if (doc.createElement && (link = doc.createElement('link'))) {
		link.rel = 'stylesheet';
		link.href = "../stylesheets/" + sheetURL;
		link.type = 'text/css';
		if (mediaTypes) {
		  link.media = mediaTypes;
		}
		var head = doc.getElementsByTagName('head')[0];
		if (head) {
		  head.appendChild(link);
		}
	 }
	x=document.getElementById(sheetURL);
	x.selected = true;
}

function makeDisable(setNoneVar)
{
	var x=document.getElementById(setNoneVar);
	x.disabled=true;
	x.style.display = 'none';
}

function makeEnable(setNoneVar)
{
var x=document.getElementById(setNoneVar)
x.disabled=false
x.style.display = '';
}