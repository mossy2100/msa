<html>

<head>


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

	.H1 {z-index:2; color: white; text-align: left; font-size: 56pt; font-weight: bold; position:absolute; left:70px; top:10px 
		}

	.H2 {z-index:2; color: white; text-align: left; font-size: 20pt; font-weight: bold; position:absolute; left:170px; top:100px; font-style: italic  
		}
		
	.subheading {color: #ff0000; text-align: left; font-family: arial, verdana, sans-serif; font-size: 14pt; font-weight: bold; position: relative; font-style: italic; left:10px; right:810px;
		} 

	.subheading2 {color: black; text-align: left; font-family: arial, verdana, sans-serif; font-size: 10pt; font-weight: bold; position: relative; font-style: italic; left:10px;  right:810px;
		} 

	.bodytext {color: black; text-align: justify; font-family: arial, verdana, sans-serif; font-size: 10pt; position: relative; left:10px; right:810px;
		} 

	.picture {position:relative; top:0px; left:10px; z-index:1
		} 

	div.box {position:relative; width: 780px; border: 0px solid black; background: white; left:10px; right:780px; margin: 0px 10px 0px 0px;
		}

	div.dividing_line {position:relative; width: 780px; border: 0px solid black; left:10px; right:780px; margin: 0px 10px 0px 0px;
		}

		</style>


</head>



<body alink="#ff0000" link="#ff0000" vlink="#ff0000">

<div class="box">

<?php
	if($data=mysql_fetch_assoc($result)) 
		{?> 

<div class="picture">
<img src="/images/amec_header.jpg" alt="panorama" height="162" width="780"  border="0" >
</div>

<!--

	<div class="H1">AMEC2009</div>


	<div class="H2">9th Australian Mars Exploration Conference</div>

-->


<div class="dividing_line"><hr height="1pt"></div>

<center>



<span class="bodytext">

      <a href="#Opening_event">Opening Event</a> :: 
      <a href="#Registration">Registration</a> :: 
      <a href="#Program">Program</a> :: 
      <?php if(!$data['sponsors']){echo"";} else {?><a href="#Sponsors">Sponsors</a> :: <?php } ?>
      <?php if(!$data['banquet']){echo"";} else {?><a href="#Banquet">Banquet</a> :: <?php } ?>
      <?php if(!$data['venue']){echo"";} else {?><a href="#Venue">Venue</a> :: <?php } ?>
      <?php if(!$data['accommodation']){echo"";} else {?><a href="#Accommodation">Accommodation</a> :: <?php } ?>
      <?php if(!$data['travel']){echo"";} else {?><a href="#Travel">Travel</a> :: <?php } ?>
      <?php if(!$data['sponsors']){echo"";} else {?><a href="#Sponsors">Sponsors</a> :: <?php } ?>
     <?php if(!$data['brochure']){echo"";} else {?><a href="#Brochure">Brochure</a> :: <?php } ?>
      <script> email('amec','marssociety.org.au','Inquiries')</script>

</span>
</center>

<br>
<br>

<div class="bodytext">
<i>
<?php if(!$data['introduction']){echo"";} else {?><?PHP echo $data['introduction']; ?><a href="/library/AMEC2009_Flyer.pdf">Download a conference flyer</a> and help spread the word.<?php } ?>
</div>
</i>
<br>
<br>


<div class="subheading"><a name="Opening_event"> Dr Chris McKay Public Lecture</a></div>

 <?php if(!$data['opening_event']){echo"";} 
 else {?>

<div class="bodytext">
The conference will open with a free public lecture by one of the world's leading planetary scientists, <b>Dr Chris McKay</b> of the Space Science Division, <a href="http://www.nasa.gov/centers/ames/home/index.html">NASA Ames</a>.<br></div><br>

<b><div class="subheading2">'<?PHP echo $data['opening_event_title']; ?>' </b></div><br> 

<div class="bodytext">
<b>When</b>:   7.30pm, Friday 17th July<br>

<b>Where</b>:  
Centenary Building, Level 3 - Room 16 (C3-16), University of South Australia, City East Campus, <br>
Cnr North Terrace and Frome Rd, ADELAIDE.  (<a href="/library/AMEC2009_Map.pdf">Download pdf map</a>).</div><br>

<div class="picture">
<img src="/images/chris_mckay.jpg" alt="panorama" height="151" width="130"  border="0" >
</div>

<div class="bodytext">

<?PHP echo $data['opening_event_speaker_bio']; ?>

<br>
<?php } ?></div><br><br>

<?php if(!$data['registration_link']){echo"";} 
 else {?>
<div class="subheading"><a name="Registration">Registration</a></div>
<div class="bodytext">

			<?PHP echo $data['registration_link']; ?> Last minute registration will also be available on the day.  <br></b>
</div>
<br>
<?php } ?>


<div class="subheading"><a name="Program">Program</a></div>

<div class="bodytext">

A full draft program is <a href="/library/AMEC2009_Program.doc">available here</a>.  (This may be subject to last minute revisions).  Confirmed speakers include -<br>

<ul><li><b>Reut Sorek-Abramovich, Brendan Burns and Brett Neilan</b>, <i>Australian Centre for Astrobiology, University of NSW</i><br>
&#39;Temporal Biodiversity of Potential Diazotrophs in Stromatolites, Shark Bay, Western Australia&#39;</li><br>

<li><b>Rosalba Bonaccorsi</b>1,2<b>, Christopher P. McKay</b>1<b>, and the Marte Team</b>, <i>(1) NASA Ames Research Center, Moffett Field CA, (2) SETI Institute - 515 N. Whisman Road - Mountain View, CA</i><br>
&#39;Preservation of Biosignatures in Phyllosilicates vs. Iron-rich Environments as Mars Analogues&#39;</li><br>

<li><b>Jonathan Clarke</b>, <i>Mars Society Australia/Australian Centre for Astrobiology</i><br>
&#39;A Summary of Australian Mars Analogues&#39;<br><br>

<li><b>Mark Gargano</b>, <i>Science Coordinator, St Josephs School, Northam</i><br>
&#39;Student Field Expeditions: Improving Conceptual Understanding in Earth, Planetary and Space Science&#39;</li><br>

<li><b>Brittany Hunter</b>, <i> Iona Presentation College, 33 Palmerston Street WA</i><br>
&#39;Space Science: Developing scientific awareness amongst Middle to Senior School Students&#39;</li><br>

<li><b>Eriita Jones (and Charles Lineweaver)</b>, <i>Research School of Astronomy and Astrophysics, ANU,
Mt Stromlo Observatory</i><br>&#39;To What Extent Does Terrestrial Life &#39;Follow The Water&#39;?&#39;&#39;</li><br>

<li><b>Dr. John Malos and Dr. Jonathon Ralston</b>, <i> Queensland Centre for Advanced Technologies (QCAT), Commonwealth Scientific and Industrial Research Organisation (CSIRO)</i><br>
&#39;Advanced Mining Automation: Space Technology Transfer&#39;</li></ul><br>

</div>
<br>


<?php if(!$data['banquet']){echo"";} 
 else {?>
<div class="subheading"><a name="Banquet">Conference Banquet</a></div>
<div class="bodytext">
<?php echo $data['banquet']; ?></b>
</div>
<br>
<br>
<?php } ?>


<?php if(!$data['venue']){echo"";} 
 else {?>
<div class="subheading"><a name="Venue">Venue</a></div>
<div class="bodytext">
<?php echo $data['venue']; ?></b>
</div>
<br>
<?php } ?>


<?php if(!$data['accommodation']){echo"";} 
 else {?>
<div class="subheading"><a name="Accommodation">Accommodation</a></div>
<div class="bodytext">
<?PHP echo $data['accommodation']; ?></b><br><?php } ?>
</div>
<br>


<span class="subheading"><a name="Travel">Travel</a></span>
<div class="bodytext">
<?php if(!$data['travel']){echo"";} else {?><?PHP echo $data['travel']; ?></b><br><?php } ?>
</div>
<br>
<br>


<div class="dividing_line"><hr height="1pt"></div>
<?php  } ?>
</div>


</body>
</html>



	



