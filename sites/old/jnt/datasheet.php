<?php include("includes/header.php"); ?>
<?php include("includes/body_datasheet.php"); ?>
<?php include("includes/title.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/connection.php"); ?>
<?php include("includes/searchbox.php"); ?>


<?php
	$place=$_POST["site"]; 

$SQL="SELECT * FROM records WHERE (place Like '%".$place."%')";  
?>

<?php

		$result=mysql_query($SQL);
	while($data=mysql_fetch_assoc($result))
		{  ?>	

<table width="100%" border="1">
  <tbody>

<tr>
<td width="50%" bgcolor="#ffcc00">LOCALITY</td>

<td rowspan=4 align="left">

<img align="top" id="img<?php echo $data['image_general1']; ?>" 
alt="" 
src="/images/<?php echo $data['image_general1']; ?>" pbshowcaption="true" 
pbcaption=""
class="PopBoxImageSmall" title="Click to magnify/shrink" 
pbshowpopbar="false"
style="width: 143px;
height: 114px;" 
onclick="Pop(this,200,'PopBoxImageLarge');" />

<i><span align="right" style="font-size:11px;">
Click to enlarge</span></i>

</td>
</tr>

<tr>
<td valign="top" width="50%"><b><?php echo $data['place']; ?></b>
</td>
</tr>

<tr>
<td width="50%" bgcolor="#ffcc00">LONGITUDE & LATITUDE
</td>
</tr>

<tr>
<td valign="top" width="50%">

<?php echo $data['longitude1']; ?>
<?PHP if(!$data['longitude2'])
	{echo"";} else 
	{?> - <?php echo $data['longitude2']; echo ""; } ?>, 
<?php echo $data['latitude1']; ?> 
<?PHP if(!$data['latitude2'])
	{echo"";} else 
	{?> - <?php echo $data['latitude2']; echo "<br>"; } ?>

</td>
</tr>



    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAj4G1jvEHsUhSOV4DqiAELxQBm2fDokdck4rk3c1cQWhxmjEUCBQRwFCdU_9hcRTB1WtDq5QqRZ8kzA"
      type="text/javascript"></script>
    <script type="text/javascript">

    //<![CDATA[

    function load() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map"));
	  map.setMapType(G_HYBRID_MAP);
        map.setCenter(new GLatLng(<?php echo $data['geotag']; ?>), 14);

	map.addControl(new GScaleControl());
	map.addControl(new GMapTypeControl());
	map.addControl(new GOverviewMapControl());
	map.addControl(new GLargeMapControl());

	var marker = new GMarker(new GLatLng(<?php echo $data['geotag']; ?> ));
	map.addOverlay(marker);

      }
    }

    //]]>
    </script>


	<tr>
	<td colspan="2">
	<center>

<?PHP if(!$data['geotag'])
	{echo"<i>MAPS GO HERE</i>";} else 
	{ ?>
    <div id="map" style="width: 580px; height: 350px"></div>
 <?php } ?> 

	</center>
	</td>
	</tr>

	<tr>
	<td width="50%" bgcolor="#ffcc00">LOCALITY & OWNERSHIP</td>
	<td width="50%" bgcolor="#ffcc00">ACCESS</td>
	</tr>

	<tr>
	<td valign="top">
	<?php echo $data['locality_and_ownership']; ?>
	<br>
	<br>
	</td>

	<td valign="top">
	<?php echo $data['access']; ?>
	<br>
	<br>
	</td>
	</tr>

	<tr>
	<td width="50%" bgcolor="#ffcc00">LOCAL CONTACTS</td>
	<td width="50%" bgcolor="#ffcc00">NEAREST SERVICES</td>
	</tr>

	<tr>
	<td valign="top">
	<?php echo $data['local_contacts']; ?>
	<br>
	<br>
	</td>

	<td valign="top">
	<?php echo $data['nearest_services']; ?>
	<br>
	<br>
	</td>
	</tr>

  	<tr>
    	<td width="100%" bgcolor="#ffcc00" colspan="2">RISKS</td>
  	</tr>

  	<tr>
    	<td width="100%" colspan="2" valign="top">
	<?php echo $data['risks']; ?><br><br>
	</td>
  	</tr>


	<tr>
	<td width="100%" bgcolor="#ffcc00" colspan="2">TERRAIN</td>
	</tr>

	<tr>
	<td colspan="2" valign="top" >
	<?php echo $data['terrain']; ?><br><br>
	</td>
  	</tr>

	<tr>
	<td width="100%" bgcolor="#ffcc00" colspan="2">GEOLOGY</td>
	</tr>

	<tr>
	<td colspan="2" valign="top" >

<?PHP if(!$data['geology_general'])
	{echo"";} else 
	{?><?php echo $data['geology_general']; echo "<br><br> "; } ?>

<?PHP if(!$data['geology_site1'])
	{echo"";} else 
     	{?>Site 1 - <?php echo $data['geology_site1']; echo "<br><br>";} ?>

<?PHP if(!$data['geology_site2'])
	{echo"";} else 
     	{?>Site 2 - <?php echo $data['geology_site2']; echo "<br><br> ";} ?>

<?PHP if(!$data['geology_site3'])
	{echo"";} else 
     	{?>Site 3 - <?php echo $data['geology_site3']; echo "<br><br> ";} ?>

<?PHP if(!$data['geology_site4'])
	{echo"";} else 
     	{?>Site 4 - <?php echo $data['geology_site4']; echo "<br><br> ";} ?>

<?PHP if(!$data['geology_site5'])
	{echo"";} else 
     	{?>Site 5 - <?php echo $data['geology_site5']; echo "<br><br> ";} ?>
	</td>
	</tr>

	<tr>
	<td width="50%" bgcolor="#ffcc00">CLIMATE</td>
	<td width="50%" bgcolor="#ffcc00">FLORA & FAUNA</td>
	</tr>

	<tr>
	<td valign="top">
	<?php echo $data['climate']; ?>
	<br>
	<br>
	</td>

	<td valign="top">
	<?PHP if(!$data['flora_and_fauna'])
	{echo"";} else 
	{?><?php echo $data['flora_and_fauna']; } ?>
	<br>
	<br>
	</td>
	</tr>

	<tr>
	<td width="100%" bgcolor="#ffcc00" colspan="2">HISTORY</td>
	</tr>

	<tr>
	<td colspan="2" valign="top">
	<?PHP if(!$data['history'])
	{echo"";} else 
	{?><?php echo $data['history']; echo "<br>"; } ?>
	<br>
	</td>
	</tr>

	<tr>
	<td width="100%" bgcolor="#ffcc00" colspan="2">ANALOGUE VALUE</td>
	</tr>

	<tr>
	<td colspan="2" valign="top"><?php echo $data['analogue_value']; ?>
	<br>
	<br>
	</td>
	</tr>

	<tr>
	<td bgcolor="#ffcc00" colspan="2">REFERENCES</td>
	</tr>

<tr>
	<td colspan="2" valign="top">

<?PHP if(!$data['reference1'])
	{echo"";} else 
	{?>(1) <?php echo $data['reference1']; echo "<br>"; } ?>
<?PHP if(!$data['reference2'])
	{echo"";} else 
	{?>(2) <?php echo $data['reference2']; echo "<br>"; } ?>
<?PHP if(!$data['reference3'])
	{echo"";} else 
	{?>(3) <?php echo $data['reference3']; echo "<br>"; } ?>
<?PHP if(!$data['reference4'])
	{echo"";} else 
	{?>(4) <?php echo $data['reference4']; echo "<br>"; } ?>
<?PHP if(!$data['reference5'])
	{echo"";} else 
	{?>(5) <?php echo $data['reference5']; echo "<br>"; } ?>
<?PHP if(!$data['reference6'])
	{echo"";} else 
	{?>(6) <?php echo $data['reference6']; echo "<br>"; } ?>
	<br>
	</td>
	</tr>
	</tbody>
</table>

<?php } ?>

<?php include("includes/footer.php"); ?>