
<html>

<head>

<script type="text/javascript">

function css_browser_selector(u){var ua = u.toLowerCase(),is=function(t){return ua.indexOf(t)>-1;},g='gecko',w='webkit',s='safari',h=document.getElementsByTagName('html')[0],b=[(!(/opera|webtv/i.test(ua))&&/msie\s(\d)/.test(ua))?('ie ie'+RegExp.$1):is('firefox/2')?g+' ff2':is('firefox/3')?g+' ff3':is('gecko/')?g:/opera(\s|\/)(\d+)/.test(ua)?'opera opera'+RegExp.$2:is('konqueror')?'konqueror':is('chrome')?w+' '+s+' chrome':is('applewebkit/')?w+' '+s+(/version\/(\d+)/.test(ua)?' '+s+RegExp.$1:''):is('mozilla/')?g:'',is('j2me')?'mobile':is('iphone')?'iphone':is('ipod')?'ipod':is('mac')?'mac':is('darwin')?'mac':is('webtv')?'webtv':is('win')?'win':is('freebsd')?'freebsd':(is('x11')||is('linux'))?'linux':'','js'];
 c = b.join(' ');
 h.className += ' '+c; return c;};
 css_browser_selector(navigator.userAgent);
 

</script>


<?php include("includes/connection.php"); ?>

<?php
$SQL="SELECT * FROM conference ORDER BY year DESC";    
$result=mysql_query($SQL); 
$i=0;
?>


<title>9th Australian Mars Exploration Conference, Adelaide, South Australia</title>

<meta name="description" content="9th Australian Mars Exploration Conference, Adelaide, South Australia"><meta name="keywords" content="Mars, exploration, conference, australia, student, discount, space, flight, travel, aliens, astronauts, cosmonauts, NASA, N.A.S.A., USA, Russia, Russian European, agency,RSA, ESA,CSIRO, marsupial, mars-oz, marsskin, mdrs, flashline, vision, starchaser, rockets, ames, research, centre.">


<SCRIPT LANGUAGE="JavaScript">
<!-- Hide script from old browsers
     function email(user,site,link) {
          document.write('<a href=\"mailto:' + user + '@' + site + '\">');
          document.write(link + '</a>');
}
// End -->
</SCRIPT>


		<style type="text/css">

		.ie .H1 {z-index:2; color: white; text-align: left; font-size: 56pt; font-weight: bold; position:absolute; left:60px; top:30px 
		}
		.ie7 .H1 {z-index:2; color: white; text-align: left; font-size: 56pt; font-weight: bold; position:absolute; left:60px; top:30px 
		}
		.ff3 .H1 {z-index:2; color: white; text-align: left; font-size: 56pt; font-weight: bold; position:absolute; left:60px; top:30px 
		}
		.ff2 .H1 {z-index:2; color: white; text-align: left; font-size: 56pt; font-weight: bold; position:absolute; left:60px; top:30px 
		}
		.gecko .H1 {z-index:2; color: white; text-align: left; font-size: 56pt; font-weight: bold; position:absolute; left:60px; top:30px 
		}
		.win.gecko .H1 {z-index:2; color: white; text-align: left; font-size: 56pt; font-weight: bold; position:absolute; left:60px; top:30px 
		}
		.linux.gecko .H1 {
			background-color: pink
		}
		.opera .H1 {
			background-color: green
		}
		.konqueror .H1 {
			background-color: blue
		}
		.safari .H1 {
			background-color: black
		}
		.chrome .H1 {
			background-color: cyan
		}


		.ie .H2 {z-index:2; color: white; text-align: left; font-size: 20pt; font-weight: bold; position:absolute; left:200px; top:120px 
		}
		.ie7 .H2 {z-index:2; color: white; text-align: left; font-size: 20pt; font-weight: bold; position:absolute; left:200px; top:120px 
		}
		.ff3 .H2 {z-index:2; color: white; text-align: left; font-size: 20pt; font-weight: bold; position:absolute; left:200px; top:120px 
		}
		.ff2 .H2 {z-index:2; color: white; text-align: left; font-size: 20pt; font-weight: bold; position:absolute; left:200px; top:120px 
		}
		.gecko .H2 {z-index:2; color: white; text-align: left; font-size: 20pt; font-weight: bold; position:absolute; left:200px; top:120px 
		}
		.win.gecko .H2 {z-index:2; color: white; text-align: left; font-size: 20pt; font-weight: bold; position:absolute; left:200px; top:120px 
		}


.no_js { display: block }
.has_js { display: none }
.js .no_js { display: none }
.js .has_js { display: block }

		</style>




</head>

<body alink="#ff0000" background="AMEC2008.php_files/BGCOLOR.htm" link="#ff0000" vlink="#ff0000">

<?php
	if($data=mysql_fetch_assoc($result)) 
		{?> 

<table align="left" border="0" cellpadding="5" width="800"><tbody>
<tr>
<td>



<img  style="position:relative; top:0; left:0; z-index:1" src="/images/amec_header.jpg" alt="panorama" height="162" width="780"  border="0" >

<div class="has_js">
	<div class="H1">
AMEC2009
</div>
</div>

<div class="has_js">
	<div class="H2">
9th Australian Mars Exploration Conference
</div>
</div>




<hr height="1pt">

<center>



      <font face="Arial" size="2">
      <a href="#Speakers">Program & Speakers</a> :: 
      <?php if(!$data['registration']){echo"";} else {?><a href="#Registration">Registration</a> :: <?php } ?>
      <?php if(!$data['banquet']){echo"";} else {?><a href="#Banquet">Banquet</a> :: <?php } ?>
      <?php if(!$data['venue']){echo"";} else {?><a href="#Venue">Venue</a> :: <?php } ?>
      <?php if(!$data['accommodation']){echo"";} else {?><a href="#Accommodation">Accommodation</a> :: <?php } ?>
      <?php if(!$data['travel']){echo"";} else {?><a href="#Travel">Travel</a> :: <?php } ?>
      <?php if(!$data['sponsors']){echo"";} else {?><a href="#Sponsors">Sponsors</a> :: <?php } ?>
     <?php if(!$data['brochure']){echo"";} else {?><a href="#Brochure">Brochure</a> :: <?php } ?>
      <script> email('amec','marssociety.org.au','Inquiries')</script>

</center>

<br>
<br>

			<?php if(!$data['introduction']){echo"";} else {?><?PHP echo $data['introduction']; ?></b><?php } ?>


 			<?php if(!$data['registration']){echo"";} 
 else {?>
<font color="#ff0000" face="ARIAL" size="4"><a name="Registration"><b><i>Registration</i></b></a></font><a name="Registration"><br><br>
 <font face="ARIAL" size="2">

			<?PHP echo $data['registration']; ?></b><br>
<br>
<br>
<br>
<?php } ?>


<font color="#ff0000" face="ARIAL" size="4"><b><i><a name="Speakers">Program & Speakers</a></i></b><a name="Call_for_papers"></a></font>
<br>
<br>



 			<?php if(!$data['opening_event']){echo"";} 
 else {?>
<a name="Banquet"><b><i>OPENING EVENT - <?PHP echo $data['opening_event']; ?></i></b></a><br><br>
<b><?PHP echo $data['opening_event_speaker']; ?>, </b><i>
<?PHP echo $data['opening_event_speaker_affiliation']; ?></i><br>
<?PHP echo $data['opening_event_speaker_bio']; ?>.<br>

<br>
<br>
<?php } ?>


<br>

 			<?php if(!$data['banquet']){echo"";} 
 else {?>
<font color="#ff0000" face="ARIAL" size="4"><a name="Banquet"><b><i>Conference Banquet</i></b></a></font><br><br>
 <font face="ARIAL" size="2">

			<?PHP echo $data['banquet']; ?></b>
<br>
<br>
<br>
<?php } ?>




<font color="#ff0000" face="ARIAL" size="4"></font></font></a><font face="ARIAL" size="2"><font color="#ff0000" face="ARIAL" size="4"></a><b><i><a name="Venue"></a>Venue</i></b></font>

			<?php if(!$data['venue']){echo"";} else {?><?PHP echo $data['venue']; ?></b><br><?php } ?>

<br>
<br>



 			<?php if(!$data['accommodation']){echo"";} 
 else {?>
<font color="#ff0000" face="ARIAL" size="4"><b><i><a name="Accommodation">Accommodation</a></i></b></font>

<font face="ARIAL" size="2">
<br>
<br>
<?PHP echo $data['accommodation']; ?></b><br><?php } ?>

<br>
<br>
<br>



<font color="#ff0000" face="ARIAL" size="4"><b><i><a name="Travel">Travel</a></i></b></font>

<font face="ARIAL" size="2">
<br>
<br>


			<?php if(!$data['travel']){echo"";} else {?><?PHP echo $data['travel']; ?></b><br><?php } ?>


<br>
<br>
<br>


<font color="#ff0000" face="ARIAL" size="4"><b><i><a name="Sponsors">Sponsors</a></i><a name="Sponsors"></a></b><a name="Sponsors"></a></font>

<a name="Sponsors"><font face="ARIAL" size="2">
<br>
<br>

			<?php if(!$data['sponsors']){echo"";} else {?><?PHP echo $data['sponsors']; ?></b><br><?php } ?>

<br>
<br>

<hr>

<img src="AMEC2008_footer.jpg" alt="panorama" height="66" width="783">

</font></td></tr>




<?php  } ?>



</tbody>

</table>

</body>

</html>



	



