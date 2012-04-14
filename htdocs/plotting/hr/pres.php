<?php
include("../../../config/settings.inc.php");
$station = isset($_GET['station']) ? $_GET["station"]: "AMW";
$hours = isset($_GET["hours"]) ? intval($_GET["hours"]): 24;

include("$rootpath/include/database.inc.php");

$connection = iemdb("access");


$query1 = "SET TIME ZONE 'GMT'";
$query2 = "SELECT tmpf, alti
        , valid from current_log c JOIN stations t on (t.iemid = c.iemid) 
        WHERE alti > 0 and t.id = '". $station ."'
        and valid + '".$hours." hours' > CURRENT_TIMESTAMP ORDER by valid ASC";

// $result = pg_exec($connection, $query1);
$result = pg_exec($connection, $query2);


$ydata = array();
$times= array();

for( $i=0; $row = @pg_fetch_array($result,$i); $i++) 
{ 
  $ydata[] = $row["alti"];
  $times[] = strtotime( $row["valid"] );
}


pg_close($connection);


include ("$rootpath/include/jpgraph/jpgraph.php");
include ("$rootpath/include/jpgraph/jpgraph_line.php");
include ("$rootpath/include/jpgraph/jpgraph_scatter.php");
include ("$rootpath/include/jpgraph/jpgraph_date.php");
include ("$rootpath/include/station.php");
$st = new StationData($station);
$cities = $st->table;

// Create the graph. These two calls are always required
$graph = new Graph(400,350,"example1");
$graph->SetScale("datlin");
$graph->yaxis->scale->ticks->Set(5,1);
//$graph->yaxis->scale->ticks->SetPrecision(0);
//$graph->SetY2Scale("lin", 0, 360);
//$graph->y2axis->scale->ticks->Set(30,15);
//$graph->y2axis->scale->ticks->SetPrecision(0);
$graph->img->SetMargin(40,40,25,90);
//$graph->xaxis->SetFont(FONT1,FS_BOLD);
$graph->xaxis->SetLabelAngle(90);
$graph->title->Set($hours." Altimeter for ". $cities[$station]['name']);

$graph->title->SetFont(FF_FONT1,FS_BOLD,16);
//$graph->yaxis->SetTitle("Wind Speed [knots]");
//$graph->y2axis->SetTitle("Direction [N 0]");
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD,12);
//$graph->xaxis->SetTitle("Local Valid Time");
$graph->xaxis->SetTitleMargin(55);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD,12);
$graph->xaxis->SetLabelFormatString("M d h A", true);

$graph->legend->Pos(0.01, 0.07);
$graph->legend->SetLayout(LEGEND_HOR);

// Create the linear plot
$lineplot=new LinePlot($ydata, $times);
// $lineplot->SetLegend("Temp (F)");
$lineplot->SetColor("red");

// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
?>

