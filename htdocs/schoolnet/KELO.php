<a href="alerts/">About wind gust alerts</a> sent to the Weather Service.

<table width="100%" bgcolor="white" border=0>
<tr><td colspan=2>KELO's WeatherNet sites were just to the IEM on 11 Sept
2002.</td></tr>

<tr><td valign="top" width="300">

<p><h3 class="subtitle">Current Data</h3>
<ul>
 <li><a href="current.php">Current Conditions</a> (sortable)</li>
 <li><a href="<?php echo $rooturl; ?>/GIS/apps/snet/raining.php">Where's it raining?</a></li>
</ul>

<p><h3 class="subtitle">Station Plots</h3>
<ul>
 <li><a href="/data/kelo/mesonet.gif">20 Min Mesonet [SchoolNet]</a></li>
 <li><a href="<?php echo $rooturl; ?>/GIS/apps/mesoplot/plot.php?network=KELO">Rapid Update</a></li>
 <li><a href="/data/kelo/solarRad.gif">Solar Radiation</a></li>
 <li><a href="/data/kelo/snet_altm.gif">Barometer</a></li>
 <li><a href="/data/kelo/precToday.gif">Today's Precip Accum</a></li>
</ul>

<p><h3 class="subtitle">Historical Data</h3><br><ul>
 <li><a href="/schoolnet/dl/">Download</a> from the archive!</a></li>
 <li><a href="/cgi-bin/precip/catSNET.py">Hourly Precipitation</a>
 tables</a></li></ul>

<p><h3 class="subtitle">QC Info</h3>
<ul>
 <li><a href="/QC/offline.php">Stations Offline</a> [<a href="<?php echo $rooturl; ?>/GIS/apps/stations/offline.php?network=snet">Graphical View</a>]</li>
 <li><a href="/QC/madis/network.phtml?network=KELO">MADIS QC Values</a></li>
</ul>


</td><td valign="top" width="350">

  <p><div class="snet-precip-table">
     <b>Precipitation Totals</b>
  <div style="background: white; padding: 3px;">
    <a href="/data/kelo/precToday.gif">Today</a> &nbsp;
    <?php
  echo "<a href=\"/archive/data/". date("Y/m/d/", (date("U") - 86400 ) )
    ."keloPrec.gif\">Yesterday</a> &nbsp; \n";
  for ($i=2;$i<8;$i++){
    echo "<a href=\"/archive/data/". date("Y/m/d/", (date("U") - $i*86400 ) )
    ."keloPrec.gif\">". date("M d", (date("U") - $i*86400 ) )  ."</a> &nbsp; \n";
  }
  ?>
   <br><a href="/data/kelo/precMonth.gif">This Month</a>

  </div>
  </div>




    <a href="/schoolnet/kelo/totals/0210_pmon.gif">October 2002</a> &nbsp;
  </div>
  </div><br>

<p><h3 class="subtitle">Plotting Time Series</h3>
<ul>
 <li><a href="/plotting/snet/1station.php">1 station</a> [20 minute data]</li>
 <li><a href="/plotting/snet/1station_1min.php">1 station</a> [1 minute data]</li>
 <li><a href="/plotting/compare/">Generate Interactive Comparisons</a> between two sites of your choice.</li>
</ul></p>

</td></tr></table>

</td></tr></table>
