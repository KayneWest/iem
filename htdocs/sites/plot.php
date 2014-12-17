<?php 
include("../../config/settings.inc.php");
include("../../include/database.inc.php");
include("../../include/forms.php");
include("setup.php");

 include("../../include/myview.php");
 $t = new MyView();
 $t->thispage="iem-sites";
 $t->title = sprintf("Site Info: %s %s", $station, $cities[$station]["name"]);
 $t->headextra = '<script src="https://maps.googleapis.com/maps/api/js?sensor=false" type="text/javascript"></script>';
 
 $prod = isset($_GET["prod"]) ? $_GET["prod"]: 0;
 $month = isset($_GET["month"]) ? intval($_GET["month"]): date("m");
 $year = isset($_GET["year"]) ? intval($_GET["year"]): date("Y");
 $current = "7dayhilo"; 
 if ($prod == 1) $current = "monthhilo";
 if ($prod == 2) $current = "monthrain";
 
 $t->sites_current = $current;
 
 $products = Array(
0 => "7day_hilo_plot.php",
1 => "month_hilo_plot.php",
2 => "/plotting/month/rainfall_plot.php",
);

 $form = "";
if ($prod == 1 or $prod == 2) { 
	$timestamp = mktime(0,0,0, $month, 1, $year);
	$lmonth = $timestamp - 5*86400;
	$nmonth = $timestamp + 35*86400;
	$llink = sprintf("plot.php?prod=%s&amp;station=%s&amp;network=%s&amp;month=%s&amp;year=%s",
			$prod, $station, $network, date("m", $lmonth), date("Y", $lmonth));
	$nlink = sprintf("plot.php?prod=%s&amp;station=%s&amp;network=%s&amp;month=%s&amp;year=%s",
			$prod, $station, $network, date("m", $nmonth), date("Y", $nmonth));
	$ltext = date("M Y", $lmonth);
	$ntext = date("M Y", $nmonth);
	
	$ms = monthSelect($month);
	$ys = yearSelect(2004, $year);
  $form = <<<EOF
<form method="GET" name="changemonth">
 <input type="hidden" name="station" value="{$station}">
 <input type="hidden" name="network" value="{$network}">
 <input type="hidden" name="prod" value="{$prod}">
 <h3>Select month and year:</h3>
 <div class="row">
 <div class="col-sm-3">
 <a href="{$llink}" class="btn btn-default">{$ltext} <i class="glyphicon glyphicon-arrow-left"></i></a>
	</div>
	<div class="col-sm-6">
  {$ms} {$ys}
 <input type="submit" value="Generate Plot">
 </div>
 <div class="col-sm-3">
 <a href="{$nlink}" class="btn btn-default"><i class="glyphicon glyphicon-arrow-right"></i> {$ntext}</a>
</div>
</div>
</form>
EOF;

}
if ($prod == 1){
	$uri = sprintf("/plotting/auto/plot/17/month:%s"
			."__year:%s::station:%s::network:%s::dpi:100.png", $month, $year,
 			$station, $network);
} else {
	$uri = sprintf("%s?month=%s&year=%s&network=%s&station=%s", $products[$prod], 
		$month, $year, $network, $station);
}
$t->content = <<<EOF

{$form}

 <div class="row"><div class="col-md-12">
 <img src="{$uri}" alt="Monthly Plot" class="img img-responsive">
 </div></div>
EOF;
$t->render('sites.phtml');
?>