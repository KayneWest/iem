<?php
 /* Make a nice simple plot of 7 day temperatures */
 include('../../config/settings.inc.php');
 include("$rootpath/include/database.inc.php");
 include('setup.php');

 $db = iemdb("access");

 /* Get high and low temps for the past 7 days */
$sql = "SELECT day, max_tmpf, min_tmpf from summary WHERE 
         station = '$station' and day < 'TODAY' ORDER by day DESC LIMIT 7";

$rs = pg_query($db, $sql);

$highs = Array();
$lows = Array();
$xlabels = Array();

for( $i=0; $row = @pg_fetch_array($rs,$i); $i++) {
  $ts = strtotime($row["day"]);
  $xlabels[] = date("m/d", $ts);
  $highs[] = $row["max_tmpf"];
  $lows[] = $row["min_tmpf"];
}
$xlabels = array_reverse($xlabels);
$highs = array_reverse($highs);
$lows = array_reverse($lows);

pg_close($db);

/* Now, lets get averages */
$db = iemdb("coop");

$sqlDate = sprintf("2000-%s", date("m-d") );

$sql = "SELECT valid, high, low from climate WHERE station = 'ia0200'
        and valid < '$sqlDate' ORDER by valid DESC LIMIT 7";

$rs = pg_query($db, $sql);

$ahighs = Array();
$alows = Array();

for( $i=0; $row = @pg_fetch_array($rs,$i); $i++) {
  $ahighs[] = $row["high"];
  $alows[] = $row["low"];
}
$ahighs = array_reverse($ahighs);
$alows = array_reverse($alows);

pg_close($db);

/* Time to plot */
include("$rootpath/include/jpgraph/jpgraph.php");
include("$rootpath/include/jpgraph/jpgraph_bar.php");
include("$rootpath/include/jpgraph/jpgraph_line.php");


$graph = new Graph(300,250,"auto");
$graph->SetScale("textlin", min($lows)-7, max($ahighs)+2);
$graph->SetMarginColor('white');

$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');
$graph->xgrid->Show();

$graph->title->Set("7 Day Hi/Lo Temps for $sname");
$graph->SetMargin(40,5,50,40);

$graph->xaxis->SetTickLabels($xlabels);
$graph->xaxis->SetLabelAngle(90);

$graph->yscale->SetGrace(5);
$graph->yaxis->SetTitle("Temperature [F]");

$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->Pos(0.01,0.08,"left","top");

$bplot1 = new BarPlot($highs);
$bplot1->SetFillColor('red');
$bplot1->SetLegend("High Temp");
$bplot1->value->Show();
$bplot1->value->SetFormat('%d');
$bplot1->value->SetColor('white');
$bplot1->value->SetAngle(90);
$bplot1->value->SetFont(FF_FONT1,FS_BOLD);
$bplot1->SetValuePos('bottom');
$bplot1->SetWidth(0.7);

$bplot2 = new BarPlot($lows);
$bplot2->SetFillColor('blue');
$bplot2->SetLegend("Low Temp");
$bplot2->value->Show();
$bplot2->value->SetFormat('%d');
$bplot2->value->SetColor('white');
$bplot2->value->SetAngle(90);
$bplot2->value->SetFont(FF_FONT1,FS_BOLD);
$bplot2->SetValuePos('bottom');
$bplot2->SetWidth(0.7);

$gbarplot = new GroupBarPlot(array($bplot1,$bplot2));
$gbarplot->SetWidth(0.6);

$l1plot=new LinePlot($alows);
$l1plot->SetColor("black");
$l1plot->mark->SetType(MARK_FILLEDCIRCLE);
$l1plot->mark->SetFillColor("lightblue");
$l1plot->mark->SetWidth(4);
$l1plot->SetWeight(2);
$l1plot->SetLegend("Avg Low");
$l1plot->SetBarCenter();

$l2plot=new LinePlot($ahighs);
$l2plot->SetColor("black");
$l2plot->mark->SetType(MARK_FILLEDCIRCLE);
$l2plot->mark->SetFillColor("lightred");
$l2plot->mark->SetWidth(4);
$l2plot->SetWeight(2);
$l2plot->SetLegend("Avg High");
$l2plot->SetBarCenter();

$graph->Add($gbarplot);
$graph->Add($l2plot);
$graph->Add($l1plot);

$graph->Stroke();
?>
