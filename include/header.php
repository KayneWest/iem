<?php
  include("$rootpath/include/catch_phrase.php");
  srand ((float) microtime() * 10000000);
  $t = array_rand($phrases);
  $phrase = $phrases[$t];

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
 <title><?php echo isset($TITLE) ? $TITLE: "Iowa Environmental Mesonet"; ?></title>
 <link rel="stylesheet" type="text/css" href="<?php echo $rooturl; ?>/css/main.css?v=0.0.2" />
 <?php if (isset($REFRESH)){ echo $REFRESH; } ?>
 <?php if (isset($HEADEXTRA)){ echo $HEADEXTRA;} ?>
</head>
<body <?php if (isset($BODYEXTRA)){ echo $BODYEXTRA;} ?>>
<div id="iem-main">
<div id="iem-header">
<?php include("$rootpath/include/webring.html"); ?>
<div id="iem_header_logo">
<a href="<?php echo $rooturl; ?>/"><img src="<?php echo $rooturl; ?>/images/logo_small.gif" alt="IEM" /></a>
</div>
                                                                                
<div id="iem-header-title">
<h3>Iowa Environmental Mesonet</h3>
<h4>Iowa State University Department of Agronomy</h4>
</div>
                                                                                
<div id="iem-header-items">
<i><?php echo $phrase; ?></i>
</div>
<?php
$_pages = Array(
 "archive" => Array(
    "base" => Array("title" => "Archive", "url" => "/archive/"),
    "browse" => Array("title" => "Browse data/", "url" => "/archive/data/"),
 ),
 "current" => Array(
    "base" => Array("title" => "Current", "url" => "/current/"),
    "advanced" => Array("title" => "Advanced Products", "url" => "/current/misc.phtml"),
    "sort" => Array("title" => "Sortable Currents", "url" => "/my/current.phtml"),
    "surface" => Array("title" => "Surface Data", "url" => "/current/"),
    "radar" => Array("title" => "RADAR & Satellite", "url" => "/current/radar.phtml"),
 ),
 "climatology" => Array(
    "base" => Array("title" => "Climatology", "url" => "/climate/"),
    "climodat" => Array("title" => "Climodat", "url" => "/climodat/"),
 ),
 "networks" => Array(
    "base" => Array("title" => "IEM Networks", "url" => "/"),
    "asos" => Array("title" => "ASOS", "url" => "/ASOS/"),
    "awos" => Array("title" => "AWOS", "url" => "/AWOS/"),
    "coop" => Array("title" => "NWS COOP", "url" => "/COOP/"),
    "dcp" => Array("title" => "DCP", "url" => "/DCP/"),
    "agclimate" => Array("title" => "ISU AG", "url" => "/agclimate/"),
    "flux" => Array("title" => "NSTL Flux", "url" => "/nstl_flux/"),
    "rwis" => Array("title" => "RWIS", "url" => "/RWIS/"),
    "scan" => Array("title" => "SCAN", "url" => "/scan/"),
    "schoolnet" => Array("title" => "SchoolNet", "url" => "/schoolnet/"),
    "other" => Array("title" => "Other", "url" => "/other/"),
 ),
 "sites" => Array(
    "base" => Array("title" => "IEM Sites", "url" => "/sites/locate.php"),
    "main" => Array("title" => "Mainpage", "url" => "/sites/locate.php"),
    "info" => Array("title" => "Info", "url" => "/info.php"),
    "qc" => Array("title" => "Quality Control", "url" => "/QC/"),
 ),
 "gis" => Array(
    "base" => Array("title" => "GIS", "url" => "/GIS/"),
    "ogc" => Array("title" => "OGC Webservices", "url" => "/ogc/"),
    "rainfall" => Array("title" => "Rainfall Data", "url" => "/rainfall/"),
    "software" => Array("title" => "Software", "url" => "/GIS/software.php"),
 ),
 "severe" => Array(
    "base" => Array("title" => "Severe Weather", "url" => "/current/severe.phtml"),
    "main" => Array("title" => "Mainpage", "url" => "/current/severe.phtml"),
    "cow" => Array("title" => "IEM Cow", "url" => "/cow/"),
    "iembot" => Array("title" => "iembot", "url" => "/projects/iembot/"),
    "interact" => Array("title" => "Interact Radar", "url" => "/GIS/apps/rview/warnings.phtml"),
    "watch" => Array("title" => "SPC Watches", "url" => "/GIS/apps/rview/watch.phtml"),
    "vtec" => Array("title" => "VTEC Browser", "url" => "/GIS/apps/rview/warnings_cat.phtml"),
 ),
 "webcam" => Array(
    "base" => Array("title" => "Web Cams", "url" => "/current/camera.phtml"),
    "still" => Array("title" => "Still Images", "url" => "/current/camera.phtml"),
    "loop" => Array("title" => "Loops", "url" => "/current/bloop.phtml"),
    "lapse" => Array("title" => "Recent Movies", "url" => "/current/camlapse/"),
    "cool" => Array("title" => "Cool Lapses", "url" => "/cool/"),
 ),
);
$THISPAGE = isset($THISPAGE) ? $THISPAGE : "networks-base";
$ar = split("-", $THISPAGE);
if (sizeof($ar) == 1) $ar[1] = "";
echo "<div id=\"iem_nav\"><ul>\n";
$b = "";
while( list($idx, $page) = each($_pages) )
{
  echo sprintf("<li%s><a href=\"%s\">%s</a></li>", 
      ($ar[0] == $idx) ? " class=\"selected\"" : " ",
      $rooturl . $page["base"]["url"], $page["base"]["title"]);
  if ($ar[0] == $idx)
  {
    $b .= "<div id=\"iem_subnav\"><ul>\n";
    while( list($idx2, $page2) = each($page) )
    {
       if ($idx2 == "base") continue;
       $b .= sprintf("<li%s><a href=\"%s\">%s</a></li>", 
         ($ar[1] == $idx2) ? " class=\"selected\"" : " ",
          $rooturl . $page[$idx2]["url"], 
     ($ar[1] == $idx2) ? "[ ". $page[$idx2]["title"] ." ]": $page[$idx2]["title"] );
    }
    $b .= "</ul></div>\n";
  }
}
echo "<ul></div> $b";
?>

 
</div><!-- End of iem-header -->
<div id="iem-content">
