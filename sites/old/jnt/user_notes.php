<?php include("includes/header.php"); ?>
<?php include("includes/body.php"); ?>
<?php include("includes/title.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/connection.php"); ?>

</center>

<h1>USER NOTES</h1>

<a href="/jnt/user_notes.php#location_id"> 
Hierarchical Locality Identification</a><br> 
<a href="/jnt/user_notes.php#fields"> 
Fields</a><br> 
<br>


<h2><a name="fields"></a>FIELDS</h2> 

These are some of the fields currently included in the database - there is scope to include more:</p>
<table border="1" width="100%" height="497">

  <tr>
    <td width="16%" height="19" bgcolor="#FFCC00">FIELD</td>
    <td width="16%" height="19" bgcolor="#FFCC00">DATATYPE(S)</td>
    <td width="68%" height="19" bgcolor="#FFCC00">DESCRIPTION</td>
  </tr>
  <tr>
    <td width="16%" height="19">Name</td>

    <td width="16%" height="19">text</td>
    <td width="68%" height="19">Our site identifier</td>
  </tr>
  <tr>
    <td width="16%" height="19">Location</td>
    <td width="16%" height="19">text</td>
    <td width="68%" height="19">Lat and Long range</td>

  </tr>
  <tr>
    <td width="16%" height="19">Ownership</td>
    <td width="16%" height="19">text</td>
    <td width="68%" height="19">Briefly outlines cadastral information</td>
  </tr>
  <tr>

    <td width="16%" height="19">Access</td>
    <td width="16%" height="19">text</td>
    <td width="68%" height="19">Means of access, nearest supplies and town(s).
      Contact details for local contacts etc</td>
  </tr>
  <tr>
    <td width="16%" height="19">Risks</td>
    <td width="16%" height="19">text</td>

    <td width="68%" height="19">Very brief overview of risks to personnel and
      mitigation strategies (e.g. water access points etc.)</td>
  </tr>
  <tr>
    <td width="16%" height="19">Map - Continental</td>
    <td width="16%" height="19">image</td>
    <td width="68%" height="19">Location Map, shows Local Region box on
      continental map</td>
  </tr>

  <tr>
    <td width="16%" height="19">Map - Regional</td>
    <td width="16%" height="19">image</td>
    <td width="68%" height="19">Location Map, shows local region map with towns,
      roads, etc</td>
  </tr>
  <tr>
    <td width="16%" height="19">Map - Local</td>

    <td width="16%" height="19">image</td>
    <td width="68%" height="19">Higher Resolution map(s) showing roads and
      natural features (GIS quality)</td>
  </tr>
  <tr>
    <td width="16%" height="19">Description</td>
    <td width="16%" height="19">text and image(s)</td>
    <td width="68%" height="19">General description of site, including any
      nearby photographs or sketches</td>

  </tr>
  <tr>
    <td width="16%" height="19">Geology</td>
    <td width="16%" height="19">text</td>
    <td width="68%" height="19">Geological description of site and context</td>
  </tr>
  <tr>

    <td width="16%" height="16">Climatic</td>
    <td width="16%" height="16">text and tables</td>
    <td width="68%" height="16">Climatic description of area, with data tables
      of rainfall, temp, humidity etc</td>
  </tr>
  <tr>
    <td width="16%" height="19">Flora &amp; Fauna</td>

    <td width="16%" height="19">text and images</td>
    <td width="68%" height="19">Very brief description of local flora and fauna,
      any risks to personnel</td>
  </tr>
  <tr>
    <td width="16%" height="19">History</td>
    <td width="16%" height="19">text</td>
    <td width="68%" height="19">Very brief general cultural history of site, and
      history of any research undertaken in area</td>

  </tr>
  <tr>
    <td width="16%" height="19">Analogue Value</td>
    <td width="16%" height="19">text and images</td>
    <td width="68%" height="19">Describes analogue value of site and includes GIS and Mars (e.g. MOC) images for comparison of features</td>
  </tr>
</table>
<br>


<h2><a name="location_id"></a>HIERARCHICAL LOCALITY IDENTIFICATION</h2> 
<p>For the purpose of identifying and cataloguing localities, the following categories are proposed:</p> 

<table border="1" width="600">   
<tr>     
<td width="24" align="center" bgcolor="#FFCC00">LC</td>     
<td width="81" align="center" bgcolor="#FFCC00">TERM</td>     

<td width="158" align="center" bgcolor="#FFCC00">RADIUS</td>     
<td width="309" align="center" bgcolor="#FFCC00">DESCRIPTION</td>   
</tr>   
<tr>     
<td width="24" align="center">1</td>
     <td width="81" align="center">Point</td>
     <td width="158" align="center">&lt; 10 m (10<sup>1</sup> m)</td>

     <td width="309">Identifying a survey or GPS point</td>   </tr>
   <tr>
     <td width="24" align="center">2</td>
     <td width="81" align="center">Site</td>
     <td width="158" align="center">&lt; 100 m (10<sup>2</sup> m)</td>

     <td width="309">Identifying a locality with high localised scientific  features of interest (e.g. fossiliferous rock)</td>   </tr>
   <tr>
     <td width="24" align="center">3</td>
     <td width="81" align="center">Location</td>
     <td width="158" align="center">&lt; 1 km (10<sup>3</sup> m)</td>

     <td width="309">A locality containing points of interest or relevant features (e.g. geomorphology)</td>   </tr>
   <tr>
     <td width="24" align="center">4</td>
     <td width="81" align="center">Area</td>
     <td width="158" align="center">&lt; 10 km (10<sup>4</sup> m)</td>

     <td width="309">A locality containing locations of interest or having analogue value (e.g. rover and suit testing)</td>   </tr>
   <tr>
     <td width="24" align="center">5</td>
     <td width="81" align="center">Range</td>
     <td width="158" align="center">&lt; 100 km (10<sup>5</sup> m)</td>

     <td width="309">A locality containing localised sites of interest or having analogue value (e.g. free ranging field simulations)</td>   </tr>   <tr>     <td width="24" align="center">6</td>     <td width="81" align="center">Region</td>     <td width="158" align="center">&lt; 1,000 km (10<sup>6</sup> m)</td>     <td width="309">A region containing multiple regional sites and having       general relevance for Mars analogue field studies.</td>   </tr>   <tr>     <td width="24" align="center">7</td>     <td width="81" align="center">Continent</td>     <td width="158" align="center">&gt; 1,000 km (10<sup>7</sup> m)</td>     <td width="309">For continental identification</td>   </tr> </table> <p>LC is the <i>Locality Category</i>, being the log10 value of the site radius rounded upwards. The radius is order-of-magnitude only and each locality is associated with a rectangular map section bounded by a pair of longitude and latitude values. Localities with LC = 1 can be identified with a unique latitude and longitude pair. All localities with LC &gt;= 2 can be identified by a hierarchical name string, for example a site near the Henbury Craters:</p> <p>Australia-NT_SW-Henbury-Crater_3-Location_C-Site_32-Point_X</p> <p>Figure 1 illustrates the hierarchy scheme. Each locality will have associated with it an exclusive topographical definition.</p> <table border="0" width="53%" cellspacing="0" cellpadding="0">   <tr>     <td width="25%" align="left">LC 1</td>     <td width="2%" align="center">+</td>     <td width="96%" align="center" colspan="7">       <p align="left">North America</td>   </tr>   <tr>     <td width="25%" align="left"></td>     <td width="2%" align="center">-</td>     <td width="96%" align="center" colspan="7">       <p align="left">Australia</td>   </tr>   <tr>     <td width="25%" align="left">LC 2</td>     <td width="2%" align="center"></td>     <td width="2%" align="center">+</td>     <td width="94%" align="left" colspan="6">WA_NW</td>   </tr>   <tr>     <td width="25%" align="left"></td>     <td width="2%" align="center"></td>     <td width="2%" align="center">+</td>     <td width="94%" align="left" colspan="6">WA_N</td>   </tr>   <tr>     <td width="25%" align="left"></td>     <td width="2%" align="center"></td>     <td width="2%" align="center">+</td>     <td width="94%" align="left" colspan="6">NT_SE</td>   </tr>   <tr>     <td width="25%" align="left"></td>     <td width="2%" align="center"></td>     <td width="2%" align="center">-</td>     <td width="94%" align="left" colspan="6">NT_SW</td>   </tr>   <tr>     <td width="25%" align="left">LC 3</td>     <td width="2%" align="center"></td>     <td width="2%" align="center"></td>     <td width="2%" align="center">+</td>     <td width="92%" align="left" colspan="5">Bloods_Range</td>   </tr>   <tr>     <td width="25%" align="left"></td>     <td width="2%" align="center"></td>     <td width="2%" align="center"></td>     <td width="2%" align="center">+</td>

     <td width="92%" align="left" colspan="5">Lake_Amadeus</td>
   </tr>
   <tr>
     <td width="25%" align="left"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center">-</td>
     <td width="92%" align="left" colspan="5">Henbury</td>

   </tr>
   <tr>
     <td width="25%" align="left">LC 4</td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center">+</td>
     <td width="90%" align="left" colspan="4">Crater_1</td>

   </tr>
   <tr>
     <td width="25%" align="left"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center">+</td>
     <td width="90%" align="left" colspan="4">Crater_2</td>

   </tr>
   <tr>
     <td width="25%" align="left"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center">-</td>
     <td width="90%" align="left" colspan="4">Crater_3</td>

   </tr>
   <tr>
     <td width="25%" align="left">LC 5</td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center">+</td>

     <td width="88%" align="left" colspan="3">Location_A</td>
   </tr>
   <tr>
     <td width="25%" align="left"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>

     <td width="2%" align="center">+</td>
     <td width="88%" align="left" colspan="3">Location_B</td>
   </tr>
   <tr>
     <td width="25%" align="left"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>

     <td width="2%" align="center"></td>
     <td width="2%" align="center">-</td>
     <td width="88%" align="left" colspan="3">Location_C</td>
   </tr>
   <tr>
     <td width="25%" align="left">LC 6</td>
     <td width="2%" align="center"></td>

     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center">+</td>
     <td width="86%" align="left" colspan="2">Site_01</td>
   </tr>
   <tr>

     <td width="25%" align="left"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center">+</td>
     <td width="86%" align="left" colspan="2">Site_04</td>

   </tr>
   <tr>
     <td width="25%" align="left"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center">-</td>

     <td width="86%" align="left" colspan="2">Site_32</td>
   </tr>
   <tr>
     <td width="25%" align="left">LC 7</td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>

     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center">-</td>
     <td width="84%" align="left">Point_X</td>
   </tr>
   <tr>
     <td width="25%" align="left"></td>
     <td width="2%" align="center"></td>

     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center"></td>
     <td width="2%" align="center">-</td>
     <td width="84%" align="left">Point_Y</td>
   </tr>

 </table>
 <p>This scheme is not yet implemented in this version.</p>

<?php include("includes/footer.php"); ?>