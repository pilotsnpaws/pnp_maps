<?php
function xmlspecialchars($text) {
   return str_replace('-' , ' ' , str_replace('&#039;', '&apos;', htmlspecialchars($text, ENT_QUOTES)));
}
include ( "../forum/config.php");
$con = mysql_connect($dbhost,$dbuser,$dbpasswd)or die( "Unable to connect to database");			@mysql_select_db($dbname) or die( "Unable to select database");

	$mode = $_GET[mode];
	$fromzip=$_GET[fromzip];
	$tozip=$_GET[tozip];
	if ($tozip == "" ) {
	                   $tozip = substr ( $fromzip , 5 , 5  );
	                   $fromzip = substr ( $fromzip , 0 , 5 ) ;
					   }
	$query='select lat , lon from zipcodes where zip="' . $fromzip . '"';
	if ( $dbg == "yes") echo $query;
	$result=mysql_query($query);
	if ( $dbg == "yes")  echo mysql_numrows($result);
	$fromlat = mysql_result($result,0,"lat");
	$fromlon = mysql_result($result,0,"lon");
	$query='select lat , lon from zipcodes where zip="' . $tozip . '"';
    if ( $dbg == "yes") echo $query;
	$result=mysql_query($query);
	
	if ( $dbg == "yes") echo mysql_numrows($result);

	$tolat = mysql_result($result,0,"lat");
	$tolon = mysql_result($result,0,"lon") ;
	if ( $dbg == "yes") echo " $fromlat , $tolat , $fomlon , $tolin" ;
	$lowlat =  min ( $fromlat - 1 , $tolat - 1 ) ;
	$lowlon =  min ( $fromlon - 1 , $tolon - 1 ) ;
	$hilat =  max ( $fromlat + 1 , $tolat + 1 ) ;
	$hilon =  max ( $fromlon + 1 , $tolon + 1 ) ;

	$query= "select contact_name , pnp_name , pnp_id , lat , lon ,  public_comment,	 
	type , city , state , airport, email, email_alt, cell_num, home_num, other
	from contacts where lat >= $lowlat and lat <= $hilat and lon >= $lowlon and lon <= $hilon " ;

// based on phpbb_kml2.php
// the query below looks for pf_pilot_yn = 0 and returns an empty set 
// because all users are either pf_pilot_yn = 1 or pf_pilot_yn = 2
// therefore the map displays the transport route without pins

$query = 'select '
        . ' phpbb_users.user_id ,'
        . ' phpbb_users.username ,'
        . ' phpbb_users.user_email , '
        . ' phpbb_profile_fields_data.user_id ,'
        . ' phpbb_profile_fields_data.pf_airport_id ,'
        . ' airports.apt_id ,'
        . ' airports.apt_name ,'
        . ' airports.lat ,'
        . ' airports.city ,'
        . ' airports.state ,'
        . ' airports.lon'
        . ' from phpbb_users,'
        . ' phpbb_profile_fields_data ,'
        . ' airports'
        . ' where '
// BEGIN MOD
        . ' phpbb_profile_fields_data.pf_pilot_yn = 0 and'
// END MOD
        . ' phpbb_profile_fields_data.user_id = phpbb_users.user_id and '
        . ' airports.apt_id = UCASE(phpbb_profile_fields_data.pf_airport_id) and '
        . ' airports.lat >=' . $lowlat . 'and '
        . ' airports.lat <=' . $hilat . 'and '
        . ' airports.lon >=' . $lowlon . 'and '
        . ' airports.lon <=' . $hilon;


	if ( $dbg == "yes")  echo $query;
	$result=mysql_query($query);
        if ($mode == "email" ) echo "<html><body>";
else {
	header('Content-type: application/vnd.google-earth.kml+xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>
		 <kml xmlns="http://earth.google.com/kml/2.2">
		 <Document>
		 		   <name>Pilots N Paws - Transport Route</name>
				   <description>
				      <![CDATA[Pilots N Paws - Transport Route www.pilotsnpaws.org]]>
				   </description>';
  

  
  
  
  	   echo '<Placemark><LineString>
        <extrude>1</extrude>
        <tessellate>1</tessellate>
        <altitudeMode>absolute</altitudeMode>
        <coordinates>';
		echo " $fromlon , $fromlat , 0 ";
		echo '
		';
		echo "$tolon , $tolat , 0 ";
				echo '
		';

		echo '</coordinates>
          </LineString>
		  </Placemark>';
}
       $num=mysql_numrows($result);
       mysql_close;
       $i=0;
       while ($i < $num) {
	   $contact_name = mysql_result($result,$i,"phpbb_users.username");  	   
	   $pnp_name = mysql_result($result,$i,"phpbb_users.username");
	   $pnp_id = mysql_result($result,$i,"phpbb_users.user_id");
	   $lat = mysql_result($result,$i,"airports.lat");
	   $lon = mysql_result($result,$i,"airports.lon");
	   $type = "PILOT";
	   $city = mysql_result($result,$i,"airports.city");
	   $state = mysql_result($result,$i,"airports.state");
	   $airport = mysql_result($result,$i,"airports.apt_id");
	   $email = mysql_result($result,$i,"phpbb_users.user_email");
/*	   $public_comment = xmlspecialchars (mysql_result($result,$i,"public_comment") ); */
	   /*	   $public_comment = "Public Comment"; */
	   $public_comment = $type;
if ( $mode == "email" )
echo $contact_name . "&lt;" . $email . "&gt;<br>" ;
else
{
	   echo '<Placemark>';
  	   echo '<Style>
      <IconStyle>
        <Icon>
          <href>root://icons/palette-2';

		  echo '.png</href>
          <x>';
		  echo 0;
		  echo '</x>
          <y>';
		  echo 0;
		  echo '</y>
          <w>32</w>
          <h>32</h>
        </Icon>
      </IconStyle>
    </Style>';
	
if ( $mode == "private" ) {
echo '<name>'; 
echo $pnp_name ;
echo '</name><description><![CDATA[<div="ltr"><br>';
echo 'Name:' . $contact_name . '<br>';
if ( $cell_num > " " ) echo 'Cell_num:' . $cell_num . '<br>';
if ( $email > " " ) echo 'Email:' . $email . '<br>';
if ( $city > " " ) echo 'city:' . $city . '<br>';
if ( $state > " " ) echo 'state:' . $state . '<br>';
if ( $email_alt > " " ) echo 'email_alt:' . $email_alt . '<br>';
if ( $home_num > " " ) echo 'home_num:' . $home_num . '<br>';
	echo '<br><br></div>]]>'; 
	echo '</description>';
   }
else
{

	   echo '<name>';
	   echo $pnp_name;
	   echo '</name><description>';
	   echo '<![CDATA[<div dir="ltr">';
	    
/*	echo $contact_name .'<br>' ; */
	echo $type . '<br>';
	echo $city . ', ' . $state . '  (' . $airport . ')<br>';
	echo '<br><br><a href="http://pilotsnpaws.org/forum/memberlist.php?mode=viewprofile&amp;u=';
	echo $pnp_id ; 
	echo '">View Profile for ';
	echo  $pnp_name ;
	echo '</a><br><br><a href="http://pilotsnpaws.org/forum/ucp.php?i=pm&amp;mode=compose&amp;u=';
	echo $pnp_id ;
	echo '">Request Transport Assistance from ';
	echo $pnp_name ;
	echo '</a>'; 
	echo '<br><br></div>]]>'; 
	echo '</description>';
	}
/*	echo '<styleUrl>#style1</styleUrl>'; */
    echo '<Point>
      <coordinates>';
	  echo $lon ;
	  echo ',' ;
	  echo $lat ;
	  echo ',0.000000</coordinates>
    </Point>
  </Placemark>';
}
	       $i++;
	       }
		   echo 
		   '</Document></kml>';
       ?>
