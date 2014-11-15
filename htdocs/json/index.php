<?php 
  include_once "../../config/settings.inc.php";
  include_once "../../include/myview.php";
  $t = new MyView();
  $t->thispage = "current-jsonp";
  $t->headextra = "<style>
  .sect {
	border-top: 1px solid #ccc; 
	background: #EEFFCC; 
	border-bottom: 1px solid #ccc; 
	margin-left: 25px;
  		padding-left: 5px;
  		line-height: 1.3em;
  		margin-bottom: 10px;
  	}
  		</style>";
  $t->title = "JSON(P) Web Services";


  $services = array();
  
  $services[] = Array(
  		"title" => "Daily NWS Climate (CLI Product) Summaries",
  		"url" => "/geojson/cli.py?dt={dt}&fmt={fmt}",
  		"desc" => "Provides a GeoJSON response summarizing all of the atomic
  		processed data from the NWS issued CLI reports.  These reports contain
  		daily temperature, precipitation, and snow data from the primary 
  		climate sites.",
  		"vars" => Array(
  				"dt" => "YYYY-mm-dd Date you want data for (optional)",
  				"fmt" => "Return format (optional) geojson (default) or csv",
  		),
  		"example" => Array(
  				"{dt}" => "2014-10-09",
  				"{fmt}" => "geojson",
  		)
  );
  
  $services[] = Array(
  		"title" => "IEM Tile Map Service Metadata",
  		"url" => "/json/tms.json?",
  		"desc" => "Provides metadata about the currently available Tile Map
  		Services provided by the IEM.  This is useful to determine how to 
  		call back to the Tile Map Services.",
  		"vars" => Array(
  		),
  		"example" => Array(
  		)
  );
  
  $services[] = Array(
  		"title" => "Current Storm Based Warnings",
  		"url" => "/geojson/sbw.geojson?",
  		"desc" => "Provides a geojson format of current National Weather Service
  		storm based warnings.  There is a 15 second caching done by the server
  		to ease load.  The generation_time attribute is set on the output 
  		to diagnose when the file is valid.",
  		"vars" => Array(
  		),
  		"example" => Array(
  		)
  );
  
  $services[] = Array(
  		"title" => "Search for Warnings by UGC Code and Date Interval",
  		"url" => "/json/vtec_events_byugc.php?ugc={ugc}&edate={edate}&sdate={sdate}",
  		"desc" => "Provides a json response of archived warnings valid for the
  		given UGC code and date interval (UTC time, end date exclusive). The date
  		of product issuance is used for the date filtering.",
  		"vars" => Array(
  				"ugc" => "Five character UGC identifier used by the NWS",
  				"sdate" => "YYYY-mm-dd Start Date (UTC)",
  		  		"edate" => "YYYY-mm-dd End Date (UTC)",
  		),
  		"example" => Array(
  				"{ugc}" => "IAC001",
  				"{sdate}" => "1990-06-01",
  				"{edate}" => "1990-07-01",
  		)
  );
  $services[] = Array(
  		"title" => "Storm Prediction Center Watches",
  		"url" => "/json/spcwatch.py?ts={timestamp}",
  		"desc" => "Provides a geojson format of SPC watches valid at given time
		or current time if no time specified.",
  		"vars" => Array(
  				"timestamp" => "YYYYMMDDHHMI UTC Timestamp (optional)"
  		),
  		"example" => Array(
  				"{timestamp}" => "201104280000"
  		)
  );
  $services[] = Array(
  		"title" => "RAOB Soundings",
  		"url" => "/json/raob.py?ts={timestamp}&station={station}",
  		"desc" => "Provides a single sounding profile for the given station,
		either a 3 or 4 character site ID and a UTC timestamp.  Realtime data
		from this service is typically available within 2 hours of observation
		time.",
  		"vars" => Array(
  				"sid" => "3 or 4 character site ID used in North America",
  				"timestamp" => "YYYYMMDDHHMI UTC Timestamp"
  		),
  		"example" => Array(
  				"{timestamp}" => "199905031200",
  				"{station}" => "KOUN",
  		)
  );
  
  $services[] = Array(
  		"title" => "SHEF Station Variables",
  		"url" => "/json/dcp_vars.php?station={station}",
  		"desc" => "Provides SHEF variables provided by this station.",
  		"vars" => Array(
  				"station" => "National Weather Service Location Identifier (nwsli)",
  		),
  		"example" => Array(
  				"{station}" => "AMWI4",
  		)
  );
  
  $services[] = Array(
  		"title" => "Data Collection Network Details",
  		"url" => "/json/network.php?network={network}",
  		"desc" => "The IEM bunches observation stations into networks. This
service provides metadata for sites within a network.  A listing of networks
can be found on <a href='/sites/locate.php'>this page.</a>",
  		"vars" => Array(
  				"network" => "IEM Network Identifier",
  		),
  		"example" => Array(
  				"{network}" => "IA_ASOS",
  		)
  );
  
  $services[] = Array(
  		"title" => "IEM Archived Data Products",
  		"url" => "/json/products.php?",
  		"desc" => "The IEM generates and archives a large number of products.
 This service provides some metadata details necessary to build programic URIs
 against this archive of data.  This service drives the <a href='/timemachine'>Timemachine</a>
 application.",
  		"vars" => Array(
  		),
  		"example" => Array(
  		)
  );
  
  $services[] = Array(
  		"title" => "RIDGE Single Site Available NEXRADs",
  		"url" => "/json/radar?operation=available&amp;lat={lat}&amp;lon={lon}&amp;start={start}",
  		"desc" => "This service returns an estimate of which NEXRAD RADARs have
imagery available for the timestamp and latitude / longitude location you specify.",
  		"vars" => Array(
  				"lat" => "Point location latitude (degrees north)",
  				"lon" => "Point location longitude (degrees east)",
  				"start" => "ISO-8601 UTC Timestamp"
  		),
  		"example" => Array(
  				"{lat}" => 41.99,
  				"{lon}" => 93.50,
  				"{start}" => "2012-12-01T00:00:00Z",
  		)
  );
  
  $services[] = Array(
  		"title" => "RIDGE Current Metadata by Product",
  		"url" => "/json/ridge_current.py?product={product}",
  		"desc" => "This service returns a listing of the most recent image for
  		a given product from all collected RADAR sites.",
  		"vars" => Array(
  				"product" => "Level III Product {N0Q, N0S, N0U, N0Z, NET}"
  		),
  		"example" => Array(
  				"{product}" => 'N0Q'
  		 )
  );
  
  $services[] = Array(
  		"title" => "RIDGE Single Site Available Products for single NEXRAD",
  		"url" => "/json/radar?operation=products&amp;radar={radar}&amp;start={start}",
  		"desc" => "This service returns available NEXRAD level 3 products for
a given RADAR and date.",
  		"vars" => Array(
  				"radar" => "NEXRAD 3 character identifier",
  				"start" => "ISO-8601 UTC Timestamp"
  		),
  		"example" => Array(
  				"{radar}" => 'DMX',
  				"{start}" => "2012-12-01T00:00:00Z",
  		)
  );
  
  $services[] = Array(
  		"title" => "RIDGE Single Site Available Volume Scan Times",
  		"url" => "/json/radar?operation=list&amp;radar={radar}&amp;product={product}&amp;start={start}&amp;end={end}",
  		"desc" => "This service returns NEXRAD volume scan times for a given
RADAR, level 3 product, and start/end timestamp.",
  		"vars" => Array(
  				"radar" => "NEXRAD 3 character identifier",
  				"product" => "Three character level 3 NEXRAD product identifier.",
  				"start" => "ISO-8601 UTC Timestamp",
  				"end" => "ISO-8601 UTC Timestamp"
  		),
  		"example" => Array(
  				"{radar}" => 'DMX',
  				"{product}" => 'N0Q',
  				"{start}" => "2012-12-01T00:00:00Z",
  				"{end}" => "2012-12-01T23:59:59Z",
  		)
  );
  
  $services[] = Array(
  		"title" => "NWS State UGC Codes",
  		"url" => "/json/state_ugc.php?state={state}",
  		"desc" => "This service returns metadata for UGC codes used by the
National Weather Service to issue warnings for in a given state.",
  		"vars" => Array(
  				"state" => "Two character state identifier",
  		),
  		"example" => Array(
  				"{state}" => 'IA'
  		)
  );
  
  $services[] = Array(
  		"title" => "NWS Text Product",
  		"url" => "/json/nwstext.py?product_id={product_id}",
  		"desc" => "This service returns the raw text of a NWS Text Product.",
  		"vars" => Array(
  				"product_id" => "String that uniquely (not fully) indentifies a text product.",
  		),
  		"example" => Array(
  				"{product_id}" => '201302241745-KDMX-FXUS63-AFDDMX'
  		)
  );
  
  $services[] = Array(
  		"title" => "IEM Tracked Station Metadata Changes",
  		"url" => "/json/stations.php?date={date}",
  		"desc" => "This service returns metadata for any IEM tracked
station locations with changed metadata since the given date.  This provides
a programic mechanism to keep up with metadata updates done on a daily basis.",
  		"vars" => Array(
  				"date" => "Request changes since this date",
  		),
  		"example" => Array(
  				"{date}" => date('Y-m-d')
  		)
  );
  
  $services[] = Array(
  		"title" => "IEM Webcam Availability",
  		"url" => "/json/webcams.php?network={network}&amp;ts={ts}",
  		"desc" => "This service returns metadata on available webcam imagery
for a given network that collects webcams and a UTC timestamp.",
  		"vars" => Array(
  				"network" => "IEM Webcam network (KCCI, KELO, KCRG, IDOT)",
  				"ts" => "UTC Timestamp that you want images for",
  		),
  		"example" => Array(
  				"{network}" => "KCCI",
  				"{ts}" => "201212070600",
  		)
  );
  $table = "";
  while (list($key, $ws) = each($services)){
  	$table .= sprintf("<div class='sect'><strong>%s</strong>
	<br /><strong>URI:</strong> %s%s&amp;callback=gotData
	<br /><strong>Description:</strong> %s
	<br /><strong>Method GET Parameters:</strong>
	<br /><table border='1' cellspacing='0' cellpadding='3'>",
  			$ws["title"], ROOTURL, $ws["url"], $ws["desc"]);
  	while (list($key2, $vs) = each($ws['vars'])){
  		$table .= sprintf("<tr><th>%s</th><td>%s</td></tr>", $key2, $vs);
  	}
  	$table .= "</table>";
  	$url = $ws['url'];
  	while (list($key2, $vs) = each($ws['example'])){
  		$url = str_replace($key2, $vs, $url);
  	}
  	$table .= sprintf("<br /><a href=\"%s%s\">Example JSON</a>
     	&nbsp; <a href=\"%s%s&callback=gotData\">Example JSONP</a>",
  			ROOTURL, $url, ROOTURL, $url);
  	$table .= "</div>\n";
  }
  
  $t->content = <<<EOF
<h3>IEM Provided JSON-P Webservices</h3>

<p>This page is an attempt to document the various JSON-P web services the
 IEM uses to drive its various applications.  These services are provided without
 warranty and may be disabled without warning if "bad things happen". We do
 try our best to keep them running, so please <a href="/info/contacts.php">Contact Us</a>
 if you have difficulties.
 
 <p>These services support <a href="http://en.wikipedia.org/wiki/JSONP">JSONP</a>,
 which is a technique that allows the deliver of data from a cross-domain origin.
 The technique involves placing or injecting &lt;script&gt; tags into your webpage
 and specifying a javascript function to callback.  For example,
 <br /><pre>
 &lt;script type="text/javascript"&gt;
  function gotData(data){
     console.log(data);
  }
 &lt;/script&gt;
 &lt;script type="text/javascript"
         src="http://mesonet.agron.iastate.edu/json/example.php?callback=gotData"&gt;
 
 </pre>
 
 <p>This &lt;script&gt; tag above that requests data from this server will return
 javascript that looks like:
 <br /><pre>
 gotData({"Name": "daryl", "Profession": "nerd", "Age": 99});
 </pre>

 <p>Okay, so you are all set for documentation on what services are available!
{$table}
 
 <p>That is all for now. Enjoy!
 
EOF;
$t->render('single.phtml');
 ?>
