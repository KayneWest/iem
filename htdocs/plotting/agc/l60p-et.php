<?php
include("../../../config/settings.inc.php");
include("../../../include/database.inc.php");
$connection = iemdb("isuag");
include("../../../include/network.php");
$nt = new NetworkTable("ISUAG");
$ISUAGcities = $nt->table;

$station = $_GET['station'];


$rs = pg_prepare($connection, "SELECT", "SELECT c900 - c70 as dater, " .
		"c900 as dater2, to_char(valid, 'yy/mm/dd') as valid " .
		"from daily WHERE station = $1 and " .
		"(valid + '60 days'::interval) > CURRENT_TIMESTAMP  " .
		"ORDER by valid ASC");
$result = pg_execute($connection, "SELECT", Array($station));

$ydata = array();
$ydata2 = array();
$xlabel= array();

$accumm = 0.00;

for( $i=0; $row = @pg_fetch_array($result,$i); $i++) 
{ 
  $temper = $row["dater"];
  $accumm = $accumm + $temper ; 
  $ydata[$i]  = $accumm;
  $xlabel[$i] = $row["valid"];

  $precDay = $row["dater2"];
  if ($precDay > 0){
	$ydata2[$i] = $row["dater2"];
  } else {
	$ydata2[$i] = "";
  }
}

//  $xlabel = array_reverse( $xlabel );
//  $ydata  = array_reverse( $ydata );
//  $ydata2  = array_reverse( $ydata2 );
//  $ydata3  = array_reverse( $ydata3 );

pg_close($connection);

include ("../../../include/jpgraph/jpgraph.php");
include ("../../../include/jpgraph//jpgraph_line.php");
include ("../../../include/jpgraph//jpgraph_bar.php");


// Create the graph. These two calls are always required
$graph = new Graph(600,350,"example1");
$graph->SetScale("textlin");
//$graph->SetY2Scale("lin");
$graph->img->SetMargin(50,10,45,80);
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->SetTickLabels($xlabel);
$graph->xaxis->SetLabelAngle(90);
$graph->title->Set("Last 60 days accumulated (Obs Prec - PET) for  ". $ISUAGcities[ $station]["name"] );

$graph->title->SetFont(FF_FONT1,FS_BOLD,12);
$graph->yaxis->SetTitle("inches");
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD,12);
$graph->xaxis->SetTitle("Month/Day");
//$graph->y2axis->SetTitle("Solar Radiation [Langleys]");
//$graph->y2axis->title->SetFont(FF_FONT1,FS_BOLD,12);
$graph->xaxis->SetTitleMargin(48);
$graph->yaxis->SetTitleMargin(35);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD,12);
$graph->xaxis->SetPos("min");

//$graph->y2axis->SetColor("red");
//$graph->yaxis->SetColor("red");

// Create the linear plot
$lineplot=new LinePlot($ydata);
$graph->Add($lineplot);
$lineplot->SetColor("black");

// Create the linear plot
//$lineplot2=new LinePlot($ydata2);
//$lineplot2->SetColor("red");
//$lineplot2->SetLegend("Solar Rad");

$bplot = new BarPlot($ydata2);
$graph->Add($bplot);
$bplot->SetFillColor("blue");
$bplot->SetLegend("Ob Prec [in]");

$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->Pos(0.10, 0.06, "right", "top");


$graph->Stroke();
?>

