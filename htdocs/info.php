<?php
include("../config/settings.inc.php");
include("../include/myview.php");
$t = new MyView();
define("IEM_APPID", 61);
$t->title = "Information Mainpage";
$t->thispage = "iem-base";

$t->content = <<<EOF
<h3>Information/Documents</h3><p>

<div class="row">
<div class="col-md-6 col-sm-6">
		
<h3 class="subtitle">Quick Links:</h3></p>
<ul>
<li><a href="/info/iem.php">IEM Info/Background</a></li>
<li><a href="/info/members.php">IEM Partners</a></li>
<li><a href="/info/links.php">Links</a></li>
<li><a href="/info/variables.phtml">Variables Collected</a></li></ul>

<p>Information about requesting a <a href="/request/ldm.php">real-time data feed</a>
<p>
<h3 class="subtitle">Station Locations: (graphical)</h3>
<ul>
	<li><a href="/sites/locate.php?network=IA_ASOS">ASOS Locations</a></li>
	<li><a href="/sites/locate.php?network=AWOS">AWOS Locations</a></li>
	<li><a href="/sites/locate.php?network=IA_RWIS">RWIS Locations</a></li>
	<li><a href="/sites/locate.php?network=IA_COOP">COOP Locations</a></li>
	<li><a href="/sites/locate.php?network=ISUAG">ISU Agclimate Locations</a></li>
</ul>

</div>
<div class="col-md-6 col-sm-6">

<h3 class="subtitle">IEM Server Information:</h3>
<ul>
	<li><a href="/info/software.php">Software Utilized</a></li>
	<li><a href="/mailman/listinfo/">Mailing Lists</a></li>
</ul>

<h3 class="subtitle">Papers/Presentations</h3>
<ul>
  <li><a href="/pubs/seniorthesis/">ISU Senior Thesis Presentations</a></li>
  <li><a href="/present/">IEM Presentation Archive</a></li>
  <li><a href="/docs/unidata2006">Unidata Equipment Grant Report</a> (21 Aug 2006)</li>
</ul>

</div></div>

EOF;
$t->render('single.phtml');
?>