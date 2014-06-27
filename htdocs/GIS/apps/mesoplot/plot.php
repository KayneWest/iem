<?php
include("../../../../config/settings.inc.php");
//  mesoplot/plot.php
//  - Replace GEMPAK mesoplots!!!
include_once "../../../../include/iemmap.php";
include("../../../../include/mlib.php");
include("../../../../include/iemaccess.php");
include("../../../../include/iemaccessob.php");
include("../../../../include/network.php");

$network = isset($_GET['network']) ? $_GET['network'] : 'KCCI';
$nt = new NetworkTable($network);
$Scities = $nt->table;
$titles = Array("KCCI" => "KCCI SchoolNet8",
 "KELO" => "KELO WeatherNet",
 "KIMT" => "KIMT StormNet");

$iem = new IEMAccess();
$data = $iem->getNetwork($network);
function skntChar($sknt){
  if ($sknt < 2)  return chr(0);
  if ($sknt < 5)  return chr(227);
  if ($sknt < 10)  return chr(228);
  if ($sknt < 15)  return chr(229);
  if ($sknt < 20)  return chr(230);
  if ($sknt < 25)  return chr(231);
  if ($sknt < 30)  return chr(232);
  if ($sknt < 35)  return chr(233);
  if ($sknt < 40)  return chr(234);
  if ($sknt < 45)  return chr(235);
  if ($sknt < 50)  return chr(236);
  if ($sknt < 55)  return chr(237);
  if ($sknt < 60)  return chr(238);
}


$myStations = Array();
$formStr = "";
$cgiStr = "";
if (! isset($str) ){
  $str = Array();
}

foreach ($Scities as $key => $value) {
    $myStations[$key] = "hi";
}

$var = isset($_GET["var"]) ? $_GET["var"] : "tmpf";

$height = 350;
$width = 450;


$proj = "init=epsg:26915";

$projInObj = ms_newprojectionobj("init=epsg:4326");
$projOutObj = ms_newprojectionobj($proj);

$varDef = Array("tmpf" => "Current Temperatures",
  "dwpf" => "Current Dew Points",
  "sped" => "Current Wind Speed [MPH]",
  "sknt" => "Current Wind Speed [knts]",
  "barb" => "Current Wind Barbs [knts]",
  "gbarb" => "Today Wind Gust Barbs [knts]",
  "max_sped" => "Today's Wind Gust [MPH]",
  "max_sknt" => "Today's Wind Gust [knts]",
  "feel" => "Currently Feels Like",
  "tmpf_max" => "Today's High Temperature",
  "tmpf_min" => "Today's Low Temperature",
  "pmonth" => "This Month's Precip",
  "pday" => "Today's Precip");

$rnd = Array("tmpf" => 0,
  "dwpf" => 0,
  "sknt" => 0,
  "max_sknt" => 0,
  "feel" => 0,
  "pmonth" => 2,
  "pday" => 2);


$height = 480;
$width = 640;

$map = ms_newMapObj("../../../../data/gis/base26915.map");
$map->setSize($width, $height);

$map->setprojection($proj);
if (isset($_GET["zoom"]))
  $map->setextent(375000, 4575000, 475000, 4675000);
else
{
  if ($network == "KIMT")
    $map->setextent(420000, 4740000, 600000, 4900000);
  else if ($network == "KELO")
    $map->setextent(-400000, 4600000, 320000, 5200000);
  else
    $map->setextent(300000, 4480000, 550000, 4800000);
}

$counties = $map->getlayerbyname("uscounties");
$counties->set("status", MS_ON);

$states = $map->getlayerbyname("states");
$states->set("status", MS_ON);

$barbs = $map->getlayerbyname("barbs");
$barbs->set("status", MS_ON);
$bclass = $barbs->getClass(0);

$temps = $map->getlayerbyname("station_plot");
$temps->set("status", MS_ON);

$iards = $map->getlayerbyname("interstates");
$iards->set("status", MS_ON);

$mwradar = $map->getlayerbyname("nexrad_n0q");
$mwradar->set("status", MS_ON);

$img = $map->prepareImage();

$counties->draw($img);
$states->draw($img);
$iards->draw($img);
$mwradar->draw($img);
$now = time();

foreach($data as $key => $value){

  if ($Scities[$key]["online"] == false) continue;
  $bzz = $value->db;
  if (($now - $bzz["ts"]) < 3600){ 
     $pt = ms_newPointObj();
     $pt->setXY($Scities[$key]["lon"], $Scities[$key]["lat"], 0);
     $rotate =  0 - intval($bzz["drct"]);
     $bclass->getLabel(0)->set("angle", doubleval($rotate));
     $pt->draw($map, $barbs, $img, 0, skntChar($bzz["sknt"]) );

     $pt = ms_newPointObj();
     $pt->setXY($Scities[$key]["lon"], $Scities[$key]["lat"], 0);
     $tmpf = intval($bzz['tmpf']);
     $pt->draw($map, $temps, $img, 1, round($bzz['tmpf'], $rnd['tmpf']) );

     $pt = ms_newPointObj();
     $pt->setXY($Scities[$key]["lon"], $Scities[$key]["lat"], 0);
     $dwpf = intval($bzz['dwpf']);
     $pt->draw($map, $temps, $img, 2, round($bzz['dwpf'], $rnd['dwpf']) );
  }
}

  $ts = strftime("%I %p");

$temps->draw($img);
$map->drawLabelCache($img);

iemmap_title($map, $img, $titles[$network] ."  Station Plot", 
			"Valid: ". date("h:i A d M Y") );

header("Content-type: image/png");
$img->saveImage('');
?>
