<html>
<head>

<SCRIPT LANGUAGE="JavaScript">
<!-- Hide script from old browsers
     function email(user,site,link) {
          document.write('<a href=\"mailto:' + user + '@' + site + '\">');
          document.write(link + '</a>');
}
// End -->
</SCRIPT>


<link rel="shortcut icon" href="/favicon.ico">


<SCRIPT LANGUAGE="JavaScript" TYPE="text/javascript">
<!--
// Courtesy of SimplytheBest.net - http://simplythebest.net/scripts/
gSlideshowInterval = 5; 
gNumberOfImages = 2;

gImages = new Array(gNumberOfImages);
gImages[0] = "http://marssociety.org.au/amec2008/OnlineButton2008.JPG";
gImages[1] = "http://marssociety.org.au/amec2008/OnlineButton2008_2.JPG";


function canManipulateImages() {
	if (document.images)
		return true;
	else
		return false;
}
function loadSlide(imageURL) {
	if (gImageCapableBrowser) {
		document.slide.src = imageURL;
		return false;
	}
	else {
		return true;
	}
}
function nextSlide() {
	gCurrentImage = (gCurrentImage + 1) % gNumberOfImages;
	loadSlide(gImages[gCurrentImage]);
}
gImageCapableBrowser = canManipulateImages();
gCurrentImage = 0;
setInterval("nextSlide()",gSlideshowInterval * 1000);
// -->
</SCRIPT>

<style>
a.new-site-link:link,
a.new-site-link:focus,
a.new-site-link:active,
a.new-site-link:visited {
  color: #ff9941;
  text-decoration: none;
  font-family: Arial,Helvetica,sans-serif;
}

a.new-site-link:hover {
  color: #ff7600;
}

h1.new-site-link {
  border: solid 5px #ffdea6;
  background-color: white;
  padding: 20px;
  -moz-border-radius:     10px;
  -webkit-border-radius:  10px;
  -khtml-border-radius:   10px;
  border-radius:          10px;
}
</style>


<link rel=stylesheet href="../msa_mainstyle.css" type="text/css">

</head>

<body aLink=white  bgColor class="background" link=#ffcc00 text=white vLink=#ffcc00>

<center>
  
<h1 class='new-site-link'><a class='new-site-link' href='http://marssociety.org.au' target='_top'>This is our old website - visit our new one!</a></h1>
  
<table border=0>
  	<tr>
	<valign=top>
    <td bgColor class="background" width=634>




<a href="/main.php">
<img src="/images/msa_website_banner_plain.jpg" border=0 width=634 height=124>
</a>



</td>
</tr>
<tr>
<td>

</td>
</tr>
</table>
</center>


	<font face=geneva,arial size=2, color=#ffcc00>
    <left>  &nbsp; &nbsp; &nbsp; &nbsp;	
	<i>Branch Pages & Contacts</i></font></A> -<font face=geneva,arial size=2> 

	<A href="/vic.php" target=main>Vic</A> - 
	<A href="/nsw.php" target=main>NSW</A> - 
	<A href="/qld.php" target=main>Qld</A> - 
	<A href="/sa.php" target=main>SA</A> - 
	<A href="/nt.php" target=main>NT</A> - 
	<A href="/wa.php" target=main>WA</A> - 
	<A href="/act.php" target=main>ACT</A> - 
	<A href="/tas.php" target=main>Tas</A> - 
	<A href="http://www.marssociety.org.nz/" target=_top>NZ</A>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	</font>

	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

	<span class="datelines">

<?php
  putenv("TZ=Australia/Melbourne");
  echo "Updated: " . date( "d F, Y.", getlastmod() );
?>



	</span>
<br>

<FONT COLOR="#FFFFFF" FACE="ARIAL" SIZE="1"><br><B><i>

&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

The Mars Society Australia, Inc is an APPROVED RESEARCH INSTITUTE for the purposes of Section 73A of the Income Tax Assessment Act 1936 for undertaking scientific research which is, or may prove to be, of value to Australia.

</B></i><BR><BR></FONT> 
</center>

<hr>
