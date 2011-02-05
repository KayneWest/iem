<?php
  // Here is where we start pulling station Information
function printTags($tokens)
{
  global $rooturl;
  if (sizeof($tokens) == 0 || $tokens[0] == ""){ return "";}
  $s = "<br /><span style=\"font-size: smaller; float: left;\">Tags: &nbsp; ";
  while (list($k,$v) = each($tokens))
  {
    $s .= sprintf("<a href=\"%s/onsite/features/tags/%s.html\">%s</a> &nbsp; ", $rooturl, $v, $v);
  }
  $s .= "</span>";
  return $s;
}

function genFeature()
{
  global $rooturl;

  $connection = iemdb("mesosite");
  $query1 = "SELECT oid, *, to_char(valid, 'YYYY/MM/YYMMDD') as imageref, 
                to_char(valid, 'DD Mon YYYY HH:MI AM') as webdate,
                to_char(valid, 'YYYY-MM-DD') as permalink from feature
                ORDER by valid DESC LIMIT 1";
  $result = pg_exec($connection, $query1);
  $row = @pg_fetch_array($result,0);
  $foid = $row["oid"];
  $good = intval($row["good"]);
  $bad = intval($row["bad"]);
  $tags = explode(",", $row["tags"]);
  $fbid = $row["fbid"];
  $fburl = "http://www.facebook.com/pages/IEM/157789644737?v=wall&story_fbid=".$fbid;
  /* Hehe, check for a IEM vote! */
  $voted = 0;
  if (array_key_exists('foid', $_COOKIE) && $_COOKIE["foid"] == $foid)
  { 
    $voted = 1;
  } elseif (getenv("REMOTE_ADDR") == "129.186.142.22" || getenv("REMOTE_ADDR") == "129.186.142.37") 
  {

  } elseif (isset($_GET["feature_good"]))
  {
    setcookie("foid", $foid, time()+100600);
    $voted = 1;

    $isql = "UPDATE feature SET good = good + 1 WHERE oid = $foid";
    $good += 1;
    pg_exec($connection, $isql);
  } elseif (isset($_GET["feature_bad"]))
  {
    setcookie("foid", $foid, time()+100600);
    $voted = 1;

    $isql = "UPDATE feature SET bad = bad + 1 WHERE oid = $foid";
    $bad += 1;
    pg_exec($connection, $isql);
  }


  $fref = "/mesonet/share/features/". $row["imageref"] ."_s.png";
  list($width, $height, $type, $attr) = @getimagesize($fref);
  $width += 6;

  $s = "<span style=\"font-size: larger; font-weight: bold;\">". $row["title"] ."</span><br />\n";
  $s .= "<span style=\"font-size: smaller; float: left;\">Posted: " . $row["webdate"] ."</span>";
  $s .= printTags($tags);
  $s .= "<span style=\"font-size: smaller; float: right;\">
  <a href=\"$fburl\"><img src=\"http://facebook.com/favicon.ico\" border=\"0\" />Facebook</a> | 
  <a href=\"$rooturl/onsite/features/cat.php?day=". $row["permalink"] ."\">Permalink</a> | 
  <a href=\"$rooturl/onsite/features/past.php\">Past Features</a> | 
  <a href=\"$rooturl/onsite/features/tags/\">Tags</a></span>";

 /* Feature Image! */
  $s .= "<div style=\"background: #eee; float: right; border: 1px solid #ee0; padding: 3px; margin-left: 10px; width: ${width}px;\"><a href=\"$rooturl/onsite/features/". $row["imageref"] .".png\"><img src=\"$rooturl/onsite/features/". $row["imageref"] ."_s.png\" alt=\"Feature\" /></a><br />". $row["caption"] ."</div>";

  $s .= "<br /><div class='story' style=\"text-align: justify;\">". $row["story"] ."</div>";

/* Rate Feature and Past ones too! */
$s .= "<br clear=\"all\" />";
$s .= "<div style=\"float: left; margin-bottom: 10px; margin-left: 15px; \">";
if ($row["voting"] == "f"){
  
} else if ($voted){
  $s .= "<strong> Rate Feature: </strong> Good ($good votes) or Bad ($bad votes) &nbsp; Thanks for voting!";

} else {
  $s .= "<strong> Rate Feature: </strong> <a href=\"$rooturl/index.phtml?feature_good\">Good</a> ($good votes) or <a href=\"$rooturl/index.phtml?feature_bad\">Bad</a> ($bad votes)";
}
$s .= "<div id=\"fb-root\"></div><script src=\"http://connect.facebook.net/en_US/all.js#appId=196492870363354&amp;xfbml=1\"></script>
	<fb:comments callbackurl=\"$rooturl/onsite/features/cat.php?day=". $row["permalink"] ."\" xid=\"$fbid\" numposts=\"6\" width=\"520\"></fb:comments>";

$s .= "</div>";

/* Now, lets look for older features! */
$s .= "<br clear=\"all\" /><strong>Previous Years' Features</strong><table width=\"100%\">";
$sql = "select *, extract(year from valid) as yr from feature WHERE extract(month from valid) = extract(month from now()) and extract(day from valid) = extract(day from now()) and extract(year from valid) != extract(year from now()) ORDER by yr DESC";
$result = pg_exec($connection, $sql);
for($i=0;$row=@pg_fetch_array($result,$i);$i++)
{
  if ($i % 2 == 0){ $s .= "<tr>"; }
  $s .= "<td width=\"50%\">". $row["yr"] .": <a href=\"onsite/features/cat.php?day=". substr($row["valid"], 0, 10) ."\">". $row["title"] ."</a></td>";
  if ($i % 2 != 0){ $s .= "</tr>"; }
}
$s .= "</table>";



if (getenv("REMOTE_ADDR") == "192.188.162.21" )
{
 $s = "<img src=\"images/i3.jpg\" style=\"float: left; margin: 5px;\">
Smokey, muah! <br /> &nbsp; &nbsp; &nbsp; &nbsp; 253 weeks now!!!!  Hehe, no more features, no more teachers, no more books, no more classes, dirty looks!  darly is liberated from the feature today!  Fancy that we made it this far, marriage will be simple compared with 253 weeks of courtship.  Darly have all sorts of free time now that he doesn't have to post smokey feature every week. I love you very much!<br />&nbsp; &nbsp; &nbsp; &nbsp;   &nbsp; &nbsp; &nbsp; &nbsp;  love, darly";

  $s .= "<br style=\"clear: right;\" /><b>Rate Feature:</b> <a href=\"$rooturl/index.phtml?feature_good\">Good</a> ($good votes) or <a href=\"$rooturl/index.phtml?feature_bad\">Bad</a> ($bad votes) &nbsp; &nbsp;<a href=\"$rooturl/onsite/features/past.php\">Past Features</a>";
}

  return $s;
}
?>
