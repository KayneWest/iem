<?php
/* Print last two days worth of some PIL */
include("../../../config/settings.inc.php");
include("../../../include/database.inc.php");

$pil = isset($_GET['pil']) ? strtoupper(substr($_GET['pil'],0,3)) : "AFD";

$conn = iemdb("afos");
$rs = pg_prepare($conn, "_LSELECT", "SELECT data from products
        WHERE substr(pil,1,3) = $1 
		and entered between now() - '48 hours'::interval and now()
        ORDER by entered DESC");
$rs = pg_execute($conn, "_LSELECT", Array($pil)); 

$content = "";
if (pg_numrows($rs) < 1){
	$content .= "ERROR: No products found in past 48 hours.";
}
for ($i=0; $row = @pg_fetch_assoc($rs, $i); $i++)
{
	$d = preg_replace("/\r\r\n/", "\n", $row["data"]);
	$d = preg_replace("/\001/", "", $d);

    $content .= $d;

}

header("Content-type: text/plain");
echo $content;
?>