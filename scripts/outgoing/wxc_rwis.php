<?php
include("../../config/settings.inc.php");
include("../../include/mlib.php");
include("../../include/network.php");
$nt = new NetworkTable("IA_RWIS");
$cities = $nt->table;

function fancy($v, $floor,$ceil, $p){
  if ($v < $floor || $v > $ceil) return "";
  return sprintf("%${p}.1f", $v);
}

include("../../include/iemaccess.php");
include("../../include/iemaccessob.php");
$iem = new IEMAccess();

/* Lets also get the traffic data, please */
$rs = pg_query($iem->dbconn, "select l.nwsli, t.* from rwis_traffic t, 
	rwis_locations l where l.id = t.location_id and lane_id < 4");
$traffic = Array();
for ($i=0;$row=@pg_fetch_array($rs,$i);$i++){
	if (! array_key_exists($row["nwsli"], $traffic)){
		$traffic[$row["nwsli"]] = Array("avgspeed0" => "M", 
		"avgspeed1" => "M", "avgspeed2" => "M", "avgspeed3" => "M");
	}
	$traffic[$row["nwsli"]][sprintf("avgspeed%s", $row["lane_id"])] = round($row["avg_speed"],0);
}

$tstamp = gmdate("Y-m-d\TH:i:s");

$mydata = $iem->getNetwork("IA_RWIS");

$rwis = fopen('/tmp/wxc_iadot.txt', 'w');
fwrite($rwis, "Weather Central 001d0300 Surface Data TimeStamp=$tstamp
  22
   5 Station
  52 CityName
   2 State
   7 Lat
   8 Lon
   2 Day
   4 Hour
   5 AirTemp
   5 AirDewp
   4 Wind Direction Degrees
   4 Wind Direction Text
   4 Wind Speed
   4 SubSurface Temp
   4 P1 Temp
   4 P2 Temp
   4 P3 Temp
   4 P4 Temp
   4 Pave Ave Temp
   3 Sensor 0 Average Speed
   3 Sensor 1 Average Speed
   3 Sensor 2 Average Speed
   3 Sensor 3 Average Speed
");
 


$now = time();
while ( list($key, $val) = each($mydata) ) {
	if (! array_key_exists($key,$traffic)){
		$traffic[$key] = Array("avgspeed0" => "M", 
		"avgspeed1" => "M", "avgspeed2" => "M", "avgspeed3" => "M");
	}
  $tdiff = $now - $val->db["ts"];

  if ($val->db['tsf0'] == "") $val->db['tsf0'] = -99.99;
  if ($val->db['tsf1'] == "") $val->db['tsf1'] = -99.99;
  if ($val->db['tsf2'] == "") $val->db['tsf2'] = -99.99;
  if ($val->db['tsf3'] == "") $val->db['tsf3'] = -99.99;
  $t = Array($val->db['tsf0'], $val->db['tsf1'],
     $val->db['tsf2'], $val->db['tsf3']);
  arsort($t);
  //print_r($t);
  while (min($t) < -39.99){
    $ba = array_pop($t);
    if (sizeof($t) == 0) break;
  }
  asort($t);
  if (sizeof($t) > 0){
    while ((max($t) - min($t)) > 20){ $ba = array_pop($t); }
    $val->db['pave_avg'] = array_sum($t) / sizeof($t);
  } else {
    $val->db['pave_avg'] = -99.99;
  }
  //echo  $val->db['pave_avg'];



  if ($tdiff < 1800){
  if (round($val->db['rwis_subf'],0) == -100) $val->db['rwis_subf'] = 'M';
  else $val->db['rwis_subf'] = round($val->db['rwis_subf'],0);
  if (round($val->db['tsf0'],0) == -100) $val->db['tsf0'] = 'M';
  else $val->db['tsf0'] = round($val->db['tsf0'],0);
  if (round($val->db['tsf1'],0) == -100) $val->db['tsf1'] = 'M';
  else $val->db['tsf1'] = round($val->db['tsf1'],0);
  if (round($val->db['tsf2'],0) == -100) $val->db['tsf2'] = 'M';
  else $val->db['tsf2'] = round($val->db['tsf2'],0);
  if (round($val->db['tsf3'],0) == -100) $val->db['tsf3'] = 'M';
  else $val->db['tsf3'] = round($val->db['tsf3'],0);
  if (round($val->db['pave_avg'],0) == -100) $val->db['pave_avg'] = 'M';
  else $val->db['pave_avg'] = round($val->db['pave_avg'],0);

  $s = sprintf("%5s %52s %2s %7s %8s %2s %4s %5s %5s %4s %4s %4.1d %4s %4s %4s %4s %4s %4s %3s %3s %3s %3s\n", $key, 
    $cities[$key]['name'], $val->db['state'], round($cities[$key]['lat'],2), 
     round($cities[$key]['lon'],2),
     date('d', $val->db['ts'] + (6*3600) ), date('H', $val->db['ts'] + (6*3600)),
     $val->db['tmpf'], $val->db['dwpf'],
     $val->db['drct'], drct2txt($val->db['drct']), $val->db['sknt'], 
     $val->db['rwis_subf'],
     $val->db['tsf0'], $val->db['tsf1'], 
     $val->db['tsf2'], $val->db['tsf3'],
     $val->db['pave_avg'], $traffic[$key]["avgspeed0"],
     $traffic[$key]["avgspeed1"], $traffic[$key]["avgspeed2"],
      $traffic[$key]["avgspeed3"]); 
  fwrite($rwis, $s);
  }
} // End of while

fclose($rwis);

$pqstr = "data c 000000000000 wxc/wxc_iadot.txt bogus txt";
$cmd = sprintf("/home/ldm/bin/pqinsert -p '%s' /tmp/wxc_iadot.txt", $pqstr);
system($cmd);
unlink("/tmp/wxc_iadot.txt");


$nt->table = Array();
$nt->load_network("IL_RWIS");
$cities = $nt->table;

$mydata = $iem->getNetwork("IL_RWIS");

$rwis = fopen('/tmp/wxc_ildot.txt', 'w');
fwrite($rwis, "Weather Central 001d0300 Surface Data TimeStamp=$tstamp
  18
   6 Station
  52 CityName
   2 State
   7 Lat
   8 Lon
   2 Day
   4 Hour
   5 AirTemp
   5 AirDewp
   4 Wind Direction Degrees
   4 Wind Direction Text
   5 Wind Speed
   5 SubSurface Temp
   5 P1 Temp
   5 P2 Temp
   5 P3 Temp
   5 P4 Temp
   5 Pave Ave Temp
");
 

$now = time();
while ( list($key, $val) = each($mydata) ) {
  $tdiff = $now - $val->db["ts"];

  if ($val->db['tsf0'] == "") $val->db['tsf0'] = -99.99;
  if ($val->db['tsf1'] == "") $val->db['tsf1'] = -99.99;
  if ($val->db['tsf2'] == "") $val->db['tsf2'] = -99.99;
  if ($val->db['tsf3'] == "") $val->db['tsf3'] = -99.99;
  $t = Array($val->db['tsf0'], $val->db['tsf1'],
     $val->db['tsf2'], $val->db['tsf3']);
  arsort($t);
  //print_r($t);
  while (min($t) < -39.99){
    $ba = array_pop($t);
    if (sizeof($t) == 0) break;
  }
  asort($t);
  if (sizeof($t) > 0){
    while ((max($t) - min($t)) > 20){ $ba = array_pop($t); }
    $val->db['pave_avg'] = array_sum($t) / sizeof($t);
  } else {
    $val->db['pave_avg'] = -99.99;
  }
  //echo  $val->db['pave_avg'];



  if ($tdiff < 3600){
  if (round($val->db['rwis_subf'],0) == -100) $val->db['rwis_subf'] = 'M';
  else $val->db['rwis_subf'] = round($val->db['rwis_subf'],0);
  if (round($val->db['tsf0'],0) == -100) $val->db['tsf0'] = 'M';
  else $val->db['tsf0'] = round($val->db['tsf0'],0);
  if (round($val->db['tsf1'],0) == -100) $val->db['tsf1'] = 'M';
  else $val->db['tsf1'] = round($val->db['tsf1'],0);
  if (round($val->db['tsf2'],0) == -100) $val->db['tsf2'] = 'M';
  else $val->db['tsf2'] = round($val->db['tsf2'],0);
  if (round($val->db['tsf3'],0) == -100) $val->db['tsf3'] = '';
  else $val->db['tsf3'] = round($val->db['tsf3'],0);
  if (round($val->db['pave_avg'],0) == -100) $val->db['pave_avg'] = 'M';
  else $val->db['pave_avg'] = round($val->db['pave_avg'],0);

  $s = sprintf("%6s %-52s %2s %7s %8s %2s %4s %5.1f %5.1f %4.0f %4.0f %5.1f %5s %5s %5s %5s %5s %5s\n", $key, 
    $cities[$key]['name'], $val->db['state'], round($cities[$key]['lat'],2), 
     round($cities[$key]['lon'],2),
     date('d', $val->db['ts'] + (6*3600) ), date('H', $val->db['ts'] + (6*3600)),
     $val->db['tmpf'], $val->db['dwpf'],
     $val->db['drct'], drct2txt($val->db['drct']), $val->db['sknt'], 
     fancy($val->db['rwis_subf'],-50,180,5),
     fancy($val->db['tsf0'],-50,180,5), fancy($val->db['tsf1'],-50,180,5),
     fancy($val->db['tsf2'],-50,180,5), fancy($val->db['tsf3'],-50,180,5),
     fancy($val->db['pave_avg'],-50,180,5) ); 
  fwrite($rwis, $s);
  }
} // End of while

fclose($rwis);

$pqstr = "data c 000000000000 wxc/wxc_ildot.txt bogus txt";
$cmd = sprintf("/home/ldm/bin/pqinsert -p '%s' /tmp/wxc_ildot.txt", $pqstr);
system($cmd);
unlink("/tmp/wxc_ildot.txt");


// ------------------------------------------------

pg_close($nt->dbconn);
$nt = new NetworkTable( Array("OH_RWIS","IN_RWIS", "KY_RWIS") );
$cities = $nt->table;

$mydata = $iem->getNetwork( Array("OH_RWIS","IN_RWIS", "KY_RWIS") );

$rwis = fopen('/tmp/wxc_oh_in_kydot.txt', 'w');
fwrite($rwis, "Weather Central 001d0300 Surface Data TimeStamp=$tstamp
  22
   6 Station
  52 CityName
   2 State
   7 Lat
   8 Lon
   2 Day
   4 Hour
   5 AirTemp
   5 AirDewp
   4 Wind Direction Degrees
   4 Wind Direction Text
   5 Wind Speed
   5 SubSurface Temp
   5 P1 Temp
   5 P2 Temp
   5 P3 Temp
   5 P4 Temp
   5 Pave Ave Temp
   5 Wind Chill F
   5 Heat Index F
   5 Today High Temp F
   5 Today Low Temp F
");
 

$now = time();
while ( list($key, $val) = each($mydata) ) {
  $tdiff = $now - $val->db["ts"];
  // Heat index
  $relh = relh( f2c($val->db['tmpf']), f2c($val->db['dwpf']));
  $val->db["heat"] = round(heat_idx($val->db['tmpf'], $relh),1);
  $val->db["wcht"] = round(wcht_idx($val->db['tmpf'], $val->db['sknt'] * 1.15),1);
  if ($val->db['dwpf'] < -99.0) {
  	$val->db['dwpf'] = 'M';
  	$val->db['heat'] = 'M';
  }
  else {
  	$val->db['dwpf'] = round($val->db['dwpf'],1);
  }

  if ($val->db['tmpf'] < -99.0){
  	$val->db['tmpf'] = 'M';
  	$val->db['heat'] = 'M';
  	$val->db['wcht'] = 'M';
  }
  else {
  	$val->db['tmpf'] = round($val->db['tmpf'],1);
  }

  if ($val->db['tsf0'] == "") $val->db['tsf0'] = -99.99;
  if ($val->db['tsf1'] == "") $val->db['tsf1'] = -99.99;
  if ($val->db['tsf2'] == "") $val->db['tsf2'] = -99.99;
  if ($val->db['tsf3'] == "") $val->db['tsf3'] = -99.99;
  $t = Array($val->db['tsf0'], $val->db['tsf1'],
     $val->db['tsf2'], $val->db['tsf3']);
  arsort($t);

  while (min($t) < -39.99){
    $ba = array_pop($t);
    if (sizeof($t) == 0) break;
  }
  asort($t);
  if (sizeof($t) > 0){
    while ((max($t) - min($t)) > 20){ $ba = array_pop($t); }
    $val->db['pave_avg'] = array_sum($t) / sizeof($t);
  } else {
    $val->db['pave_avg'] = -99.99;
  }
  //echo  $val->db['pave_avg'];



  if ($tdiff > 7200){
  	continue;
  }
  if (round($val->db['rwis_subf'],0) == -100) $val->db['rwis_subf'] = 'M';
  else $val->db['rwis_subf'] = round($val->db['rwis_subf'],0);
  if (round($val->db['tsf0'],0) == -100) $val->db['tsf0'] = 'M';
  else $val->db['tsf0'] = round($val->db['tsf0'],0);
  if (round($val->db['tsf1'],0) == -100) $val->db['tsf1'] = 'M';
  else $val->db['tsf1'] = round($val->db['tsf1'],0);
  if (round($val->db['tsf2'],0) == -100) $val->db['tsf2'] = 'M';
  else $val->db['tsf2'] = round($val->db['tsf2'],0);
  if (round($val->db['tsf3'],0) == -100) $val->db['tsf3'] = '';
  else $val->db['tsf3'] = round($val->db['tsf3'],0);
  if (round($val->db['pave_avg'],0) == -100) $val->db['pave_avg'] = 'M';
  else $val->db['pave_avg'] = round($val->db['pave_avg'],0);

  $s = sprintf("%6s %-52s %2s %7s %8s %2s %4s %5.1f %5s %4.0f %4.0f %5.1f %5s %5s %5s %5s %5s %5s %5s %5s %5s %5s\n", $key, 
    strtoupper($cities[$key]['name']), $val->db['state'], round($cities[$key]['lat'],2), 
     round($cities[$key]['lon'],2),
     date('d', $val->db['ts'] + (6*3600) ), date('H', $val->db['ts'] + (6*3600)),
     $val->db['tmpf'], $val->db['dwpf'],
     $val->db['drct'], drct2txt($val->db['drct']), $val->db['sknt'], 
     fancy($val->db['rwis_subf'],-50,180,5),
     fancy($val->db['tsf0'],-50,180,5), fancy($val->db['tsf1'],-50,180,5),
     fancy($val->db['tsf2'],-50,180,5), fancy($val->db['tsf3'],-50,180,5),
     fancy($val->db['pave_avg'],-50,180,5),
  		$val->db["wcht"], $val->db["heat"], 
  	fancy($val->db["max_tmpf"],-50,120,5), fancy($val->db["min_tmpf"],-50,120,5)
  		); 
  fwrite($rwis, $s);

} // End of while

fclose($rwis);

$pqstr = "data c 000000000000 wxc/wxc_oh_in_kydot.txt bogus txt";
$cmd = sprintf("/home/ldm/bin/pqinsert -p '%s' /tmp/wxc_oh_in_kydot.txt", $pqstr);
system($cmd);
unlink("/tmp/wxc_oh_in_kydot.txt");

?>
