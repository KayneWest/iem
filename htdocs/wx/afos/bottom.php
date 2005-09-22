<html>
<head>
<style type="text/css">
P { 
 width: 600px;
 text-indent: 2em; } 
body{
 margin-left: 5px;
 border-left-width: 3px; 
 border-color: blue;
}
</style>


</head>
<body bgcolor="#eeeeee">

<h3>NWS Text Product Finder</h3>

<p>Using our local <a href="http://www.unidata.ucar.edu/projects/idd/index.html">UNIDATA IDD</a>
 data feed, a simple script archives <b>recent</b> NWS text products into a database.  The above form allows
you to query this database for recent products.  You must know 
the <a href="http://205.156.54.206/nwws/listings.htm">AFOS PIL</a> in order to 
get the products you want.</p>

<div style="float: left; margin: 5px; padding: 5px; border: 1px dashed; background: #eeeeee;">
<b>MOS PILS</b>
<pre>
PIL       Description             Product ID for Des Moines
MAVxxx    GFS MOS Guidance         MAVDSM
METxxx    Eta MOS Guidance         METDSM
MEXxxx    GFSX MOS Guidance        MEXDSM
FWCxxx    NGM MOS                  FWCDSM

And model output

FRHxx     Eta Output               FRH68
FRHTxx    NGM Output               FRHT68
</pre>

<b>Other Favorites:</b>
<pre>
REPNT2  NHC Vortex Message
PMDHMD  Model Diagnostic Discussion
PMDSPD  Short Range Prognostic Discussion
PMDEPD  Extended Forecast Discussion
SWOMCD  SPC Mesoscale Discussion
SWODY1  SPC Day 1
SWODY2  SPC Day 2
AFDDMX  Des Moines WFO Area Forecast Discussion
SELX    Convective Watch where "X" is a number between 0-9
</pre>
</div>

<p>The archive maintains products from at least the most recent 7 days.  A daily scrubber 
runs at 3:30 AM each day to reindex the products and delete old products, so the query 
interface is off-line at that time. This interruption should only last 10 minutes.</p>

<p>Please do not depend on this page for operational decision making, errors can and do occur
with data processing, data reception and any other error possible with Internet communications.
All products should be used for educational purposes only.</p>

<ul>
 <li><a href="http://www.nws.noaa.gov/datamgmt/x_ref/xr04_X_ref_by_NNN.html">NNN Categories</a></li>
</ul>

<p>21 Jan 2002 -- Daryl Herzmann (akrherz@iastate.edu)</p>

</body>
</html>
