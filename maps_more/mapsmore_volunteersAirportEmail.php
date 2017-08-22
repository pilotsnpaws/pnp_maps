<?php
// created 2017-04-05 by Mike 
// this is temporary as we can't expose emails to a public page - will move into phpbb format asap

// include the phpbb forum config for db connection
include ( "../forum/config.php");
// get link for goign to user profile, kept in config.php as it changes with new releases sometimes
$profileLinkConfig = $profileLink;

$lineBreak = '<br>';

$con = mysqli_connect($dbhost,$dbuser,$dbpasswd,$dbname);
// check connection
if (mysqli_connect_errno())
    {
    echo "mySQL connect failed: " . mysqli_connect_error() . $lineBreak ;
    }

    $airportCode = isset($_GET['airportCode']) ? $_GET['airportCode'] : '';
    $miles = isset($_GET['miles']) ? $_GET['miles'] : '';
    $debug = isset($_GET['debug']) ? $_GET['debug'] : '';

    // set a default if nothing provided
    if ($airportCode == "" ) {
                       $airportCode = 'KMGY' ;
                       }
    if ($miles == "" ) {
                       $miles = '50' ;
                       }

?>

    <html>

    <head>
        <title>PNP|Volunteers around an airport</title></head>
        <link rel="shortcut icon" href="/forum/favicon.ico">
    </head>

    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            font-family: Arial,Verdana,sans-serif;
            font-size: 13px;            
        }
        th, td {
            padding: 5px;
        }

        .TFtable{
                width:100%; 
                border-collapse:collapse; 
            }
            .TFtable td{ 
                padding:7px; border:#4e95f4 1px solid;
            }
            /* provide some minimal visual accomodation for IE8 and below */
            .TFtable tr{
                background: #b8d1f3;
            }
            /*  Define the background color for all the ODD background rows  */
            .TFtable tr:nth-child(odd){ 
                background: #b8d1f3;
            }
            /*  Define the background color for all the EVEN background rows  */
            .TFtable tr:nth-child(even){
                background: #dae5f4;
            }

    </style>
    <body>

    <br>
    <a href="/forum/viewforum.php?f=5">Return to forum</a>
    <br>

    <br>
        <div id="optionsBox">
            <form method="GET" action="mapsmore_volunteersAirportEmail.php" id="searchform">
            Search for pilots near airport: <input type="text" name="airportCode" id="airportCode" maxlength="4" size="11" placeholder="Airport Code" value="<?

    // prefill the field with the prior search
    echo $airportCode ; 
    
    ?>"/>  
            Radius to show:  
                <select name="miles" id="miles">
                    <option selected="selected"> </option>
                    <option value="10" >10 miles</option>
                    <option value="30" >30 miles</option>
                    <option value="50" >50 miles</option>
                    <option value="100">100 miles</option>
                    <option value="150">150 miles</option>
                    <option value="200">200 miles</option>
                    <option value="300">300 miles</option>
                    <option value="400">400 miles</option>
                    <option value="500">500 miles</option>
									
                </select>
            <input type="submit" name="submit" value="Search" />
            </form>
        </div>

<?

    echo 'Showing pilots within ' . $miles . ' miles of airport code ' . $airportCode ;
    echo $lineBreak ; 

    // sql injection protection
    $airportCode = $con->real_escape_string($airportCode);
    $miles = $con->real_escape_string($miles);

    $query = 'select user_id, username, user_email, pf_flying_radius, fn_distance(a.lat, a.lon, '
    . ' v.lat,v.lon) as distance, '
    . ' a.apt_id AS from_apt, a.apt_id, a.apt_name, a.city, last_visit_human, ' 
    . ' v.apt_id AS vol_apt_id, ' 
	. ' v.apt_name AS vol_apt_name, v.city AS vol_city, v.user_inactive_reason ' 
    . ' from vw_volunteers v, '
    . ' airports a ' 
    . ' where pf_pilot_yn = 1 '  // only show pilots
    . ' and a.apt_id = "' . $airportCode . '" '
    . ' and fn_distance(a.lat, a.lon, v.lat,v.lon) < ' . $miles 
		. ' and user_inactive_reason = 0 ' // only show active users
    . ' order by distance,last_visit_human ' ; 

    if ( $debug == 'yes') echo 'Debug: Yes' . $lineBreak . $lineBreak ;
	if ( $debug == "yes") echo $query . $lineBreak;
	
    $result=mysqli_query($con, $query);
    echo 'Users returned: <b>' . mysqli_affected_rows($con) . '</b>, sorted by miles away, then last time they visited the forum' . $lineBreak ; 

    $rows = array();

    $headerBreak = '</th><th>' ; 
    $colBreak = '</td><td>' ; 

    echo '<table class="TFtable" border="1">' ;
    echo '<tr><th>User' . $headerBreak . 'Email' . $headerBreak . 'Willing to fly' . $headerBreak . 'Miles from ' . 
			$airportCode . '</th><th colspan=2>' .  'Home Airport' . $headerBreak . 'Airport City' . 
			$headerBreak . 'Last active on forum' . '</th></tr>' ; 

    while($row = mysqli_fetch_array($result)) {
        $rows[] = $row;
        $user_id = $row['user_id'];
        $username = $row['username'];
		$email	 = $row['user_email'];
        $flying_radius = $row['pf_flying_radius'];
        $distance = $row['distance'];
        $airportCodeFrom = $row['from_apt'];
        $airportCode = $row['vol_apt_id'];
        $airportName = $row['vol_apt_name'];
        $airportCity = $row['vol_city'];
        $lastVisitDate = $row['last_visit_human'];
        echo '<tr><td><a href="' .  $profileLinkConfig .
						$user_id . '" target=_blank>' . $username . '</a>' . $colBreak . $email .
						$colBreak . $flying_radius . 
						$colBreak . $distance . $colBreak . '<a href="https://www.aopa.org/airports/' . 
						$airportCode . '" target="_blank">' . $airportCode . '</a>' . $colBreak . $airportName . 
            $colBreak . $airportCity . $colBreak . $lastVisitDate . '</td></tr>' ;

    }

    echo '</table>'; 

    echo $lineBreak;

?>
</body>
</html>

    <!-- Google analytics added 2015-05-06 --> 
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-62646402-1', 'auto');
      ga('send', 'pageview');

    </script>
    <!-- End Google analytics added 2015-05-06 --> 
