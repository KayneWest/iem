<?php
 /* This bad boy converts a PNG to a geo-tiff */

$dstr = isset($_GET["dstr"]) ? $_GET["dstr"]: die("No dstr set");
$year = intval( substr($dstr,0,4));
$month = intval( substr($dstr,4,2));
$day = intval( substr($dstr,6,2));
$hour = intval( substr($dstr,8,2));
$minute = intval( substr($dstr,10,2));
$ts = mktime($hour,$minute,0,$month,$day,$year);

 @mkdir("/tmp/png2gtiff");
 chdir("/tmp/png2gtiff");

$inFile = sprintf("/mnt/a1/ARCHIVE/data/%s/GIS/uscomp/n0r_%s.png", date("Y/m/d", $ts), date("YmdHi", $ts) );
$outFile = sprintf("n0r_%s", date("YmdHi", $ts) );
$zipFile = sprintf("n0r_%s.zip", date("YmdHi", $ts) );
if (! is_file($inFile)) die("No GIS composite found for this time!");

$cmd = sprintf("/mesonet/local/bin/gdalwarp -co \"WORLDFILE=ON\" -of GTIFF %s %s.tif", $inFile, $outFile);
`$cmd`;

`cp /mesonet/data/gis/meta/4326.prj ${outFile}.prj`;
$cmd = "zip $zipFile ${outFile}.tif ${outFile}.tfw ${outFile}.prj";
`$cmd`;

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=${zipFile}");

readfile($zipFile);

unlink($zipFile);
unlink("${outFile}.tif");
unlink("${outFile}.tfw");
unlink("${outFile}.prj");

?>
