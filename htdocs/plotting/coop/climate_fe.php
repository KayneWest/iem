<?php 
include("../../../config/settings.inc.php");
include("../../../include/imagemaps.php");     
include_once "../../../include/forms.php";
include_once "../../../include/myview.php";
$t = new MyView();
$t->title = "COOP Climate Plots";

$station1 = isset($_GET["station1"]) ? $_GET["station1"] : "IA0000";
$station2 = isset($_GET["station2"]) ? $_GET["station2"] : null;
$mode = isset($_GET["mode"]) ? $_GET["mode"]: "";

$t->thispage = "networks-coop";

$imgurl = sprintf("/cgi-bin/climate/daily.py?p=daily&station1=%s", $station1);
if ($mode == 'c'){
	$imgurl .= sprintf("&station2=%s", $station2);
}

$ar = Array("o" => "One Station", "c" => "Compare Two");
$modeselect = make_select("mode", $mode, $ar);

$s1 = networkSelect("IACLIMATE", $station1, Array(), "station1");
$s2 = networkSelect("IACLIMATE", $station2, Array(), "station2");

$t->content = <<<EOF
<h3>Daily Climatology</h3>

<p>This application dynamically generates plots of the daily average high
and low temperature for climate locations tracked by the IEM.  You can optionally
plot two stations at once for a visual comparison.</p>


     <b>Make Plot Selections:</b>


<form method="GET" action="climate_fe.php">

<table cellpadding='3' border='1' cellspacing='0'>
<tr>
  <th class="subtitle">Station 1</th>
  <th class="subtitle">Station 2</th>
  <td></td>
  <td></td>
</tr>

<tr>
<td>{$s1}</td>
<td>{$s2}</td>
<td>
{$modeselect}
</td>

<td>
<input type="SUBMIT" value="Make Plot">

</form>
</td>

</tr></table>



<img src="$imgurl">
EOF;
$t->render('single.phtml');
?>
