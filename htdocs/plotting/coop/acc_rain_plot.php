<?php
include("../../../config/settings.inc.php");
include("$rootpath/include/network.php");     
include("$rootpath/include/adodb-time.inc.php");
$network = isset($_REQUEST["network"]) ? $_REQUEST["network"]: "IACLIMATE";
$nt = new NetworkTable($network);
$cities = $nt->table;

$station = isset($_GET['station']) ? $_GET['station'] : die("No station");

$syear = isset($_GET['syear']) ? $_GET['syear']: die("No syear");
$smonth = isset($_GET['smonth']) ? $_GET['smonth']: die("No smonth");
$sday = isset($_GET['sday']) ? $_GET['sday']: die("No sday");

$eyear = isset($_GET['eyear']) ? $_GET['eyear']: die("No eyear");
$emonth = isset($_GET['emonth']) ? $_GET['emonth']: die("No emonth");
$eday = isset($_GET['eday']) ? $_GET['eday']: die("No eday");

$sts = adodb_mktime(0,0,0, $smonth, $sday, $syear);
$ets = adodb_mktime(0,0,0, $emonth, $eday, $eyear);
if ($sts == $ets)
{
	$sts = $ets - (31*86400);
}
if ( ($ets - $sts) > 365*5*86400 )
{
	$sts = $ets - 365*5*86400 ;
}
$sdate = adodb_date("Y-m-d", $sts);
$edate = adodb_date("Y-m-d", $ets);
$s2date = adodb_date("2000-m-d", $sts);
$e2date = adodb_date("2000-m-d", $ets);


$today = time();

$coopdb = iemdb("coop");

/* First we load climate normals */
$crain = Array();

$rs = pg_prepare($coopdb, "SELECT", "SELECT precip, valid from climate " .
		"WHERE station = $1");
$rs = pg_execute($coopdb, "SELECT", Array($station));

for( $i=0; $row = @pg_fetch_array($rs,$i); $i++) 
{
	$crain[$row["valid"]] = $row["precip"];
}

$rs = pg_prepare($coopdb, "SELECT2", "SELECT precip, day, " .
		"extract(year from day) as y, extract(month from day) as m, " .
		"extract(day from day) as d from alldata_". substr($station,0,2) ." WHERE station = $1 " .
		"and day between $2 and $3 ORDER by day ASC");
$rs = pg_execute($coopdb, "SELECT2", Array($station, $sdate, $edate));

$aobs = Array();
$xlabels = Array();
$zeroes = Array();
$atot = 0;
$ctot = 0;
$aclimate = Array();
$cdiff = Array();
$obs = Array();
for ($i=0; $row = @pg_fetch_array($rs,$i); $i++)
{
	$p = (float)$row["precip"];
	$atot += $p;
	$aobs[$i] = $atot;
	$obs[$i] = $p;

	$dy = substr($row["day"], 8, 2);
	if ($dy == "01")
	{
		$ds = adodb_mktime(0,0,0, $row["m"], $row["d"], $row["y"]);
		$xlabels[$i] = adodb_date("1 M Y", $ds);
	} else {
		$xlabels[$i] = "";
	}

	$zeros[$i] = 0;
	$ngdd = $crain["2000-". substr($row["day"], 5,5) ];
	$ctot += $ngdd;
	$aclimate[$i] = $ctot;

	$cdiff[$i] = $atot - $ctot ;

}
$xlabels[] = "";

pg_close($coopdb);

include ("$rootpath/include/jpgraph/jpgraph.php");
include ("$rootpath/include/jpgraph/jpgraph_line.php");
include ("$rootpath/include/jpgraph/jpgraph_plotline.php");
include ("$rootpath/include/jpgraph/jpgraph_bar.php");

// Create the graph. These two calls are always required
$graph = new Graph(640,480,"example1");
$graph->SetScale("textlin");
$graph->img->SetMargin(45,10,80,80);
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->SetTitleMargin(30);
$graph->xaxis->SetPos("min");
//$graph->xaxis->SetTitle("Day of Month");
$graph->xaxis->SetTickLabels($xlabels);
$graph->xaxis->SetLabelAngle(90);
$graph->xscale->ticks->SupressTickMarks();
$graph->yaxis->SetTitle("Precipitation (in)");
$graph->title->Set( $cities[$station]["name"] ." [$station] Precipitation");
$graph->subtitle->Set("Plot Duration: $sdate -- $edate");
$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->Pos(0.05, 0.1, "right", "top");
while (list($k,$v) = each($xlabels) ){
	if ($v)
		$graph->AddLine(new PlotLine(VERTICAL,$k,"tan",1));
}


// Create the linear plot
$lp0 =new LinePlot($cdiff);
$graph->Add($lp0);
$lp0->SetFillColor("red");
$lp0->SetLegend("Accum Difference");

$b2plot = new BarPlot($obs);
$graph->Add($b2plot);
$b2plot->SetFillColor("blue");
$b2plot->SetLegend("Obs Rain");

// Create the linear plot
$lp1=new LinePlot($aobs);
$graph->Add($lp1);
$lp1->SetLegend("Actual Accum");
$lp1->SetColor("blue");
$lp1->SetWeight(2);

$lp2=new LinePlot($aclimate);
$graph->Add($lp2);
$lp2->SetLegend("Climate Accum");
$lp2->SetColor("red");
$lp2->SetWeight(2);

$z = new LinePlot($zeros);
$graph->Add($z);
$z->SetWeight(2);

// Add the plot to the graph


// Display the graph
$graph->Stroke();
?>
