<?php
include_once("../../../../config/settings.inc.php");
include_once "$rootpath/include/iemmap.php";
include_once "$rootpath/include/database.inc.php";
include("$rootpath/include/network.php");
$nt = new NetworkTable("ISUAG");
$ISUAGcities = $nt->table;

$year = isset($_GET["year"]) ? $_GET["year"]: date("Y", time() - 86400 - (7 * 3600) );
$month = isset($_GET["month"]) ? $_GET["month"]: date("m", time() - 86400 - (7 * 3600) );
$day = isset($_GET["day"]) ? $_GET["day"]: date("d", time() - 86400 - (7 * 3600) );
$date = isset($_GET["date"]) ? $_GET["date"]: $year ."-". $month ."-". $day;

$var = (isset($_GET["var"]) && $_GET["var"] != "" ) ? $_GET["var"] : "c11";
$var2 = (isset($_GET["var2"]) && $_GET["var2"] != "" ) ? $_GET["var2"] : "";
$direct = isset($_GET["direct"]) ? $_GET['direct']: "";

$ts = strtotime($date);

$varDef = Array("c11" => "High Air Temperatures",
  "c12" => "Low Air Temperatures [F]",
  "c11c12" => "High and Low Air Temperatures [F]",
  "c30" => "Avg 4in Soil Temperatures [F]",
  "c40" => "Avg Wind Velocity [MPH]",
  "c509" => "Peak 1 Minute Gust [MPH]",
  "c529" => "Peak 5 Second Gust [MPH]",
  "c930" => "Total Precipitation [inch]",
  "c90" => "Total Precipitation [inch]",
  "c20" => "Avg Relative Humidity",
  "c80" => "Solar Radiation [Langleys]",
  "c70" => "Evapotranspiration [inch]",
  "c300hc300l" => "High and Low 4in Soil Temps [F]",
  "c529c530" => "Peak 5 Sec Wind Gust [mph] and Time",
  "dwpfhdwpfl" => "Max and Min Dew Points [F]",
  "chill" => "Standard Chill Unit"
);

$rnd = Array("c11" => 0, "c12" => 0, "c30" => 0,"c300h" => 0, "c300l" => 0,
  "c70" => 2, "c40" => 1 ,"c80" => 0, "dwpfl" => 0, "dwpfh" => 0,
  "c90" => 2,
  "c529" => 0,
  "c530" => 2,
  "pmonth" => 2,
  "pday" => 2);


$myStations = $ISUAGcities;
$height = 480;
$width = 640;

$map = ms_newMapObj("$rootpath/data/gis/base26915.map");
$map->setProjection("init=epsg:26915");
$map->setsize($width,$height);
$map->setextent(175000, 4440000, 775000, 4890000);


$counties = $map->getlayerbyname("counties");
$counties->set("status", MS_ON);

$snet = $map->getlayerbyname("station_plot");
$snet->set("status", MS_ON);

$iards = $map->getlayerbyname("iards");
$iards->set("status", MS_ON);

$bar640t = $map->getlayerbyname("bar640t");
$bar640t->set("status", MS_ON);

$states = $map->getlayerbyname("states");
$states->set("status", MS_ON);

$ponly = $map->getlayerbyname("pointonly");
$ponly->set("status", MS_ON);

$img = $map->prepareImage();
$counties->draw($img);
$states->draw($img);
$iards->draw($img);
$bar640t->draw($img);

$c = iemdb("isuag");
$tbl = strftime("t%Y", $ts);
$dstamp = strftime("%Y-%m-%d", $ts);
if ($var == 'c300') {
  $q = "SELECT station, max(c300) as c300h, max(c300_f) as c300h_f,
     min(c300) as c300l, max(c300_f) as c300l_f
     from hourly WHERE date(valid) = '${dstamp}' GROUP by station";
  $var = 'c300h';
  $var2 = 'c300l';
} else if ($var == 'dwpf') {
  $q = "select MAX( k2f( dewpt( f2k(c100), c200)))::numeric(7,2) as dwpfh,
      station, MIN( k2f( dewpt( f2k(c100), c200)))::numeric(7,2) as dwpfl,
      max(c100_f) as dwpfh_f, max(c100_f) as dwpfl_f
      from hourly WHERE c200 > 0 and date(valid) = '${dstamp}' GROUP by station";
  $var = 'dwpfh';
  $var2 = 'dwpfl';
} else if ($var == "c529") {
  $q = "SELECT station, c529, c529_f, 
    substring(text(c530),length(text(c530)) - 3,2) || ':' || 
    substring(text(c530), length(text(c530)) - 1,2) as c530 , 
    c530_f from daily WHERE valid = '${dstamp}' ";
  $var2 = 'c530';
} else {
  $q = "SELECT * from daily WHERE valid = '${dstamp}' ";
}  
$rs =  pg_exec($c, $q);
$data = Array();
for ($i=0; $row = @pg_fetch_array($rs,$i); $i++) {
  $key = $row['station'];
  if ($key == "A133259") continue;
  $data[$key] = Array();
  $data[$key]['name'] = $ISUAGcities[$key]['name'];
  $data[$key]['lon'] = $ISUAGcities[$key]['lon'];
  $data[$key]['lat'] = $ISUAGcities[$key]['lat'];
  //print_r($row);
  //echo "::$var::";
  $data[$key]['var'] = $row[$var];

  // Red Dot... 
  $pt = ms_newPointObj();
  $pt->setXY($ISUAGcities[$key]['lon'], $ISUAGcities[$key]['lat'], 0);
  $pt->draw($map, $ponly, $img, 0, ' ' );


  // Value UL
  $pt = ms_newPointObj();
  $pt->setXY($ISUAGcities[$key]['lon'], $ISUAGcities[$key]['lat'], 0);
  $pt->draw($map, $snet, $img, 1, 
     round($row[$var], $rnd[$var]) ." ". $row[$var .'_f'] );


  if (strlen($var2) > 0) {
    $data[$key]['var2'] = $row[$var2];
    // Value LL
    $pt = ms_newPointObj();
    $pt->setXY($ISUAGcities[$key]['lon'], $ISUAGcities[$key]['lat'], 0);
    if ($var2 == 'c530'){
      $pt->draw($map, $snet, $img, 2, 
        $row[$var2] ." ". $row[$var2 .'_f'] );
    } else {
      $pt->draw($map, $snet, $img, 2, 
        round($row[$var2], $rnd[$var2]) ." ". $row[$var2 .'_f'] );
    }

  }

  // City Name
  $pt = ms_newPointObj();
  $pt->setXY($ISUAGcities[$key]['lon'], $ISUAGcities[$key]['lat'], 0);
  if ($key == "A131909" || $key == "A130209"){
    $pt->draw($map, $snet, $img, 0, $ISUAGcities[$key]['name'] );
  } else {
    $pt->draw($map, $snet, $img, 0, $ISUAGcities[$key]['name'] );
  }

}

iemmap_title($map, $img, $varDef[$var . $var2] ." on ". date("d M Y", $ts),
	($i == 0) ? 'No Data Found!': null );
$map->drawLabelCache($img);

$url = $img->saveWebImage();

if (strlen($direct) > 0) { 
  header("Content-type: image/png");
  $img->saveImage('');
} else {
?>
<img src="<?php echo $url; ?>" border=1>

<?php } ?>
