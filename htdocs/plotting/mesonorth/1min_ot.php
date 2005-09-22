<?php
$year = isset($_GET["year"]) ? $_GET["year"] : date("Y");
$month = isset($_GET["month"]) ? $_GET["month"]: date("m");
$day = isset($_GET["day"]) ? $_GET["day"] : date("d");

$myTime = mktime(0,0,0,$month,$day,$year);

$dirRef = strftime("%Y_%m/%d", $myTime);
$titleDate = strftime("%b %d, %Y", $myTime);
$fcontents = file('/mesonet/ARCHIVE/raw/ot/ot0003/'.$dirRef.'.dat');

$oldformat = 1;
if ($myTime >= mktime(0,0,0,8,12,2005))
{
  $oldformat = 0;
}

$t1 = array();
$t2 = array();
$t3 = array();
$t4 = array();
$times = array();

$start = intval( $myTime );
$i = 0;

$new_contents = array_slice($fcontents,2);
while (list ($line_num, $line) = each ($new_contents)) {
  $parts = split (",", $line); 
  $hhmm = str_pad($parts[3],4,"0",STR_PAD_LEFT);
  $hh = substr($hhmm,0,2);
  if ($hh == 24){$hh = 00;}
  $mm = substr($hhmm,2,3);
  $timestamp = mktime($hh,$mm,0,$month,$day,$year);
  if ($oldformat)
  {
    $thisTmpc = $parts[10];
    $t1[] = round ((9.0/5.0*$thisTmpc)+32.0,2);
  }
  else
  {
    $t1[] = $parts[4];
    $t2[] = $parts[5];
    $t3[] = $parts[6];
    $t4[] = $parts[7];
  }
  $times[] = $timestamp;

} // End of while


include ("/mesonet/php/include/jpgraph/jpgraph.php");
include ("/mesonet/php/include/jpgraph/jpgraph_line.php");
include ("/mesonet/php/include/jpgraph/jpgraph_date.php");


// Create the graph. These two calls are always required
$graph = new Graph(600,500);
$graph->SetScale("datlin");

$graph->img->SetMargin(65,40,45,60);
//$graph->xaxis->SetFont(FONT1,FS_BOLD);
//$graph->xaxis->SetTickLabels($xlabel);
//$graph->xaxis->SetTextLabelInterval(60);
$graph->xaxis->SetTextTickInterval(6);
$graph->xaxis->SetLabelAngle(90);
$graph->xaxis->scale->SetDateFormat("h A");

//$graph->yaxis->scale->ticks->SetPrecision(1);
//$graph->yaxis->scale->ticks->Set(1.2,0.5);

$graph->yscale->SetGrace(10);
$graph->title->Set("Cluster Room Temperature ($titleDate)");

$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->Pos(0.01,0.05);

//[DMF]$graph->y2axis->scale->ticks->Set(100,25);
//[DMF]$graph->y2axis->scale->ticks->SetPrecision(0);

$graph->title->SetFont(FF_VERDANA,FS_BOLD,14);
$graph->yaxis->SetTitle("Temperature [F]");

//[DMF]$graph->y2axis->SetTitle("Solar Radiation [W m**-2]");

$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->xaxis->SetTitle("Valid Local Time");
$graph->xaxis->SetTitleMargin(30);
//$graph->yaxis->SetTitleMargin(48);
$graph->yaxis->SetTitleMargin(40);
$graph->xaxis->title->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->xaxis->SetPos("min");

// Create the linear plot
$lineplot=new LinePlot($t1, $times);
if (sizeof($t2) > 0) {
  $lineplot->SetLegend("Out of Subfloor");
} else {
  $lineplot->SetLegend("Room Temp");
}
$lineplot->SetColor("red");
$graph->Add($lineplot);

if (sizeof($t2) > 0) {
// Create the linear plot
$lineplot2=new LinePlot($t2, $times);
$lineplot2->SetLegend("Inbound Top");
$lineplot2->SetColor("blue");
$graph->Add($lineplot2);

// Create the linear plot
$lineplot3=new LinePlot($t3, $times);
$lineplot3->SetLegend("Outbound Top");
$lineplot3->SetColor("green");
$graph->Add($lineplot3);

// Create the linear plot
$lineplot4=new LinePlot($t4, $times);
$lineplot4->SetLegend("Room");
$lineplot4->SetColor("purple");
$graph->Add($lineplot4);
}

$graph->Stroke();

?>
