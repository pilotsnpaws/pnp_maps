<?php

// update 2016-04-13 to move to settings.php for config location
// last updated for production use 2013-08-22  9:17am by Mike Green
// changes:
// 2013-08-22 revised underlying vw_lines time conversion to use correct timestamp, resolved bug with not showing posts in the last few hours
// added parseToXML to all possible strings returned that might contain ampersand or other errant characters

//no cache headers 
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// include forum config file for DB info
include "settings.php";
include ($configPath);

// get DB creds from forum config
$username=$dbuser;
$password=$dbpasswd;
$database=$dbname;
$server=$dbhost;

function parseToXML($htmlStr) 
{ 
  $xmlStr=$htmlStr;
  $xmlStr=str_replace('<','&lt;',$xmlStr); 
  $xmlStr=str_replace('>','&gt;',$xmlStr); 
  $xmlStr=str_replace('"','&quot;',$xmlStr); 
  $xmlStr=str_replace("'",'&#39;',$xmlStr); 
  $xmlStr=str_replace("&",'&amp;',$xmlStr); 
// fix https://github.com/pilotsnpaws/pnp_maps/issues/28
// might be able to remove the above manual ones from the past
// mjg 2020-01-08
//  $xmlStr=str_replace('—','-',$xmlStr);
  $xmlStr=htmlspecialchars($xmlStr,ENT_IGNORE);
  return $xmlStr; 
} 


//default the filter to return 30 day old posts if none provided via URL
$lastPostAge = 30;

// Get parameters from URL
if (isset($_GET['lastPostAge'])){
	$lastPostAge = $_GET["lastPostAge"];
	}


// define mysqli connection
$mysqli = new mysqli($server, $username, $password, $database);
 
 // Check connection
if (mysqli_connect_errno($mysqli))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  } else { } ;

$query = 'select last_post, last_post_human, topic_id, topic_title, pnp_sendZip, ' 
		. 'sendLat, sendLon, pnp_recZip, recLat, recLon, sendCity, recCity, forum_id, trip_status  '  
		. 'from vw_lines '
		. 'where trip_status = \'Open\' '
    . 'AND last_post > date_add(cast(current_date as datetime), INTERVAL -'
		. $lastPostAge
		. ' DAY)';

// echo $query;
$result = $mysqli->query($query);


// Start XML file, echo parent node
header("Content-type: text/xml");
echo '<trips>';

// Iterate through the rows, printing XML nodes for each

 // $row = $result->fetch_assoc(); 
while($row = $result->fetch_assoc()){
  // ADD TO XML DOCUMENT NODE
  echo '<trip ';
  echo 'lastPost="' . $row['last_post'] . '" ';
  echo 'lastPostHuman="' . $row['last_post_human'] . '" ';
  echo 'topicID="' . $row['topic_id'] . '" ';
  echo 'topicTitle="' . parseToXML($row['topic_title']) . '" ';
  echo 'sendZip="' . parseToXML($row['pnp_sendZip']) . '" ';
  echo 'sendLat="' . parseToXML($row['sendLat']) . '" ';
  echo 'sendLon="' . parseToXML($row['sendLon']) . '" ';
  echo 'recZip="' . parseToXML($row['pnp_recZip']) . '" ';
  echo 'recLat="' . parseToXML($row['recLat']) . '" ';
  echo 'recLon="' . parseToXML($row['recLon']) . '" ';
  echo 'sendCity="' . parseToXML($row['sendCity']) . '" ';
  echo 'recCity="' . parseToXML($row['recCity']) . '" ';  
  echo 'forum_id="' . parseToXML($row['forum_id']) . '" ';  
  echo 'trip_status="' . parseToXML($row['trip_status']) . '" ';  
  echo '/>';
}

// End XML file
echo '</trips>';


?>
