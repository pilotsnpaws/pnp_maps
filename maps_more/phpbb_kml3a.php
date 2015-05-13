<?php
// FILL THESE IN WITH YOUR SERVER'S DETAILS
include ( "../forum/config.php");
$con = mysql_connect($dbhost,$dbuser,$dbpasswd)or die( "Unable to connect to database");			@mysql_select_db($dbname) or die( "Unable to select database");
echo '
<html>
<head><title>List Pilots</title></head>
<body>
';
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

	$mode = $_GET[mode];

// MJG 2014-06-17 - added user_type filter to exclude deactived users
$query = 'select '
        . ' phpbb_users.user_id ,'
        . ' phpbb_users.username ,'
        . ' zipcodes.city ,'
        . ' zipcodes.state '
        . ' from phpbb_users,'
        . ' phpbb_profile_fields_data ,'
        . ' zipcodes'
        . ' where ' 
	. ' user_type in (0,3) and'
        . ' phpbb_profile_fields_data.pf_foster_yn = 1 and'
        . ' phpbb_profile_fields_data.user_id = phpbb_users.user_id and '
        . ' zipcodes.zip = phpbb_profile_fields_data.pf_zip_code and '
        . ' zipcodes.lat >= ' . $lowlat . ' and '
        . ' zipcodes.lat <= ' . $hilat . ' and '
        . ' zipcodes.lon >= ' . $lowlon . ' and '
        . ' zipcodes.lon <= ' . $hilon . ' '
        . ' ';

        $result = mysql_query($query);
        if ($result) {
                if (@mysql_num_rows($result)) {
                        ?>
                        <p><b>Result Set:</b></p>
                        <table border="1">
                        <thead>
                        <tr>
			<th>Count</th>
                        <?php
                        for ($i=0;$i<mysql_num_fields($result);$i++) {
                                echo('<th>'.mysql_field_name($result,$i).'</th>');
                        }
                        ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
			$kount = 1;
                        while ($row = mysql_fetch_row($result)) {
                                echo('<tr>');
				echo "<td>$kount</td>";
				$kount++;
                                for ($i=0;$i<mysql_num_fields($result);$i++) {
                                        echo('<td>'.$row[$i].'</td>');
                                }
                                echo('</tr>');
                        }
                        ?>
                        </tbody>
                        </table>
                        <?php
                } else {
                        echo('<p><b>Query OK:</b> '.mysql_affected_rows().' rows affected.</p>');
                }
        } else {
                echo('<p><b>Query Failed:</b> '.mysql_error().'</p>');
        }
        echo('<hr />');

?>
</body>
</html>

