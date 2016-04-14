<?php

$domain = explode(".",$_SERVER[HTTP_HOST]);
$project = $domain[0];
$conf_file = "project/".$project."/conf.php";

if( !file_exists($conf_file) )
{
	$project = "default";
	$conf_file = "project/default/conf.php";
}

include $conf_file;


?>
<!DOCTYPE html>
<html>
<head>
  <title><?php echo $conf['title'] ?> - kartenkarte.de</title>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <link rel="stylesheet" href="css/leaflet.css" />
  <link rel="stylesheet" href="css/MarkerCluster.css" />
  <link rel="stylesheet" href="css/MarkerCluster.Default.css" />
  <link rel="stylesheet" href="css/L.Control.Locate.min.css" />
  <link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css" />
  <link rel="stylesheet" href="css/bootstrap.min.css" />

  <script src="js/jquery-min.js"></script>
  <script src="js/leaflet.js"></script>
  <script src="js/icons.js"></script>
  <script src="js/leaflet.markercluster.js"></script>
  <script src="js/L.Control.Locate.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/project.js"></script>
  <style type="text/css">
    #map {
      position: absolute;
      height: 100%;
      width: 100%;
    }
    body {
      margin: 0;
    }
  </style>
  <script type="text/javascript">
  var marker = {};
  var markers;
  var map;

  function getUrl(url) {
    if (!url.match(/^[a-zA-Z]+:\/\//))
    {
      url = 'http://' + url;
    }
    return url;
  }
 
  function makeMarkers() {
    marker.osmJson.forEach(function (node) {
    var marker2;
    if (node.type == 'node') {
      var title = ""; 
      if (typeof generate_title === "function") {
        title = generate_title(node);
      }

      var icon = icon_normal; 
      if (typeof generate_icon === "function") {
        icon = generate_icon(node);
      }

      var popup = ""; 
      if (typeof generate_popup === "function") {
        popup = generate_popup(node);
      } else {
        if( typeof node.tags.name !== "undefined" ) popup += node.tags.name;
        if( typeof node.tags['website'] !== "undefined" ) popup += "<br/><a href=\"" + getUrl( node.tags['website'] ) + "\" target=\"_blank\">Website</a><br/>"; 
        popup += "<br/><a href=\"http://www.openstreetmap.org/edit?node=" + node.id + "\">edit</a>";
      }
      L.marker([node.lat, node.lon], {"title": title, "icon": icon}).bindPopup(popup).addTo(markers);
      }
    });
  };

  function mapmap() {
    ModalTimeout = window.setTimeout("$('#waitModal').modal('show');", 1000);

    // create a map in the "map" div, set the view to a given place and zoom
    map = L.map('map').setView(<?php echo "[".$conf['lat'].", ".$conf['lon']."], ".$conf['zoom']; ?>);

   // add an OpenStreetMap tile layer
    L.tileLayer('<?php echo $conf['tile_url']; ?>', {
        attribution: '<?php echo $conf['tile_attribution']; ?>'
    }).addTo(map);

    // Marker Clusters
    markers = new L.MarkerClusterGroup({showCoverageOnHover: false, maxClusterRadius: 40});
    $.ajax({
      url : 'data/<?php echo $project;?>.json',
      type : 'GET',
      success : function (data) {
        marker.osmJson = data.elements;
        makeMarkers();
      }
    });
    map.addLayer(markers);
    map.on('layeradd', function(){
      window.clearTimeout(ModalTimeout);
      $('#waitModal').modal('hide');
    });

    // Locate Button
    L.control.locate().addTo(map);
  }
  </script>
  </head>
  <body onload="mapmap()">
  <div class="modal fade" id="waitModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <?php echo $conf['loading_txt']; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="mapsmap" tabindex="-1" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4>Karten auf kartenkarte.org</h4>
          <ul>
          <?php
$projects= scandir("project"); 
foreach($projects as $project) 
{
	if(file_exists("project/".$project."/conf.php") && file_exists("data/".$project.".json"))
	{
		echo "<li><a href=\"http://$project.kartenkarte.org\">$project</a></li>\n";
	}
}
          ?>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div id="map"></div>
</body>
