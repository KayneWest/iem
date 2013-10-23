<?php
header("Content-type: text/plain");

/*
 * Iowa DOT provided state road conditions
 */
include("../../../config/settings.inc.php");

// Try to get it from memcached
$memcache = new Memcache;
$memcache->connect('iem-memcached', 11211);
$val = $memcache->get("/request/grx/roadcond.php");
if ($val){
	die($val);
}
ob_start();

include("../../../include/database.inc.php");
$conn = iemdb('postgis');

$colors = Array(
   0 => "255 255 255",
    1 => "0 204 0",
    3 => "152 152 152",
    7 => "152 152 152",
    11 => "152 152 152",
    15 => "255 197 197",
    19 => "254 51 153",
    23 => "181 0 181",
    27 => "255 197 197",
    31 => "254 51 153",
    35 => "181 0 181",
    39 => "153 255 255",
    43 => "0 153 254",
    47 => "0 0 158",
    51 => "232 95 1",
    56 => "255 197 197",
    60 => "254 51 153",
    64 => "181 0 181",
    86 => "125 0 0");



echo "Threshold: 999
Title: IEM Delivered Iowa Road Conditions
Refresh: 5
";

$rs = pg_query($conn, "SELECT astext(transform(simple_geom,4326)) as t, 
		* from roads_current r, roads_base b, roads_conditions c 
		WHERE r.segid = b.segid and r.cond_code = c.code 
		and b.geom is not null");

for ($i=0;$row= @pg_fetch_array($rs,$i);$i++)
{
  $meat = str_replace("MULTILINESTRING((", "", $row["t"]);
  $meat = str_replace("))", "", $meat);
  $segments = explode("),(", $meat);
  $valid = strtotime( substr($row["valid"],0,16) );
  echo "Color: ". $colors[$row["cond_code"]] ."\n";
  while(list($q,$seg) = each($segments))
  {
    echo "Line: 4, 0, ". $row["major"] ." :: ". $row["minor"]  ."\\nReport: ". $row["raw"] ."\\nTime: ". date('j M Y g:i A', $valid) ."\n";
    $tokens = explode(",", $seg);
    while (list($p,$s) = each($tokens)){
      $t = explode(" ", $s);
      echo sprintf("  %.5f,%.5f", $t[1], $t[0]) ."\n";
    }
    echo "End:\n";
  }

}

$memcache->set("/request/grx/roadcond.php", ob_get_contents(), false, 300);
ob_end_flush();
?>
