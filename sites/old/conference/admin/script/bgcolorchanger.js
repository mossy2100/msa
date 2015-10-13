// =============================== //
// Background Color Changer        //
// v1.0 - Feb 12, 2005             //
// ------------------------------- //
// Written by Lloyd Hassell        //
// Website: lloydhassell.com       //
// Email: lloydhassell@hotmail.com //
// =============================== //

// INITIALIZATION:

bgColorChanger = new Object();

// CONFIGURATION:

bgColorChanger.blankImgSrc = '../images/BGColorPickerblank.gif';

// MAIN:

bgColorChanger.cellWidth = 15;
bgColorChanger.cellHeight = 15;

bgColorChanger.isLoaded = false;

bgColorChanger.hexList = new Array('FF','CC','99','66','33','00');

var IE = document.all?true:false
if (!IE) document.captureEvents(Event.MOUSEMOVE)
document.onmousemove = getMouseXY;
var tempX = 0
var tempY = 0
var colourChoice

function getMouseXY(e) {
  if (IE) { // grab the x-y pos.s if browser is IE
    tempX = event.clientX + document.body.scrollLeft
    tempY = event.clientY + document.body.scrollTop
  } else {  // grab the x-y pos.s if browser is NS
    tempX = e.pageX
    tempY = e.pageY
  }  
  // catch possible negative values in NS4
  if (tempX < 0){tempX = 0}
  if (tempY < 0){tempY = 0}  
  // show the position values in the form named Show
  // in the text fields named MouseX and MouseY
  // document.form1.x.value = tempX
  // document.form1.y.value = tempY
  return true
}
function loadBgColorChanger() {
   bgColorChanger.boxWidth = (bgColorChanger.cellWidth * 36) + 37;
   bgColorChanger.boxHeight = (bgColorChanger.cellHeight * 6) + 25;
   bgColorChanger.layerObj = addLayer('bgColorChangerLyr');
   var htmlStr = getTableTag(0,0,0) + '<tr><td bgcolor="#000000">';
   htmlStr += getTableTag(0,3,0,'100%') + '<tr><td><font color="#FFFFFF" face="verdana,arial" size="1">&nbsp;Select a new Colour</font></td>';
   htmlStr += '<td align="right"><font color="#FFFFFF" face="verdana,arial" size="1"><a style="color:red;" href="javascript:closeBgColorChanger();">Close</a>&nbsp;</font></td></tr></table>';
   htmlStr += '</td></tr><tr><td bgcolor="#000000">';
   htmlStr += getTableTag(0,1,0);
   for (var colorLoop = 0; colorLoop < 216; colorLoop++) {
      if (colorLoop % 36 == 0) htmlStr += '<tr>';
      var hexValue = bgColorChanger.hexList[Math.floor(colorLoop/36)] + '' + bgColorChanger.hexList[Math.floor(colorLoop/6)%6] + '' + bgColorChanger.hexList[colorLoop%6];
      htmlStr += '<td bgcolor="#' + hexValue + '">';
      htmlStr += '<a href="javascript:setColor(\'' + hexValue + '\');"';
      htmlStr += 'onMouseOver="javascript:window.status = \'' + hexValue + '\'; return true;" ';
      htmlStr += 'onMouseOut="javascript:window.status = window.defaultStatus; return true;">';
      htmlStr += '<img src="' + bgColorChanger.blankImgSrc + '" width="' + bgColorChanger.cellWidth + '" height="' + bgColorChanger.cellHeight + '" border="0"></a></td>';
      if (colorLoop % 36 == 35) htmlStr += '</tr>';
      }
   htmlStr += '</table></td></tr></table>';
   setLayerHTML(bgColorChanger.layerObj,htmlStr);
   bgColorChanger.isLoaded = true;
   }

function openBgColorChanger(choice) {
	colourChoice = choice;
   if (dyn) {
      if (!bgColorChanger.isLoaded) loadBgColorChanger();
      var posX = tempX - (bgColorChanger.boxWidth/2);//(getWinWidth() - bgColorChanger.boxWidth)  / 2;
      var posY = tempY - (bgColorChanger.boxHeight+20);//(getWinHeight() - bgColorChanger.boxHeight) / 2;
      moveLayerTo(bgColorChanger.layerObj, posX, posY);//moveLayerTo(bgColorChanger.layerObj,getDocScrollLeft() + posX,getDocScrollTop() + posY);
      showLayer(bgColorChanger.layerObj);
      }
   }

function setColor(COLOR) {
	if(colourChoice ==  'bgcolor'){
		document.body.style.background = "#" + COLOR;
		document.form1.BackgroundColor.value = "#" + COLOR;
	}
	else{
		document.body.style.color = "#" + COLOR;
		document.form1.FontColor.value = "#" + COLOR;
	}
  
   hideLayer(bgColorChanger.layerObj);
   }

function resumeDefaultColor(){
	
	document.body.style.background = document.form1.BackgroundColor.value;
	document.body.style.color = document.form1.FontColor.value;
}

function setListToDefaultColor(){
	var x;
	
	x=document.getElementById("CommentStyleDefault.css");
	x.selected = true;
}

function closeBgColorChanger() {
   hideLayer(bgColorChanger.layerObj);
}