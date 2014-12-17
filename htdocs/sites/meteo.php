<?php 
include("../../config/settings.inc.php");
include("../../include/database.inc.php");
include("../../include/forms.php");
include("setup.php");
include("../../include/myview.php");
$t = new MyView();
$t->thispage = "iem-sites";
$t->title = "Site Meteorograms";
$t->sites_current="meteo"; 

$uri = sprintf("/plotting/auto/plot/43/station:%s::network:%s.png",  $station,
		$network);
$t->content = <<<EOF

<p>This page creates a simple plot of recent observations from this site.</p>

 <br /><img src="{$uri}" class="img img-responsive">
EOF;
$t->render('sites.phtml');
?>
