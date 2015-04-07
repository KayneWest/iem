<?php 
// Monitor a bunch of climodat sites
require_once "../../config/settings.inc.php";
define("IEM_APPID", 89);
require_once "../../include/myview.php";
require_once "../../include/imagemaps.php";
require_once "../../include/database.inc.php";
require_once "../../include/network.php";

$nt = new NetworkTable("IACLIMATE");

$sdate = isset($_GET["sdate"]) ? $_GET["sdate"]: "04/01/2015";
$edate = isset($_GET["edate"]) ? $_GET["edate"]: "12/31/2015";
$hiddendates = <<<EOF
<input type="hidden" name="sdate" value="{$sdate}">
<input type="hidden" name="edate" value="{$edate}">
EOF;
$sdate = strtotime($sdate);
$edate = strtotime($edate);
$s = isset($_GET["s"]) ? $_GET["s"] : Array();
if (isset($_GET['r'])){
	$r = $_GET["r"];
	while(list($k,$v)=each($r)){
		if(($key = array_search($v, $s)) !== false) {
			unset($s[$key]);
		}
	}
}
$hiddenstations = "";
$table = "";
while (list($k,$v)=each($s)){
	$hiddenstations .= "<input type=\"hidden\" name=\"s[]\" value=\"$v\">";
}

$sdatestr = date("Y-m-d", $sdate);
$edatestr = date("Y-m-d", $edate);
$sstring = "('". implode(",", $s) ."')";
$sstring = str_replace(",", "','", $sstring);
$sql = <<<EOF
select o.station,
   sum(gdd50(o.high, o.low)) as ogdd50, sum(o.precip) as oprecip,
   sum(gdd50) as cgdd50, sum(c.precip) as cprecip,
   max(o.high) as maxtmpf, min(o.low) as mintmpf,
   avg( (o.high + o.low) / 2.0 ) as avgtmpf,
   sum(sdd86) as csdd86, sum(sdd86(o.high, o.low)) as osdd86
  from alldata_ia o, climate51 c WHERE
   c.station in {$sstring}
   and o.station = c.station
   and day >= '{$sdatestr}' and day < '{$edatestr}'
   and extract(doy from day) = extract(doy from valid)  GROUP by o.station
   ORDER by o.station ASC
EOF;

reset($s);
if (count($s) > 0){
	$pgconn = iemdb('coop');
	$rs = pg_query($pgconn, $sql);
	while (list($k,$v)=each($s)){
		$row = pg_fetch_assoc($rs, $k);
		$table .= sprintf("<tr><td>"
				."<input type=\"checkbox\" name=\"r[]\" value=\"%s\" >"
				." %s</td><td>%s</td>"
				."<td>%.2f</td><td>%.2f</td><td>%.2f</td>"
				."<td>%.1f</td><td>%.1f</td><td>%.1f</td>"
				."<td>%.1f</td><td>%.1f</td><td>%.1f</td>"
				."<td>%s</td><td>%s</td><td>%.1f</td></tr>", 
				$v, $v, $nt->table[$v]['name'], $row["oprecip"],
				$row["cprecip"], $row["oprecip"] - $row["cprecip"],
				$row["ogdd50"],
				$row["cgdd50"], $row["ogdd50"] - $row["cgdd50"],
				$row["osdd86"],
				$row["csdd86"], $row["osdd86"] - $row["csdd86"],
				$row["maxtmpf"], $row["mintmpf"], $row["avgtmpf"]);
	}
}

$nselect = networkSelect("IACLIMATE", "IA0000", Array(), "s[]");

$t = new MyView();
$t->title = "Climodat Station Monitor";
$t->headextra = <<<EOF
<link rel="stylesheet" href="/assets/jquery-ui/1.11.2/jquery-ui.min.css" />
EOF;

$sdatestr = date("m/d/Y", $sdate);
$edatestr = date("m/d/Y", $edate);
$t->jsextra = <<<EOF
<script src="/assets/jquery-ui/1.11.2/jquery-ui.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	sdate = $("#sdate").datepicker({altFormat:"yymmdd"});
	sdate.datepicker('setDate', "$sdatestr");
	edate = $("#edate").datepicker({altFormat:"yymmdd"});	
	edate.datepicker('setDate', "$edatestr");
});
</script>
EOF;

$snice = date("d M Y", $sdate);
$today = ($edate > time()) ? time() : $edate;
$enice = date("d M Y", $today);
$t->content = <<<EOF
<ol class="breadcrumb">
 <li><a href="/climodat/">Climodat Reports</a></li>
 <li class="active">IEM Climodat Station Monitor</li>
</ol>

<p>The purpose of this page is to provide a one-stop view of summarized 
IEM Climodat data for a period of your choice.  Once you have configured 
your favorite sites, <strong>please bookmark the page</strong>.</p>
		
<form name="add">
{$hiddendates}
{$hiddenstations}
	{$nselect}
	<input type="submit" value="Add Station">
</form>

<br />

<form name="dates">
{$hiddenstations}
    Start Date: <input type="text" id="sdate" name="sdate">
    End Date: <input type="text" id="edate" name="edate">
	<input type="submit" value="Set Start/End Dates">
</form>

<hr />
<h4>The following table is valid for a period from {$snice} to {$enice}.</h4>

<p><i>"Climo"</i> is the climatology value, which is computed over the period of
1951-2015.</p>

<form name="remove">
{$hiddenstations}
{$hiddendates}
<table class="table table-bordered table-striped table-condensed">
<thead><tr><th rowspan="2">ID</th>
	<th rowspan="2">Name</th>
	<th colspan="3">Precipitation [inch]</th>
	<th colspan="3">Growing Degree Days (base 50)</th>
	<th colspan="3">Stress Degree Days (base 86)</th>
	<th colspan="3">Daily Temperature [F]</th></tr>
<tr>
	<th>Total</th><th>Climo</th><th>Departure</th>
	<th>Total</th><th>Climo</th><th>Departure</th>
	<th>Total</th><th>Climo</th><th>Departure</th>
	<th>Max</th><th>Min</th><th>Avg</th>
	</tr>
	</thead>
<tbody>{$table}</tbody>
</table>

<br /><input type="submit" value="Remove Selected Stations From List">
</form>
EOF;

$t->render('single.phtml');
?>