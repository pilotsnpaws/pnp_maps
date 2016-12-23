<?php

// last updated for production use 2014-01-22  by Mike Green
// changes:
// this supports maps_single_trip.php

//no  cache headers 
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
  $xmlStr=str_replace('<','&lt;',$htmlStr); 
  $xmlStr=str_replace('>','&gt;',$xmlStr); 
  $xmlStr=str_replace('"','&quot;',$xmlStr); 
  $xmlStr=str_replace("'",'&#39;',$xmlStr); 
  $xmlStr=str_replace("&",'&amp;',$xmlStr); 
  return $xmlStr; 
} 


//default the filter to return a year old posts if none provided via URL
$topic = 23419;

// Get parameters from URL
if (isset($_GET['topic'])){
	$topic = $_GET["topic"];
	}


// define mysqli connection
$mysqli = new mysqli($server, $username, $password, $database);
 
 // Check connection
if (mysqli_connect_errno($mysqli))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  } else { } ;

$query = 'select DISTINCT last_post, last_post_human, topic_id, topic_title, pnp_sendZip, ' 
		. 'sendLat, sendLon, pnp_recZip, recLat, recLon, sendCity, recCity '  
		. 'from vw_lines ' 
		. 'where topic_id = '
		 . $topic ;

// echo $query;
$result = $mysqli->query($query);


// Start XML file, echo parent node
header("Content-type: text/xml");
echo '<trips>';

// Iterate through the rows, printing XML nodes for each

//$row = $result->fetch_assoc();
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
  echo '/>';
}

// End XML file
echo '</trips>';


?>
