<?php 
include("../../config/settings.inc.php");
$TITLE = "IEM | QC | Sites Offline";
include("$rootpath/include/header.php"); ?>

<div class="text">
<a href="/QC/">Quality Control</a> > <b>Sites Offline</b>

<br>

<P>Unfortunately, automated observing sites occasionally go offline due
to a wide range of factors.  Here is a listing of sites currently offline.
You can also view a <a href="<?php echo $rooturl; ?>/GIS/apps/stations/offline.php">Graphical</a>
display of sites that are offline.</p>


<?php

include ("$rootpath/include/all_locs.php");
include("$rootpath/include/iemaccess.php");
$iem = new IEMAccess();

function aSortBySecondIndex($multiArray, $secondIndex) {
        while (list($firstIndex, ) = each($multiArray))
                $indexMap[$firstIndex] = $multiArray[$firstIndex][$secondIndex];        asort($indexMap);
        while (list($firstIndex, ) = each($indexMap))
                if (is_numeric($firstIndex))
                        $sortedArray[] = $multiArray[$firstIndex];
                else $sortedArray[$firstIndex] = $multiArray[$firstIndex];
        return $sortedArray;
}

function networkOffline($network)
{
  global $iem, $cities;
  $rs = pg_query($iem->dbconn, "SELECT *, to_char(valid, 'Mon DD YYYY HH:MI AM') as v from offline WHERE network = '$network' ORDER by valid ASC");

  $q = 0;
  for( $i=0; $row = @pg_fetch_array($rs,$i); $i++)
  {
     $valid = $row["v"];
     $tracker_id = $row["trackerid"];
     $station = $row["station"];
     if (! isset($cities[$station]))  continue;
     $name = $cities[$station]['city'];
     echo "<tr><td>$station</td><td>$name</td><td>$valid</td></tr>\n";
     $q = 1;
  }
  if ($q == 0){ echo "<tr><td colspan=3>All Sites Online!!!</td></tr>\n"; }
}

?>

<table width="100%"><tr>
 <th align="left">Site ID:</th>
 <th align="left">Name</th>
 <th align="left">Flagged Offline At</th></tr>

<tr><td colspan=3 class="subtitle" bgcolor="#CCCCCC"><b>KCCI School Network</b>
  [<a href="<?php echo $rooturl; ?>/GIS/apps/stations/offline.php?network=KCCI">Graphical View</a>]  (30 minute tolerance)</td></tr>
<?php networkOffline("KCCI"); ?>

<tr><td colspan=3 class="subtitle" bgcolor="#CCCCCC"><b>KELO School Network</b>
  [<a href="<?php echo $rooturl; ?>/GIS/apps/stations/offline.php?network=KELO">Graphical View</a>]  (3 hour tolerance)</td></tr>
<?php networkOffline("KELO"); ?>

<tr><td colspan=3 class="subtitle" bgcolor="#CCCCCC"><b>KIMT School Network</b>
  [<a href="<?php echo $rooturl; ?>/GIS/apps/stations/offline.php?network=KIMT">Graphical View</a>]  (30 minute tolerance)</td></tr>
<?php networkOffline("KIMT"); ?>

<tr>
 <td colspan=3 class="subtitle" bgcolor="#CCCCCC"><b>RWIS Network</b>
  [<a href="<?php echo $rooturl; ?>/GIS/apps/stations/offline.php?network=IA_RWIS">Graphical View</a>]  (1 hour tolerance)</td>
</tr>
<?php networkOffline("IA_RWIS"); ?>

<tr>
 <td colspan=3 class="subtitle" bgcolor="#CCCCCC"><b>AWOS Network</b>
  [<a href="<?php echo $rooturl; ?>/GIS/apps/stations/offline.php?network=AWOS">Graphical View</a>] (90 minute tolerance)</td>
</tr>
<?php networkOffline("AWOS"); ?>

</table>

<p>

<p></div>

<?php include("$rootpath/include/footer.php"); ?>

