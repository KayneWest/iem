<?php
include("../../../config/settings.inc.php");
include("$rootpath/include/database.inc.php");

/** We need these vars to make this work */
$syear = isset($_GET["syear"]) ? $_GET["syear"] : date("Y");
$smonth = isset($_GET["smonth"]) ? $_GET["smonth"]: date("m");
$sday = isset($_GET["sday"]) ? $_GET["sday"] : date("d");
$days = isset($_GET["days"]) ? $_GET["days"]: 2;
$station = isset($_GET['station']) ? $_GET["station"] : "";
$mode = isset($_GET["mode"]) ? $_GET["mode"]: "rt";

$sts = time() - (3.*86400.);
$ets = time();

/** Lets assemble a time period if this plot is historical */
if ($mode != 'rt') {
  $sts = mktime(0,0,0, $smonth, $sday, $syear);
  $ets = $sts + ($days * 86400.0);
}



$iemdb = iemdb('iem');
$rs = pg_prepare($iemdb, "SELECTMETA", "SELECT * from 
      rwis_traffic_meta WHERE nwsli = $1");
$rs = pg_execute($iemdb, "SELECTMETA", Array($station));
$avg_speed = Array();

$normal_vol = Array();
$long_vol = Array();
$occupancy = Array();
$times = Array();
$labels = Array();
for($i=0;$row=@pg_fetch_array($rs,$i);$i++){
	$avg_speed[$row["lane_id"]] = Array();
	$normal_vol[$row["lane_id"]] = Array();
	$long_vol[$row["lane_id"]] = Array();
	$occupancy[$row["lane_id"]] = Array();
	$times[$row["lane_id"]] = Array();
	$labels[$row["lane_id"]] = $row["name"];
}

$dbconn = iemdb('rwis');
$rs = pg_prepare($dbconn, "SELECT", "SELECT * from alldata_traffic
  WHERE station = $1 and valid > $2 and valid < $3 ORDER by valid ASC");
$rs = pg_execute($dbconn, "SELECT", Array($station,
       date("Y-m-d H:i", $sts), date("Y-m-d H:i", $ets)) );

for( $i=0; $row = @pg_fetch_array($rs,$i); $i++) 
{ 
  $times[$row["lane_id"]][] = strtotime( substr($row["valid"],0,16) );
  $avg_speed[$row["lane_id"]][] = $row["avg_speed"];
  $normal_vol[$row["lane_id"]][] = $row["avg_headway"];
  $long_vol[$row["lane_id"]][] = $row["avg_headway"];
  $occupancy[$row["lane_id"]][] = $row["avg_headway"];
}
pg_close($dbconn);
pg_close($iemdb);

include ("$rootpath/include/jpgraph/jpgraph.php");
include ("$rootpath/include/jpgraph/jpgraph_line.php");
include ("$rootpath/include/jpgraph/jpgraph_bar.php");
include ("$rootpath/include/jpgraph/jpgraph_date.php");
include ("$rootpath/include/jpgraph/jpgraph_led.php");

if (pg_num_rows($rs) == 0){
	
	$led = new DigitalLED74();
    $led->StrokeNumber('NO TRAFFIC DATA AVAILABLE',LEDC_GREEN);
    die();
}

include ("$rootpath/include/network.php");
$nt = new NetworkTable("IA_RWIS");
$cities = $nt->table;

// Create the graph. These two calls are always required
$graph = new Graph(650,550,"example1");
$graph->SetScale("datlin");
$graph->SetMarginColor("white");
$graph->SetColor("lightyellow");
$graph->img->SetMargin(40,55,105,105);
//$graph->xaxis->SetFont(FS_FONT1,FS_BOLD);


$graph->yaxis->SetTitle("Average Speed [mph]");
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD,12);

$graph->xaxis->SetTitle("Time Period:");
$graph->xaxis->SetTitleMargin(67);
$graph->xaxis->title->SetFont(FF_VERA,FS_BOLD,12);
$graph->xaxis->title->SetColor("brown");
$graph->xaxis->SetPos("min");
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->SetLabelFormatString("M-j h A", true);

$graph->legend->Pos(0.01, 0.01);
$graph->legend->SetLayout(LEGEND_VERT);

$colors = Array(0 => "green", 1 => "black", 2 => "blue", 3 => "red",
 4 => "purple", 5 => "tan", 6 => "pink", 7 => "lavendar");
while(list($k,$v) = each($times)){
	// Create the linear plot
    $lineplot=new LinePlot($avg_speed[$k], $times[$k]);
    $lineplot->SetLegend($labels[$k]);
	$lineplot->SetColor($colors[$k]);
	$lineplot->SetWeight(3);
	$graph->add($lineplot);
}

$graph->Stroke();
?>