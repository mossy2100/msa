<?php include("includes/header.php"); ?>
<?php include("includes/body.php"); ?>
<?php include("includes/title.php"); ?>
<?php include("includes/menu.php"); ?>
<?php include("includes/connection.php"); ?>


<table border="0" align="center">
<center>
<br>
This database is a catalogue of 

<?php 
$countresult=mysql_query("SELECT * FROM records");
$num_rows=mysql_num_rows($countresult);
$dbsizecount=($num_rows); 
echo ($num_rows); 
?> 

places in Australia we have identified as analogous in one or more ways to the surface of Mars. It is hoped it will be a useful tool for  researchers. 
<br>
<br>


<?php include("includes/searchbox.php"); ?>


<br>
<br>

<center>

(Click on a pointer for more information about each place).

<br>
<br>

  <div id="map" style="width: 480px; height: 420px"></div>

    <noscript><b>JavaScript must be enabled in order for you to use Google Maps.</b> 
      However, it seems JavaScript is either disabled or not supported by your browser. 
      To view Google Maps, enable JavaScript by changing your browser options, and then 
      try again.
    </noscript>
 

    <script type="text/javascript">
    //<![CDATA[
    
    if (GBrowserIsCompatible()) { 
   
      function createMarker(point,html) {
        var marker = new GMarker(point);
        GEvent.addListener(marker, "click", function() {
          marker.openInfoWindowHtml(html);
        });
        return marker;
      }

      var map = new GMap2(document.getElementById("map"));
      map.addControl(new GLargeMapControl());
      map.addControl(new GMapTypeControl());
      map.setCenter(new GLatLng(-26.352498, 133.857422),4);
    
      // Set up markers with info windows 
    




<?php

$SQL="SELECT * FROM records";
	$pointerresult=mysql_query($SQL);
	while($pointerdata=mysql_fetch_assoc($pointerresult))
		{  ?>

      var point = new GLatLng(<?php echo $pointerdata['geotag']; ?>);
      var marker = createMarker(point,'<b><?php echo $pointerdata['place']; ?></b><br><div style="font-size:10px;"><?php echo $pointerdata['terrain']; ?></div>');
      map.addOverlay(marker);


<?php }?>

}
    




    
    else {
      alert("Sorry, the Google Maps API is not compatible with this browser");
    }


    //]]>
    </script>


</table>

<br>

</td>
</tr>
</table>

</body>
</html>