<?php
include("../../config/settings.inc.php");
$network = isset($_GET['network']) ? $_GET['network'] : 'IA_ASOS';
$multi = isset($_GET["multi"]);
header("Content-type: application/javascript");
$uri = sprintf("%s/geojson/network.php?network=%s&c=%s", BASEURL, $network,
	time() );

echo <<<EOF
var map, selectedFeature, selectControl;

function selectAllStations(){
  $("#olstation").find('option').attr('selected','selected');
}

function cb_siteOver(feature){
  station = feature.attributes.sid;
  selectedFeature = feature;
  $('select[name="station"]').find("option[value="+station+"]").prop("selected", "selected");
  document.getElementById("sname").innerHTML = feature.attributes.sname;
  popup = new OpenLayers.Popup('chicken', 
              feature.geometry.getBounds().getCenterLonLat(),
              new OpenLayers.Size(200,20),
          "<div style='font-size:1em'>" + station +" "+feature.attributes.sname +"</div>",
              true);
  feature.popup = popup;
  map.addPopup(popup);
};

function cb_siteOut(feature){ 
    map.removePopup(feature.popup);
  document.getElementById("sname").innerHTML = "No Site Selected";
    feature.popup.destroy();
    feature.popup = null;
};


function init(){
  // Build Map Object
  map = new OpenLayers.Map( 'map',{
        projection: new OpenLayers.Projection('EPSG:900913'),
        displayProjection: new OpenLayers.Projection('EPSG:4326'),
        units: 'm',
        wrapDateLine: false,
        numZoomLevels: 18,
        maxResolution: 156543.0339,
        maxExtent: new OpenLayers.Bounds(-20037508, -20037508,
                                         20037508, 20037508.34)
  }); 
  // Traditional Google Map Layer
  var googleLayer = new OpenLayers.Layer.Google(
                'Google Streets',
                 {'sphericalMercator': true}
            );
   var styleMap = new OpenLayers.StyleMap({
       'default': {
           fillColor: 'yellow',
           strokeColor: 'black',
           strokeWidth: 2,
           pointRadius: 5,
           strokeOpacity: 1
       },
       'select': {
          fillOpacity: 1,
          strokeColor: 'white',
          fillColor: 'red'
       }
   });

  var geojson = new OpenLayers.Layer.Vector("{$network} Network", {
		protocol: new OpenLayers.Protocol.HTTP({
                    url: "{$uri}",
                    format: new OpenLayers.Format.GeoJSON()
    	}),
    	projection: new OpenLayers.Projection('EPSG:4326'),
    	styleMap: styleMap,
    	strategies: [new OpenLayers.Strategy.Fixed()]
	});
  map.addLayers([googleLayer,geojson]);
   
  // Provide hover capabilities over road_condition layer
  selectControl = new OpenLayers.Control.SelectFeature(geojson, {
       onSelect: cb_siteOver, 
       onUnselect: cb_siteOut
   });
   map.addControl(selectControl);
   selectControl.activate();

   geojson.events.register('loadend', geojson, function() {
     var e = geojson.getDataExtent();
     map.setCenter( e.getCenterLonLat(), geojson.getZoomForExtent(e,false));
   });

   var proj = new OpenLayers.Projection('EPSG:4326');
   var proj2 = new OpenLayers.Projection('EPSG:900913');
   var point = new OpenLayers.LonLat(-93.8, 42.2);
   point.transform(proj, proj2);

   map.setCenter(point, 7);


   map.addControl( new OpenLayers.Control.LayerSwitcher({id:'ls'}) );
   map.addControl( new OpenLayers.Control.MousePosition() );
}
EOF;
?>