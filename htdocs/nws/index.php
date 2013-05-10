<?php
include("../../config/settings.inc.php");
$TITLE = "IEM | NWS Related Information";
$THISPAGE = "iem-info";
include("$rootpath/include/header.php"); ?>

<h3 class="heading">IEM Data for NWS Users</h3><p>

<div class="warning">Please <a href="<?php echo $rooturl; ?>/info/contacts.php">suggest</a> features for this page.  We are looking to collect all relevant
IEM provided archives/applications of NWS data.</div>

<table>
<tr><td valign="top" width="50%">
<h4>IEM Apps</h4>
<ul>
 <li><a href="obs.php">Sortable Currents by WFO</a></li>
 <li><a href="../DCP/plot.phtml">Archived DCP Data Plotter</a>
 <br />Simple app to plot out current/historical DCP (river gauges) data for a site
 of your choice.</li>
 <li><a href="/timemachine/#59.0">NWS WWA Map Archive</a>
 <br />The IEM saves the national watch, warning, and advisory (WWA) map every
 five minutes.</li>
</ul>

<h4>Iowa AWOS RTP First Guess</h4>
<blockquote>The IEM processes an auxillary feed of Iowa AWOS data direct
from the Iowa DOT.  This information is used to produce a more accurate
first guess at fields the NWS needs for their RTP product.</blockquote>
<ul>
 <li><a href="../data/awos_rtp_00z.shef">0Z SHEF</a></li>
 <li><a href="../data/awos_rtp.shef">12Z SHEF</a></li>
</ul>

<h4>Model Data</h4>
<ul>
 <li><a href="../mos/">Model Output Statistics</a>
 <br />Archive of MOS back to 3 May 2007.</li>
</ul>

<h4>NEXRAD / RADAR Data</h4>
<ul>
 <li><a href="/request/gis/nexrad_storm_attrs.php">Archived NEXRAD Storm Attributes</a>
 <br />Download shapefiles of NEXRAD storm attribute data.</li>
</ul>


</td>
<td valign="top" width="50%">
<h4>Valid Time Extent Code (VTEC) Apps</h4>
<ul>
 <li><a href="vtec_obs.php">ASOS/AWOS Obs during WFO WWA</a>
  <br />Prints out ASOS/AWOS observations during selected VTEC warning types.</li>
 <li><a href="days_since.php">Days Since VTEC Product</a>
  <br />Map of the number of days since a WFO issued a VTEC Product.</li>
 <li><a href="../vtec/">VTEC Browser</a>
  <br />Interactive display of VTEC products.</li>
 <li><a href="../vtec/search.php">VTEC Search by County/Zone</a>
  <br />Find issued VTEC WWA products by a given zone or county.</li>
</ul>

<h4>Storm Based Warnings</h4>
<ul>
 <li><a href="../cow/">IEM Cow</a>
  <br />Interactive Storm Based Warning verification app</li>
   <li><a href="../raccoon/">IEM Raccoon</a>
  <br />Generate Microsoft Powerpoint of Storm Based Warnings for a WFO 
  and RADAR site of your choice.</li>
 <li><a href="../request/gis/watchwarn.phtml">GIS Shapefiles</a>
  <br />of archived Storm Based Warning polygons.</li>
</ul>

<h4>Text Product Archives</h4>
<ul>
 <li><a href="../wx/afos/">AFOS Product Viewer</a>
  <br />Web based version of TextDB.</li>
 <li><a href="../wx/afos/list.phtml">View Products by WFO by Date</a>
  <br />View quick listings of issued products by forecast office and by 
    date.</li>
</ul>
</td>
</tr>
</table>

<?php include("$rootpath/include/footer.php"); ?>
