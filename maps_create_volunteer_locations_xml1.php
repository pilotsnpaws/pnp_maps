<?php

	//no  cache headers 
	header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");


// include forum config file for DB info
include ( "../forum/config.php");

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

//default the filters if none provided via URL
$lastVisitAge = 180;
$typesToShow = 'all' ; 
$airportCode = 'KMGY' ;
// $zipCode = 'none'; 
$distance = 10 ;
$topicID = 0 ;

$typesFilterSQL = ' 1=1 ' ;
$distanceFilterSQL = ' 1=1 ' ;

// Get parameters from URL
if (isset($_GET['lastVisitAge'])){
	$lastVisitAge = $_GET["lastVisitAge"];
	}
if (isset($_GET['typesToShow'])){
	$typesToShow= $_GET["typesToShow"];
	}
if (isset($_GET['zipCode'])){
	$zipCode= $_GET["zipCode"];
	}
	else
	{ $zipCode = '' ;
	}
if (isset($_GET['distance'])){
	$distance= $_GET["distance"];
	}
if (isset($_GET['topicID'])){
	$topicID= $_GET["topicID"];
	}
if ($topicID == ''){
	$topicID = 'foo';
	} 
	else
	{	$distanceFilterSQL = ' apt_id in (select apt_id from vw_boxed_airports where topic_id = ' 
		. $topicID
		// . ' HAVING distance < '
		// . $distance
		. ' ) ' ;
	} 


if ($typesToShow == 'both')
	{ $typesFilterSQL = ' pf_pilot_yn = 1 and pf_foster_yn = 1 ';
	}
	elseif ($typesToShow == 'pilot')
	{ $typesFilterSQL = ' pf_pilot_yn = 1 ';
	}
	elseif ($typesToShow == 'foster')
	{ $typesFilterSQL = ' pf_foster_yn = 1 ';
	}
	elseif ($typesToShow == 'volunteer')
	{ $typesFilterSQL = ' pf_pilot_yn = 2 and pf_foster_yn = 2 ';
	}
	else 
	{ $typesFilterSQL = ' 1=1 ';
	}
	;
	
// define mysqli connection
$mysqli = new mysqli($server, $username, $password, $database);
 
 // Check connection
if (mysqli_connect_errno($mysqli))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  } else { } ;

$query = 'select last_visit, last_visit_human, user_id, username, pf_foster_yn, pf_pilot_yn, pf_flying_radius,  ' 
		. 'apt_id, apt_name, zip, '
		. 'lat, lon, city, state '  
		. 'from vw_volunteers '
		. 'where 1=1 '
		. ' and last_visit > date_add(cast(current_date as datetime), INTERVAL -'
		. $lastVisitAge 
		. ' DAY) and ' 
		. $typesFilterSQL 
		. 'and '
		. $distanceFilterSQL ;

// echo $query;
$result = $mysqli->query($query);

// Start XML file, echo parent node
header("Content-type: text/xml");
echo '<volunteers>';

// Iterate through the rows, printing XML nodes for each

// $row = $result->fetch_assoc(); --if left then will not include first row
while($row = $result->fetch_assoc()){
  // ADD TO XML DOCUMENT NODE
  echo '<volunteer ';
  echo 'lastVisit="' . $row['last_visit'] . '" ';
  echo 'lastVisitHuman="' . $row['last_visit_human'] . '" ';
  echo 'userID="' . $row['user_id'] . '" ';
  echo 'username="' . parseToXML($row['username']) . '" ';
  echo 'foster="' . $row['pf_foster_yn'] . '" ';
  echo 'pilot="' . $row['pf_pilot_yn'] . '" ';
  echo 'flyingRadius="' . $row['pf_flying_radius'] . '" ';
  echo 'airportID="' . parseToXML($row['apt_id']) . '" ';  
  echo 'airportName="' . parseToXML($row['apt_name']) . '" ';  
  echo 'zip="' . $row['zip'] . '" ';  
  echo 'lat="' . $row['lat'] . '" ';
  echo 'lon="' . $row['lon'] . '" ';
  echo 'city="' . parseToXML($row['city']) . '" ';
  echo 'state="' . parseToXML($row['state']) . '" ';
  echo '/>';
}

// End XML file
echo '</volunteers>';

?>