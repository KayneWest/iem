<?php
include("../../../config/settings.inc.php");
include("$rootpath/include/database.inc.php");
$connection = iemdb("coop");
$station = isset($_GET["station"]) ? strtolower($_GET["station"]) : die("No station");
$year = isset($_GET["year"]) ? $_GET["year"] : date("Y");


$query2 = "SELECT high, low, years, to_char(valid, 'mm dd') as valid from climate WHERE station = '". $station ."' ORDER by valid ASC";
$query3 = "SELECT high, low from alldata WHERE
  stationid = '". $station ."' and year = ".$year." ORDER by day ASC";


$result = pg_exec($connection, $query2);
$result2 = pg_exec($connection, $query3);

$s1_hi = array();
$s1_lo = array();
$s2_hi = array();
$s2_lo = array();
$xlabel= array();
$years_s1 = 0;
$years_s2 = 0;

for( $i=0; $row = @pg_fetch_array($result,$i); $i++) 
{ 
  $row2 = @pg_fetch_array($result2,$i);
  $s1_hi[$i]  = $row["high"];
  $s1_lo[$i]  = $row["low"];
  $s2_hi[$i]  = $row2["high"];
  $s2_lo[$i]  = $row2["low"];
  $xlabel[$i]  = "";
  $years_s1 = $row["years"];
  $years_s2 = @$row2["years"];
}

$xlabel[0] = "Jan 1";  // 1
$xlabel[31] = "Feb 1"; // 32
$xlabel[60] = "Mar 1"; // 61
$xlabel[91] = "Apr 1"; // 92
$xlabel[121] = "May 1"; // 122
$xlabel[152] = "Jun 1"; //153
$xlabel[182] = "Jul 1"; //183
$xlabel[213] = "Aug 1"; //214
$xlabel[244] = "Sept 1"; //245
$xlabel[274] = "Oct 1"; //274
$xlabel[305] = "Nov 1"; //306
$xlabel[335] = "Dec 1"; //336
$xlabel[365] = "Dec 31"; //366

pg_close($connection);

include ("$rootpath/include/jpgraph/jpgraph.php");
include ("$rootpath/include/jpgraph/jpgraph_line.php");
include("$rootpath/include/network.php");     
$nt = new NetworkTable("IACLIMATE");
$cities = $nt->table;


// Create the graph. These two calls are always required
$graph = new Graph(640,480);
$graph->SetScale("textlin");
$graph->img->SetMargin(40,40,65,90);
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->SetTickLabels($xlabel);
$graph->xaxis->SetLabelAngle(90);
$graph->title->Set($cities[strtoupper($station)]["name"] ." Climatological Comparison");
$graph->subtitle->Set("Climate Record: ". $years_s1 ." yrs  vs Obs for yr: " . $year );

$graph->title->SetFont(FF_FONT1,FS_BOLD,16);
$graph->yaxis->SetTitle("Temperature [F]");
$graph->yscale->SetGrace(10);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD,12);
$graph->xaxis->SetTitle("Date");
$graph->xaxis->SetTitleMargin(55);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD,12);
$graph->xaxis->SetPos("min");

$graph->legend->Pos(0.03, 0.09);
$graph->legend->SetLayout(LEGEND_HOR);

// Create the linear plot
$lineplot=new LinePlot($s1_hi);
$lineplot->SetLegend("Climate High (F)");
$lineplot->SetColor("green");

// Create the linear plot
$lineplot2=new LinePlot($s1_lo);
$lineplot2->SetLegend("Low (F)");
$lineplot2->SetColor("cyan1");

// Create the linear plot
$lineplot3=new LinePlot($s2_hi);
$lineplot3->SetLegend($year ." High (F)");
$lineplot3->SetColor("red");

// Create the linear plot
$lineplot4=new LinePlot($s2_lo);
$lineplot4->SetLegend("Low (F)");
$lineplot4->SetColor("blue");


// Add the plot to the graph
$graph->Add($lineplot);
$graph->Add($lineplot2);
$graph->Add($lineplot3);
$graph->Add($lineplot4);


$graph->AddLine(new PlotLine(VERTICAL,31,"tan",1));
$graph->AddLine(new PlotLine(VERTICAL,60,"tan",1));
$graph->AddLine(new PlotLine(VERTICAL,91,"black",1));
$graph->AddLine(new PlotLine(VERTICAL,121,"tan",1));
$graph->AddLine(new PlotLine(VERTICAL,152,"tan",1));
$graph->AddLine(new PlotLine(VERTICAL,182,"black",1));
$graph->AddLine(new PlotLine(VERTICAL,213,"tan",1));
$graph->AddLine(new PlotLine(VERTICAL,244,"tan",1));
$graph->AddLine(new PlotLine(VERTICAL,274,"black",1));
$graph->AddLine(new PlotLine(VERTICAL,305,"tan",1));
$graph->AddLine(new PlotLine(VERTICAL,335,"tan",1));
$graph->AddLine(new PlotLine(HORIZONTAL,32,"blue",2));

// Display the graph
$graph->Stroke();
?>

