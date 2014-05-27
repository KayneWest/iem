<?php
/* Create animated GIF! and then send it to them... */

$fts = isset($_GET["fts"]) ? intval($_GET["fts"]): exit();

$memcache = new Memcache;
$memcache->connect('iem-memcached', 11211);
$urls = $memcache->get("/GIS/apps/rview/warnings.phtml?fts=${fts}");
if (!$urls){
	die("fts not found, ERROR");
}
chdir("/var/webtmp");
$cmdstr = "gifsicle --colors 256 --loopcount=0 --delay=100 -o ${fts}_anim.gif ";
while (list($k,$v)=each($urls)) {
	$res = file_get_contents("http://iem.local{$v}");
	$fn = "{$fts}_{$k}.png";
	$gfn = "{$fts}_{$k}.gif";
	$f = fopen($fn, 'wb');
	fwrite($f, $res);
	fclose($f);
	`convert $fn $gfn`;
	$cmdstr .= " {$gfn} ";
}

`$cmdstr`;

 header("Content-type: application/octet-stream");
 header("Content-Disposition: attachment; filename=myanimation.gif");

 readfile("${fts}_anim.gif");
?>
