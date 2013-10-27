<?php
include("../../config/settings.inc.php");
include("$rootpath/include/database.inc.php");
$conn = iemdb("postgis");

$linewidth = isset($_REQUEST['linewidth']) ? intval($_REQUEST["linewidth"]): 3;
$linewidth2 = $linewidth + 2;
$sql = "SELECT max(valid) as valid from roads_current";
$rs = pg_query($conn, $sql);
$row = pg_fetch_array($rs, 0);
$valid = substr($row["valid"],0,16);
$ts = strtotime($valid);
$valid = strftime("%I:%M %p on %d %b %Y", $ts);


$tbl = "roads_current";
if (isset($_GET["test"])){ $tbl = "roads_current_test"; }

$sql = "SELECT ST_askml(simple_geom) as kml,
      * from $tbl r, roads_base b, roads_conditions c WHERE
  r.segid = b.segid and r.cond_code = c.code";

$rs = pg_query($conn, $sql);

header("Content-Type: application/vnd.google-earth.kml+xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<kml xmlns=\"http://earth.google.com/kml/2.2\">
 <Document>
 <ScreenOverlay id=\"legend_bar\">
   <visibility>1</visibility>
   <Icon>
       <href>http://mesonet.agron.iastate.edu/kml/timestamp.php?label=Roads:%20". urlencode($valid) ."</href>
   </Icon>
   <description>WaterWatch Legend</description>
   <overlayXY x=\".3\" y=\"0.99\" xunits=\"fraction\" yunits=\"fraction\"/>
   <screenXY x=\".3\" y=\"0.99\" xunits=\"fraction\" yunits=\"fraction\"/>
   <size x=\"0\" y=\"0\" xunits=\"pixels\" yunits=\"pixels\"/>
  </ScreenOverlay>
<Style id=\"code0\">
  <LineStyle><width>${linewidth}</width><color>ffffffff</color></LineStyle>
</Style>
<Style id=\"code1\">
  <LineStyle><width>${linewidth}</width><color>ff00CC00</color></LineStyle>
</Style>
<Style id=\"code3\">
  <LineStyle><width>${linewidth}</width><color>fff0f000</color></LineStyle>
</Style>
<Style id=\"code7\">
  <LineStyle><width>${linewidth}</width><color>fff0f000</color></LineStyle>
</Style>
<Style id=\"code11\">
  <LineStyle><width>${linewidth}</width><color>fff0f000</color></LineStyle>
</Style>
<Style id=\"code15\">
  <LineStyle><width>${linewidth}</width><color>ffffc5c5</color></LineStyle>
</Style>
<Style id=\"code19\">
  <LineStyle><width>${linewidth}</width><color>fffe3299</color></LineStyle>
</Style>
<Style id=\"code23\">
  <LineStyle><width>${linewidth}</width><color>ffb500b5</color></LineStyle>
</Style>
<Style id=\"code27\">
  <LineStyle><width>${linewidth}</width><color>ffffc5c5</color></LineStyle>
</Style>
<Style id=\"code31\">
  <LineStyle><width>${linewidth}</width><color>fffe3399</color></LineStyle>
</Style>
<Style id=\"code35\">
  <LineStyle><width>${linewidth}</width><color>ffb500b5</color></LineStyle>
</Style>
<Style id=\"code39\">
  <LineStyle><width>${linewidth}</width><color>ffdcdcdc</color></LineStyle>
</Style>
<Style id=\"code43\">
  <LineStyle><width>${linewidth}</width><color>ff0099fe</color></LineStyle>
</Style>
<Style id=\"code47\">
  <LineStyle><width>${linewidth}</width><color>ff00009E</color></LineStyle>
</Style>
<Style id=\"code51\">
  <LineStyle><width>${linewidth}</width><color>ffe85f01</color></LineStyle>
</Style>
<Style id=\"code56\">
  <LineStyle><width>${linewidth}</width><color>ffffc5c5</color></LineStyle>
</Style>
<Style id=\"code60\">
  <LineStyle><width>${linewidth}</width><color>fffe3399</color></LineStyle>
</Style>
<Style id=\"code86\">
  <LineStyle><width>${linewidth2}</width><color>ffff0000</color></LineStyle>
</Style>
";

for ($i=0;$row=@pg_fetch_array($rs,$i);$i++)
{
  $minor = $row["minor"];
  $major = $row["major"];


  echo "<Placemark>
    <description>
        <![CDATA[
  <p><font color=\"red\"><i>Road:</i> $major :: $minor</font>
  <br /><font color=\"red\"><i>Status:</i> ". $row["raw"] ."</font> 
   </p>
        ]]>
    </description>
    <styleUrl>#code".$row["cond_code"] ."</styleUrl>
    <name>$major $minor</name>\n";
  echo $row["kml"];
  echo "</Placemark>";
}
echo "</Document>
</kml>";


?>
