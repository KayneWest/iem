<?php
/** live.php request a live shot! */
include("../../config/settings.inc.php");
include("../../include/cameras.inc.php");

$id = isset($_GET["id"]) ? $_GET["id"] : "KCCI-001";

if ($id == 'KCWI-001') die();
if (! $cameras[$id]["active"]) die();

$memcache = new Memcache;
$memcache->connect('iem-memcached', 11211);
$val = $memcache->get("live_". $id);
if ($val){
	header("Content-type: image/jpeg");
	die($val);
}
ob_start();
$ip = $cameras[$id]["ip"];

$u = $camera_user[ $cameras[$id]['network'] ];
$p = $camera_pass[ $cameras[$id]['network'] ];
$port = $cameras[$id]['port'];
$im = @imagecreatefromjpeg("http://${u}:${p}@$ip:${port}/-wvhttp-01-/GetOneShot");

if (! $im ) die();

$white = imagecolorallocate($im, 250, 250, 250);
$black = imagecolorallocate($im, 0, 0, 0);
imagefilledrectangle($im, 3, 465, 637, 480, $black);
imagestring($im, 5, 15, 465, date("d M Y h:i:s A, ") . $cameras[$id]["name"] ." Live Shot!", $white);

header("Content-type: image/jpeg");
imagejpeg($im);
$memcache->set("live_". $id, ob_get_contents(), false, 15);
ob_end_flush();
?>
