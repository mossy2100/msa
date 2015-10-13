<div id='jnt-map'></div>

<noscript><b>JavaScript must be enabled in order for you to use Google Maps.</b> 
  However, it seems JavaScript is either disabled or not supported by your browser. 
  To view Google Maps, enable JavaScript by changing your browser options, and then 
  try again.
</noscript>

<script src='http://maps.googleapis.com/maps/api/js?sensor=false&language=en&region=AU&v=3.6' type='text/javascript'></script>
<script type='text/javascript'>
//<![CDATA[

var jntMap;
var points = <?php echo format_json(json_encode(jQuerypoints)); ?>;
var latMin, lngMin, latMax, lngMax;

function createMarker(point) {
  var marker = new google.maps.Marker({
    map: jntMap,
    position: new google.maps.LatLng(point.lat, point.lng),
    title: point.place
  });
  
  var infowindow = new google.maps.InfoWindow({
    content: point.html
  });

  google.maps.event.addListener(marker, 'click', function() {
    infowindow.open(jntMap, marker);
  });
}

/**
 * Find the average lat and lng of the points, to be the map centre.
 */
function findCentre() {
  var nPoints = points.length;
  var latCentre, lngCentre;
  
  if (nPoints) {
    var lat, lng;

    for (var i in points) {
      lat = parseFloat(points[i].lat);
      lng = parseFloat(points[i].lng);
      
      if (latMin === undefined || lat < latMin) {
        latMin = lat;
      }
      if (lngMin === undefined || lng < lngMin) {
        lngMin = lng;
      }
      if (latMax === undefined || lat > latMax) {
        latMax = lat;
      }
      if (lngMax === undefined || lng > lngMax) {
        lngMax = lng;
      }
    }
    
    latCentre = (latMin + latMax) / 2;
    lngCentre = (lngMin + lngMax) / 2;
  }
  else {
    // Default:
    latCentre = -28;
    lngCentre = 136;
  }
  
  return new google.maps.LatLng(latCentre, lngCentre);
}

function initMap() {
  // Create map:
  jntMap = new google.maps.Map(jQuery('#jnt-map').get(0), {
    center: findCentre(),
    zoom: 11,
    mapTypeId: google.maps.MapTypeId.HYBRID,
    mapTypeControl: true
  });
  
  // Zoom map to contain markers:
  var nPoints = points.length
  if (nPoints > 1) {
    var sw = new google.maps.LatLng(latMin, lngMin);
    var ne = new google.maps.LatLng(latMax, lngMax);
    var llb = new google.maps.LatLngBounds(sw, ne);
    jntMap.fitBounds(llb);
  }

//  zoomToFit();

  // Add marker points:
  for (var i in points) {
//    alert(points[i].lat);
    createMarker(points[i]);
  }
}

jQuery(initMap);

//]]>
</script>
