<?php

function networkMultiSelect($network, $selected, $extra=Array())
{
    global $rootpath;
    $network = strtoupper($network);
    $s = "";
    include_once("$rootpath/include/all_locs.php");
    reset($cities);
    $s .= '<select name="station[]" size="5" MULTIPLE >\n';
    while (list($sid, $tbl) = each($cities))
    {
        if ($tbl["network"] != $network) continue;
        $s .= "<option value=\"$sid\" ";
        if ($selected == $sid) { $s .= "SELECTED"; }
        $s .= ">[$sid] ". $tbl["city"] ."</option>\n";
   }
   while (list($idx,$sid) = each($extra))
   {
        $tbl = $cities[$sid];
        $s .= "<option value=\"$sid\" ";
        if ($selected == $sid) { $s .= "SELECTED"; }
        $s .= ">[$sid] ". $tbl["city"] ."</option>\n";
   }
   $s .= "</select>\n";
   return $s;
}

function networkSelect($network, $selected, $extra=Array())
{
    global $rootpath;
    $network = strtoupper($network);
    $s = "";
    include_once("$rootpath/include/all_locs.php");
    reset($cities);
    $s .= '<select name="station">\n';
    while (list($sid, $tbl) = each($cities))
    {
        if ($tbl["network"] != $network) continue;
        $s .= "<option value=\"$sid\" ";
        if ($selected == $sid) { $s .= "SELECTED"; }
        $s .= ">". $tbl["city"] ."</option>\n";
   }
   while (list($idx,$sid) = each($extra))
   {
        $tbl = $cities[$sid];
        $s .= "<option value=\"$sid\" ";
        if ($selected == $sid) { $s .= "SELECTED"; }
        $s .= ">". $tbl["city"] ."</option>\n";
   }
   $s .= "</select>\n";
   return $s;
}


function isuagSelect($selected)
{
    global $rootpath;
    $s = "";
    include_once("$rootpath/include/agclimateLoc.php");
    reset($ISUAGcities);
    $s .= '<select name="station">\n';
    while (list($sid, $tbl) = each($ISUAGcities))
    {
        $s .= "<option value=\"$sid\" ";
        if ($selected == $sid) { $s .= "SELECTED"; }
        $s .= ">". $tbl["city"] ."</option>\n";
   }
   $s .= "</select>\n";
   return $s;
}


function snetSelectMultiple($selected){
global $rootpath;
include("$rootpath/include/snet_locs.php");
echo "<select name=\"station[]\" size=7 MULTIPLE >\n";

reset($cities);
while( list($tv, $tbl) = each($cities) )
{
  while( list($station, $v) = each($tbl) )
  {
    echo "<option value=\"". $station ."\"";
    if ($selected == $station){
      echo " SELECTED ";
    }
    echo " >". $v["city"] ."</option>\n";
  }
}
                                                                               
echo "</select>\n";
                                                                                
}


function snetSelect($selected){
global $rootpath;
include("$rootpath/include/snet_locs.php");
echo "<select name=\"station\">\n";

reset($cities);
while( list($tv, $tbl) = each($cities) )
{
  while( list($station, $v) = each($tbl) )
  {
    echo "<option value=\"". $station ."\"";
    if ($selected == $station){
      echo " SELECTED ";
    }
    echo " >". $v["city"] ."</option>\n";
  }
} 

echo "</select>\n";

} 

function keloSelect($selected){
    global $rootpath;
include("$rootpath/include/keloLoc.php");
echo "<select name=\"station\">\n";

for ($i = 0; $i < count($Scities); $i++) {
  $city = current($Scities);
  echo "<option value=\"". $city["id"] ."\"";
  if ($selected == $city["id"]){
        echo " SELECTED ";
  }
  echo " >". $city["city"] ."\n";
  next($Scities);
}

echo "</select>\n";

}

function kcciSelectAuto($selected, $pre, $post){
global $rootpath;
include("$rootpath/include/kcciLoc.php");
echo "<form><select name=\"station\" onChange=\"location=this.form.station.options[this.form.station.selectedIndex].value\">\n";

for ($i = 0; $i < count($Scities); $i++) {
  $city = current($Scities);
  echo "<option value=\"". $pre . $city["id"] . $post ."\"";
  if (strcmp($selected, $city["id"]) == 0){
        echo " SELECTED ";
  }
  echo " >". $city["city"] ."</option>\n";
  next($Scities);
}

echo "</select></form>\n";

}


function kcciSelect($selected){
global $rootpath;
include("$rootpath/include/kcciLoc.php");
echo "<select name=\"station\">\n";

for ($i = 0; $i < count($Scities); $i++) {
  $city = current($Scities);
  echo "<option value=\"". $city["id"] ."\"";
  if ($selected == $city["id"]){
        echo " SELECTED ";
  }
  echo " >". $city["city"] ."</option>\n";
  next($Scities);
}

echo "</select>\n";

}

function kcci2Select($selected){
global $rootpath;
include("$rootpath/include/kcciLoc.php");
echo "<select name=\"station\">\n";

for ($i = 0; $i < count($Scities); $i++) {
  $city = current($Scities);
  echo "<option value=\"". $city["id"] ."\"";
  if ($selected == $city["id"]){
        echo " SELECTED ";
  }
  echo " >". $city["city"] ."</option>\n";
  next($Scities);
}

echo "</select>\n";

}


?>


<?php

function awosSelect($selected){
global $rootpath;
include("$rootpath/include/awosLoc.php");
  echo "<select name=\"station\">\n";
  for ($i = 0; $i < count($Wcities); $i++) {
    $city = current($Wcities);
    echo "<option value=\"". $city["id"] ."\"";
    if ($selected == $city["id"]){
        echo " SELECTED ";
    }
    echo " >". $city["city"] ." (". $city["id"] .")\n";
    next($Wcities);
  }

  echo "</select>\n";
}

function awosMultiSelect($selected, $size){
global $rootpath;
include("$rootpath/include/awosLoc.php");
  echo "<select name=\"station[]\" size=\"". $size ."\" MULTIPLE>\n";
  echo "<option value=\"_ALL\">Select All</option>\n";
  for ($i = 0; $i < count($Wcities); $i++) {
    $city = current($Wcities);
    echo "<option value=\"". $city["id"] ."\"";
    if ($selected == $city["id"]){
        echo " SELECTED ";
    }
    echo " >". $city["city"] ." (". $city["id"] .")\n";
    next($Wcities);
  }

  echo "</select>\n";
}

function asosMultiSelect($selected, $size){
global $rootpath;
include("$rootpath/include/asosLoc.php");
  echo "<select name=\"station[]\" size=\"". $size ."\" MULTIPLE>\n";
  echo "<option value=\"_ALL\">Select All</option>\n";
  for ($i = 0; $i < count($Acities); $i++) {
    $city = current($Acities);
    echo "<option value=\"". $city["id"] ."\"";
    if ($selected == $city["id"]){
        echo " SELECTED ";
    }
    echo " >". $city["city"] ." (". $city["id"] .")\n";
    next($Acities);
  }

  echo "</select>\n";
}


function rwisMultiSelect($selected, $size){
global $rootpath;
include_once("$rootpath/include/all_locs.php");
  echo "<select name=\"station[]\" size=\"". $size ."\" MULTIPLE>\n";
  echo "<option value=\"_ALL\">Select All</option>\n";
  reset($cities);
  while( list($key, $val) = each($cities) ) {
    if ($val["network"] != "IA_RWIS") continue; 
    echo "<option value=\"". $key ."\"";
    if ($selected == $key){
        echo " SELECTED ";
    }
    echo " >". $val["city"] ." (". $key .")\n";
  }

  echo "</select>\n";
}



function asosSelect($selected){
global $rootpath;
include("$rootpath/include/asosLoc.php");
echo "<select name=\"station\">\n";

for ($i = 0; $i < count($Acities); $i++) {
  $city = current($Acities);
  echo "<option value=\"". $city["id"] ."\"";
  if ($selected == $city["id"]){
  	echo " SELECTED ";
  }
  echo " >". $city["city"] ."\n";
  next($Acities);
} 

echo "</select>\n";

}


function rwisSelect($selected){
global $rootpath;
include("$rootpath/include/rwisLoc.php");
echo "<select name=\"station\">\n";

  reset($Rcities);
  while( list($key, $val) = each($Rcities) ) {
  echo "<option value=\"". $key ."\"";
  if ($selected == $key){
  	echo " SELECTED ";
  }
  echo " >". $val["city"] ."\n";
} 

echo "</select>\n";

} ?>
