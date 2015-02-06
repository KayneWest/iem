<?php 
/*
 * Datasets listing app
 */
include_once "../../config/settings.inc.php";
define("IEM_APPID", 84);
require_once "../../include/database.inc.php";
$mesosite = iemdb("mesosite");
require_once "../../include/myview.php";
$t = new MyView();
$t->title = "IEM Datasets";

$rs = pg_query($mesosite, "SELECT * from iemdatasets ORDER by name ASC");

$table = "";
for($i=0; $row=@pg_fetch_assoc($rs,$i); $i++){
	$homepage = sprintf("<a class=\"btn btn-default\" href=\"%s\">"
			."<i class=\"glyphicon glyphicon-home\"></i> Dataset Homepage</a>",
			$row["homepage"]);
	$download = sprintf("<a class=\"btn btn-default\" href=\"%s\">"
			."<i class=\"glyphicon glyphicon-download\"></i> Download</a>",
			$row["homepage"]);
	$table .= sprintf("<tr>"
		."<td><h3>%s <small>since: %s</small></h3></td>"
		."<td>%s %s</td>"
		."</tr>"
		."<tr><td colspan=\"2\">"
			."<p><strong>Description:</strong> %s</p>"
			."<p><strong>Why the IEM collects this dataset?</strong> %s</p>"
			."<p><strong>Third Party alternatives to this website?</strong> %s</p>"
		."</td></tr>", 
			$row["name"], $row["archive_begin"],
			$homepage, $download, $row["description"],
			$row["justification"], $row["alternatives"]);
}


$t->content = <<<EOF

<h2>IEM Dataset Listing</h2>
		
<p>This page attempts to provide an authoritative listing of 
 <strong>all</strong> (that's the goal at least) datasets curated by the IEM.
 Hopefully this helps folks find the obscure rabbit hole that may have the 
 data you are seeking!

<div class="table-responsive">
<table class="table table-striped table-bordered">
{$table}
</table> 
</div>
EOF;



$t->render('single.phtml');
?>