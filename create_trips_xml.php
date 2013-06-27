<?php
require("phpsqlajax_dbinfo.php");

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
$lastPostAge = 365;

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
		. 'sendLat, sendLon, pnp_recZip, recLat, recLon '  
		. 'from vw_lines '
		. 'where last_post > date_add(cast(current_date as datetime), INTERVAL -'
		. $lastPostAge
		. ' DAY)';

// echo $query;
$result = $mysqli->query($query);


// Start XML file, echo parent node
header("Content-type: text/xml");
echo '<trips>';

// Iterate through the rows, printing XML nodes for each

$row = $result->fetch_assoc();
while($row = $result->fetch_assoc()){
  // ADD TO XML DOCUMENT NODE
  echo '<trip ';
  echo 'lastPost="' . $row['last_post'] . '" ';
  echo 'lastPostHuman="' . $row['last_post_human'] . '" ';
  echo 'topicID="' . $row['topic_id'] . '" ';
  echo 'topicTitle="' . $row['topic_title'] . '" ';
  echo 'sendZip="' . $row['pnp_sendZip'] . '" ';
  echo 'sendLat="' . $row['sendLat'] . '" ';
  echo 'sendLon="' . $row['sendLon'] . '" ';
  echo 'recZip="' . $row['pnp_recZip'] . '" ';
  echo 'recLat="' . $row['recLat'] . '" ';
  echo 'recLon="' . $row['recLon'] . '" ';
  echo '/>';
}

// End XML file
echo '</trips>';


?>
