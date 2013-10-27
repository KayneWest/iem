<?php
/* Generate GR placefile of webcam stuff */
include("../../config/settings.inc.php");
include("$rootpath/include/database.inc.php");
$network = isset($_GET["network"]) ? $_GET["network"] : "KCCI"; 

header("Content-Type: application/vnd.google-earth.kml+xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<kml xmlns=\"http://earth.google.com/kml/2.2\">
 <Document>
   <Style id=\"iemstyle\">
     <IconStyle>
      <scale>0.8</scale>
      <Icon>
        <href>http://maps.google.com/mapfiles/kml/shapes/webcam.png</href>
      </Icon>
     </IconStyle>
     <BalloonStyle>
      <bgColor>ffffffff</bgColor>
    </BalloonStyle>
  </Style>";

$conn = iemdb("mesosite");
pg_prepare($conn, "SELECT", "SELECT *, ST_x(geom) as lon, ST_y(geom) as lat
		from camera_current c JOIN webcams w
		on (w.id = c.cam) WHERE 
		valid > (now() - '30 minutes'::interval) and network = $1");
$rs = pg_execute($conn, "SELECT", Array($network)); 
for ($i=0;$row=@pg_fetch_array($rs,$i);$i++)
{
  echo "<Placemark>
    <name>". str_replace('&', '&amp;', $row["name"]) ."</name>
    <description>
<![CDATA[
  <p><img src=\"http://mesonet.agron.iastate.edu/data/camera/stills/". $row["cam"] .".jpg\" /></p>
        ]]>
    </description>
    <styleUrl>#iemstyle</styleUrl>
    <Point>
       <coordinates>". $row["lon"] .",". $row["lat"] .",0</coordinates>
    </Point>
</Placemark>";
}
echo "</Document></kml>";
?>
