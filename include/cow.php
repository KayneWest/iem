<?php
/* cow.php
 *  Functionized routines for IEM Cow
 *  This way we can call from other applications, like dailyb :)
 */
putenv("TZ=GMT");

class cow {

function cow($dbconn){
    /* Constructor */
    $this->dbconn = $dbconn;
    pg_query($dbconn, "SET TIME ZONE 'GMT'");

    $this->wfo = Array();        /* Array of WFOs to potentially limit */
    $this->warnings = Array();   /* Array of warnings */
    $this->lsrs = Array();       /* Array of LSRs */
    $this->sts = 0;              /* Verification window start UTC */
    $this->ets = 0;              /* Vertification window end UTC */
    $this->wtype = Array();      /* VTEC Phenomena types to verify */
    $this->ltype = Array();      /* LSR Types to verify with */
    $this->ugcCache = Array();   /* UGC information */
    $this->hailsize = 0.75;      /* Hail size limitation */
    $this->lsrbuffer = 15;       /* LSR report buffer in km */
    $this->wind = 58;			/* Wind threshold in mph */
    $this->useWindHailTag = false;  /* Option to use wind hail tag to verify */
    $this->limitwarns = false;  /* Limit listed warnings to wind and hail criterion */
}

/* Standard Workflow */
function milk(){ 
    $this->loadWarnings();
    $this->loadLSRs();
    $this->computeUGC();
    $this->computeSharedBorder();
    $this->sbwVerify();
    $this->areaVerify();
}

function callDB($sql){
    $rs = @pg_query($this->dbconn, $sql);
    //if (! $rs ){ echo $sql; }
    return $rs;
}

function setLSRBuffer($buffer){
    $this->lsrbuffer = $buffer;
}

function setLimitWFO($arWFO){
    $this->wfo = $arWFO;
}

function setLimitTime($sts, $ets){
    $this->sts = $sts;
    $this->ets = $ets;
}

function setLimitType($arType){
    $this->wtype = $arType;
}

function setLimitLSRType($arType){
    $this->ltype = $arType;
}

function setHailSize($val){
    $this->hailsize = $val;
}
function setWind($val){
    $this->wind = $val;
}

function sqlWFOBuilder(){
    reset($this->wfo);
    if (sizeof($this->wfo) == 0){ return "1 = 1"; }

    $sql = "w.wfo IN ('". implode(",", $this->wfo) ."')";
    $sql = str_replace(",", "','", $sql);
    return $sql;
}

function sqlLSRTypeBuilder(){
    reset($this->ltype);
    if (sizeof($this->ltype) == 0){ return "1 = 1"; }
    $l = Array();
    while( list($k,$v) = each($this->ltype)){
        if ($v == "TO"){ $l[] = "T"; }
        else if ($v == "SV"){ $l[] = "H"; $l[] = "G"; $l[] = "D"; }
        else if ($v == "MA"){ $l[] = "M"; $l[] = "W"; }
        else if ($v == "FF"){ $l[] = "F"; }
        else{ $l[] = $v; }
    }   
    $sql = "type IN ('". implode(",", $l) ."')";
    $sql = str_replace(",", "','", $sql);
    return $sql;
}

function sqlTypeBuilder(){
    if (sizeof($this->wtype) == 0) return "1 = 1";

    $sql = "w.phenomena IN ('". implode(",", $this->wtype) ."')";
    $sql = str_replace(",", "','", $sql);
    return $sql;
}

function computeAverageSize(){
    if (sizeof($this->warnings) == 0){ return 0; }
    reset($this->warnings);
    $polysz = 0;
    while (list($k,$v) = each($this->warnings)){
        $polysz += $v["parea"];
    }
    return $polysz / floatval(sizeof($this->warnings));
}

function computeSizeReduction(){
    reset($this->warnings);
    $polysz = 0;
    $countysz = 0;
    while (list($k,$v) = each($this->warnings)){
        $polysz += $v["parea"];
        while (list($k2,$v2) = each($v["ugc"])){
            $countysz += $this->ugcCache[$v2]["area"];
        }
    }
    if ($countysz == 0){ return 0; }
    return ($countysz - $polysz) / $countysz * 100.0;
}

function computeUnwarnedEvents(){
    return sizeof($this->lsrs) - $this->computeWarnedEvents();
}


function computeWarnedEvents(){
    if (sizeof($this->lsrs) == 0){ return 0; }

    reset($this->lsrs);
    $counter = 0;
    while (list($k,$v) = each($this->lsrs)){
        if ($v["warned"]){ $counter += 1; }
    }
    return $counter;
}

function computeTDQEvents(){
    if (sizeof($this->lsrs) == 0){ return 0; }

    reset($this->lsrs);
    $counter = 0;
    while (list($k,$v) = each($this->lsrs)){
        if ($v["tdq"]){ $counter += 1; }
    }
    return $counter;
}

function computeMaxLeadTime(){
   if (sizeof($this->lsrs) == 0){ return 0; }

   $large = 0;
   reset($this->lsrs);
   while (list($k,$v) = each($this->lsrs)){
       if ($v["leadtime"] > $large){ $large = $v["leadtime"]; }
   }
   return $large;
}
function computeMinLeadTime(){
   if (sizeof($this->lsrs) == 0){ return 0; }
   $smallest = 99;
   reset($this->lsrs);
   while (list($k,$v) = each($this->lsrs)){
       if ($v["leadtime"] < $smallest){ $smallest = $v["leadtime"]; }
   }
   return $smallest;
}


function computeAllLeadTime(){
   $ar = Array();
   reset($this->lsrs);
   while (list($k,$v) = each($this->lsrs)){
       if ($v["leadtime"] != "NA"){ $ar[] = $v["leadtime"]; }
   }
   if (sizeof($ar) == 0){ return 0; }
   return array_sum($ar) / floatval( sizeof($ar) );
}

function computeAverageLeadTime(){
   $ar = Array();
   reset($this->warnings);
   while (list($k,$v) = each($this->warnings)){
       if ($v["lead0"] > -1){ $ar[] = $v["lead0"]; }
   }
   if (sizeof($ar) == 0){ return 0; }
   return array_sum($ar) / floatval( sizeof($ar) );
}

function computeAveragePerimeterRatio(){
   $shared = 0;
   $total = 0;
   reset($this->warnings);
   while (list($k,$v) = each($this->warnings)){
       $shared += $v["sharedborder"];
       $total += $v["perimeter"];
   }
   return ($shared / $total * 100.0);
}

function computeCSI(){
   $pod = $this->computePOD();
   $far = $this->computeFAR();
   return pow((pow($pod,-1) + pow(1-$far,-1) - 1), -1);
}

function computePOD(){
   $a_e = $this->computeWarnedEvents();
   $b = sizeof($this->lsrs) - $a_e;
   if ($b + $a_e == 0){ return 0; }
   return floatval($a_e) / floatval( $a_e + $b );
}

function computeFAR(){
    $a_w = $this->computeWarningsVerified();
    $c = sizeof($this->warnings) - $a_w;
    if ($c + $a_w == 0){ return 0; }
    return floatval($c) / floatval( $a_w + $c );
}

function computeAreaVerify(){
    $polysz = 0;
    $lsrsz = 0;
    if (sizeof($this->warnings) == 0){ return 0; }
    reset($this->warnings);
    while (list($k,$v) = each($this->warnings)){
        $polysz += $v["parea"];
        $lsrsz += $v["buffered"];
    }
    return $lsrsz / $polysz * 100.0;

}

function computeWarningsVerifiedPercent(){
    if (sizeof($this->warnings) == 0){ return 0; }
    return $this->computeWarningsVerified() / floatval(sizeof($this->warnings)) * 100.0;
}

function computeWarningsVerified(){
    reset($this->warnings);
    $counter = 0;
    while (list($k,$v) = each($this->warnings)){
        if ($v["verify"]){ $counter += 1; }
    }
    return $counter;
}

function computeWarningsUnverified(){
    return sizeof($this->warnings) - $this->computeWarningsVerified();
}

function computeUGC(){
    reset($this->warnings);
    while (list($k,$v) = each($this->warnings)){
        while (list($k2,$v2) = each($v["ugc"])){
            if (array_key_exists($v2, $this->ugcCache)){ continue; }
            /* Else we need to lookup the informations */
            $sql = sprintf("SELECT *, 
                   area(transform(geom,2163)) / 1000000.0 as area 
                   from nws_ugc WHERE ugc = '%s'", $v2);
            $rs = $this->callDB($sql);
            if (pg_num_rows($rs) > 0){
                $row = pg_fetch_array($rs,0);
                $this->ugcCache[$v2] = Array(
                     "name" => sprintf("%s,%s ", $row["name"], $row["state"]),
                     "area" => $row["area"]);
            } else {
                $this->ugcCache[$v2] = Array(
                     "name" => sprintf("(((%s)))", $v2),
                     "area" => $row["area"]);
            }
        }
    }
} /* End of computeUGC() */

function computeSharedBorder(){
    reset($this->warnings);
    while (list($k,$v) = each($this->warnings)){
        $sql = sprintf("SELECT sum(sz) as s from (
     SELECT length(transform(a,2163)) as sz from (
        select 
           intersection(
      buffer(exteriorring(geometryn(multi(ST_union(n.geom)),1)),0.02),
      exteriorring(geometryn(multi(ST_union(w.geom)),1))
            )  as a
            from warnings w, nws_ugc n WHERE gtype = 'P' 
            and w.wfo = '%s' and phenomena = '%s' and eventid = '%s' 
            and significance = '%s' and n.polygon_class = 'C'
            and st_overlaps(n.geom, w.geom) 
            and n.ugc IN (
                SELECT ugc from warnings w WHERE
                gtype = 'C' and wfo = '%s'
          and phenomena = '%s' and eventid = '%s' and significance = '%s'
       )
         ) as foo
            WHERE not isempty(a) ) as foo
       ", $v["wfo"], $v["phenomena"],
            $v["eventid"], $v["significance"],
          $v["wfo"], $v["phenomena"],
            $v["eventid"], $v["significance"] );

        $rs = $this->callDB($sql);
        if ($rs && pg_num_rows($rs) > 0){
            $row = pg_fetch_array($rs,0);
            $this->warnings[$k]["sharedborder"] = $row["s"];
        } else {
            $this->warnings[$k]["sharedborder"] = 0;
        }
    }
}

function sqlTagLimiter(){
	if ($this->limitwarns){
		return sprintf(" and ((s.windtag >= %s or s.hailtag >= %s) or (s.windtag is null and s.hailtag is null))", $this->wind, $this->hailsize);
	}
	return "";
}

function loadWarnings(){
    $sql = sprintf("
    select *, ST_astext(geom) as tgeom from 
      (SELECT distinct * from 
        (select s.hailtag, s.windtag, 
         w.geom, w.issue, w.expire, w.wfo, w.status, w.significance,
         w.phenomena, w.eventid, w.gtype, w.ugc,
         ST_area(ST_transform(w.geom,2163)) / 1000000.0 as area,
         ST_perimeter(ST_transform(w.geom,2163)) as perimeter,
         xmax(w.geom) as lon0, ymax(w.geom) as lat0 from 
         warnings w, sbw s WHERE s.wfo = w.wfo and s.phenomena = w.phenomena and
         s.eventid = w.eventid and s.significance = w.significance and 
         s.status = 'NEW' and s.issue = w.issue and 
         %s and w.issue >= '%s' and w.issue < '%s' and
         w.expire < '%s' and %s and w.significance = 'W' %s
         ORDER by w.issue ASC) as foo) 
      as foo2 ORDER by issue ASC", $this->sqlWFOBuilder(), 
   date("Y/m/d H:i", $this->sts), date("Y/m/d H:i", $this->ets), 
   date("Y/m/d H:i", $this->ets), $this->sqlTypeBuilder(), 
   $this->sqlTagLimiter() );

    $rs = $this->callDB($sql);
    for ($i=0;$row = @pg_fetch_array($rs,$i);$i++){
        $key = sprintf("%s-%s-%s-%s", substr($row["issue"],0,4), $row["wfo"], 
                       $row["phenomena"], $row["eventid"]);
        if ( ! isset($this->warnings[$key]) ){
            $this->warnings[$key] = Array("ugc"=> Array(), "geom" => "",
                                          "lsrs" => Array(), "perimeter" => 0,
                                          "parea" => 0 );
        }
        $this->warnings[$key]["hailtag"] = $row["hailtag"];
        $this->warnings[$key]["windtag"] = $row["windtag"];
        $this->warnings[$key]["issue"] = $row["issue"];
        $this->warnings[$key]["phenomena"] = $row["phenomena"];
        $this->warnings[$key]["wfo"] = $row["wfo"];
        $this->warnings[$key]["significance"] = $row["significance"];
        $this->warnings[$key]["area"] = $row["area"];
        $this->warnings[$key]["lat0"] = $row["lat0"];
        $this->warnings[$key]["lon0"] = $row["lon0"];
        $this->warnings[$key]["sts"] = strtotime($row["issue"]);
        $this->warnings[$key]["ets"] = strtotime($row["expire"]);
        $this->warnings[$key]["eventid"] = $row["eventid"];
        $this->warnings[$key]["lead0"] = -1;
        $this->warnings[$key]["buffered"] = 0;
        $this->warnings[$key]["verify"] = 0;
        if ($row["gtype"] == "P"){
        	$this->warnings[$key]["status"] = $row["status"];
        	$this->warnings[$key]["expire"] = $row["expire"];
            $this->warnings[$key]["geom"] = $row["tgeom"];
            $this->warnings[$key]["perimeter"] = $row["perimeter"];
            $this->warnings[$key]["parea"] = $row["area"];
        } else {
            $this->warnings[$key]["ugc"][] = $row["ugc"];
        }

    } /* End of rs for loop */

} /* End of loadWarnings() */

function loadLSRs() {
    $sql = sprintf("SELECT distinct *, x(geom) as lon0, y(geom) as lat0, 
        astext(geom) as tgeom,
        astext(buffer( transform(geom,2163), %s000)) as buffered
        from lsrs w WHERE %s and 
        valid >= '%s' and valid < '%s' and %s and
        ((type = 'M' and magnitude >= 34) or 
         (type = 'H' and magnitude >= %s) or type = 'W' or
         type = 'T' or (type = 'G' and magnitude >= %s) or type = 'D'
         or type = 'F')
        ORDER by valid ASC", $this->lsrbuffer, 
        $this->sqlWFOBuilder(), 
        date("Y/m/d H:i", $this->sts), date("Y/m/d H:i", $this->ets), 
        $this->sqlLSRTypeBuilder(), $this->hailsize, $this->wind);
    $rs = $this->callDB($sql);
    for ($i=0;$row = @pg_fetch_array($rs,$i);$i++)
    {
        $key = sprintf("%s-%s-%s-%s-%s", 
          $row["wfo"], $row["valid"], $row["type"],
          $row["magnitude"], $row["city"]);
        $this->lsrs[$key] = $row;
        $this->lsrs[$key]['geom'] = $row["tgeom"];
        $this->lsrs[$key]["ts"] = strtotime($row["valid"]);
        $this->lsrs[$key]["warned"] = False;
        $this->lsrs[$key]["tdq"] = False; /* Tornado DQ */
        $this->lsrs[$key]["leadtime"] = "NA";
        $this->lsrs[$key]["remark"] = $row["remark"];
    }
} /* End of loadLSRs() */

function areaVerify() {
    reset($this->warnings);
    while (list($k,$v) = each($this->warnings)) {
        if (sizeof($v["lsrs"]) == 0){ continue; }
        $bufferedArray = Array();
        while (list($k2,$v2) = each($v["lsrs"])){
            $bufferedArray[] = sprintf("SetSRID(GeomFromText('%s'),2163)", 
              $this->lsrs[$v2]["buffered"]);
        }
        $sql = sprintf("SELECT ST_Area(
         ST_Intersection( ST_Union(ARRAY[%s]), 
                          ST_Transform(ST_GeomFromEWKT('SRID=4326;%s'),2163) ) 
         ) / 1000000.0 as area",
         implode(",", $bufferedArray), $v["geom"] );
        $rs = $this->callDB($sql);
        if ($rs){
            $row = pg_fetch_array($rs,0);
        } else {
            $row = Array("area" => 0);
        }
        $this->warnings[$k]["buffered"] = $row["area"];
    }
}

function getVerifyHailSize($warn){
	if ($this->useWindHailTag && $warn['hailtag'] != null && $warn['hailtag'] >= $this->hailsize){
		return $warn['hailtag'];
	}
	return $this->hailsize;
}
function getVerifyWind($warn){
	if ($this->useWindHailTag && $warn['windtag'] != null && $warn['windtag'] > $this->wind){
		return $warn['windtag'];
	}
	return $this->wind;
}

function sbwVerify() {
    reset($this->warnings);
    while (list($k,$v) = each($this->warnings)) {
        /* Look for LSRs! */
        $sql = sprintf("SELECT distinct *
         from lsrs w WHERE 
         geom && ST_Buffer(SetSrid(GeometryFromText('%s'),4326),0.01) and 
         contains(ST_Buffer(SetSrid(GeometryFromText('%s'),4326),0.01), geom) 
         and %s and wfo = '%s' and
        ((type = 'M' and magnitude >= 34) or 
         (type = 'H' and magnitude >= %s) or type = 'W' or
         type = 'T' or (type = 'G' and magnitude >= %s) or type = 'D'
         or type = 'F')
         and valid >= '%s' and valid <= '%s' 
         ORDER by valid ASC", 
         $v["geom"], $v["geom"], $this->sqlLSRTypeBuilder(), 
         $v["wfo"], $this->getVerifyHailSize($v), $this->getVerifyWind($v),
         date("Y/m/d H:i", strtotime($v["issue"])),
         date("Y/m/d H:i", strtotime($v["expire"])) );
        $rs = $this->callDB($sql);
        for ($i=0;$row=@pg_fetch_array($rs,$i);$i++){
            $key = sprintf("%s-%s-%s-%s-%s", 
                   $row["wfo"], $row["valid"], $row["type"],
                   $row["magnitude"], $row["city"]);
            $verify = False;
            if ($v["phenomena"] == "FF"){
                if ($row["type"] == "F") { $verify = True; }
            }
            else if ($v["phenomena"] == "TO"){
                if ($row["type"] == "T") { $verify = True; }
                else { $this->lsrs[$key]["tdq"] = True; }
            }
            else if ($v["phenomena"] == "MA"){
                if ($row["type"] == "W") { $verify = True; }
                else if ($row["type"] == "M") { $verify = True; }
                else if ($row["type"] == "H") { $verify = True; }
            }
            else if ($v["phenomena"] == "SV"){
                if ($row["type"] == "G") { $verify = True; }
                else if ($row["type"] == "D") { $verify = True; }
                else if ($row["type"] == "H") { $verify = True; }
            }
            if ($verify){
                $this->warnings[$k]["verify"] = True;
            }
            if (($verify || $this->lsrs[$key]["tdq"]) &&
                 ! $this->lsrs[$key]['warned'] ){
                $this->warnings[$k]["lsrs"][] = $key;
                $this->lsrs[$key]["warned"] = True;
                $this->lsrs[$key]["leadtime"] = ($this->lsrs[$key]["ts"] - 
                       $v["sts"]) / 60;
                if ($this->warnings[$k]["lead0"] < 0){
               $this->warnings[$k]["lead0"] = $this->lsrs[$key]["leadtime"];
                }
            }
        } /* End of loop over found LSRs */

    } /* End of loop over warnings */
}

} /* End of class cow */
?>
