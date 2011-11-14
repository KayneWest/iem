<?php
putenv("TZ=GMT");
include("../../config/settings.inc.php");
include("$rootpath/include/database.inc.php");
$mos = iemdb("mos");
pg_exec($mos, "SET TIME ZONE 'GMT'");


$station = isset($_GET["station"])? $_GET["station"]: "KAMW";
$ts = isset($_GET["valid"])? strtotime($_GET["valid"]): time();
$year = date("Y", $ts);
if ($year < 2000){
	exit();
}

$rs = pg_prepare($mos, "SELECTOR", "select *, t06_1 ||'/'||t06_2 as t06, 
                 t12_1 ||'/'|| t12_2 as t12  from t${year} WHERE station = $1
                 and ftime >= $2 and ftime <= ($2 + '10 days'::interval) ORDER by ftime ASC");


if (isset($_GET["runtime"]) && isset($_GET["model"])){
  $ts = strtotime($_GET["runtime"]);
  $year = date("Y", $ts);
  $rs = pg_prepare($mos, "SELECTOR2", "select *, t06_1 ||'/'||t06_2 as t06, 
                 t12_1 ||'/'|| t12_2 as t12  from t${year} WHERE station = $1
                 and runtime = $2 and model = $3 ORDER by ftime ASC");
  $rs = pg_execute($mos, "SELECTOR2", Array($station, date("Y-m-d H:i",$ts),
        $_GET["model"]));
} else {
  $rs = pg_execute($mos, "SELECTOR", Array($station, date("Y-m-d H:i",$ts)));
}

header("Content-type: text/plain");

$ar = Array("station", "model", "runtime", "ftime", "n_x", "tmp", "dpt",
            "cld", "wdr", "wsp", "p06", "p12", "q06", "q12","t06", "t12",
             "snw", "cig", "vis", "obv", "poz", "pos", "typ");

echo implode($ar, ",") ."\n";
for ($i=0;$row=@pg_fetch_array($rs,$i);$i++)
{
  reset($ar);
  while (list($k,$v) = each($ar))
  {
    echo sprintf("%s,", $row[$v]);
  }
  echo "\n";
}
