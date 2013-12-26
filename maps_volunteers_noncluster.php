<!DOCTYPE html >
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <title>Pilotsnpaws.org Volunteer Location Map</title>
    <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyD7Dabm2M9XvDVk27xCZomEZ1uJFcJHG4k&sensor=false"></script>
    <script type="text/javascript">
    //<![CDATA[
		
	var map;
	var flightPaths = [];
	var lastVisitAge;
	function initialize() {
		var mapOptions = {
			zoom: 4,
			center: new google.maps.LatLng(37.000000,-95.000000),
			scaleControl: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		  };
		map = new google.maps.Map(document.getElementById('gMap'),mapOptions);
		updateVolunteers();

		// add options box
		map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('optionsBox'));

		// add legend table
		map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(document.getElementById('legend'));

		// add legend table
		map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(document.getElementById('clusterNotif'));

	}

	function get_checked_radio(radios) {
	    for (var i = 0; i < radios.length; i++) {
		var current = radios[i];
		if (current.checked) {
		    return current;
		}
	    }
	}

	function updateVolunteers() {

	removeFlightPaths();

	lastVisitAge = document.getElementById("lastVisitAge").value;
	typeToShow = get_checked_radio(document.getElementsByName("typesToShow")).value;
	
	var searchURL = "maps_create_volunteer_locations_xml.php?lastVisitAge=" + lastVisitAge + "&typesToShow=" + typeToShow;
	downloadUrl(searchURL, function(data) {
	var xml = data.responseXML;
	
	var infoWindow = new google.maps.InfoWindow();
		
	var volunteers = xml.documentElement.getElementsByTagName("volunteer");
		for (var i = 0; i < volunteers.length; i++) {
			var username = volunteers[i].getAttribute("username");
			var userID = volunteers[i].getAttribute("userID");
			var lastVisit = volunteers[i].getAttribute("lastVisit");
			var lastVisitHuman = volunteers[i].getAttribute("lastVisitHuman");
			var foster = volunteers[i].getAttribute("foster");
			var pilot = volunteers[i].getAttribute("pilot");
			var flyingRadius= volunteers[i].getAttribute("flyingRadius");
			var airportID = volunteers[i].getAttribute("airportID");
			var airportName = volunteers[i].getAttribute("airportName");
			var zip = volunteers[i].getAttribute("zip");
			var lat = volunteers[i].getAttribute("lat");
			var lon = volunteers[i].getAttribute("lon");
			var city = volunteers[i].getAttribute("city");
			var state = volunteers[i].getAttribute("state");
			var volunteerCoordinates = new google.maps.LatLng(lat, lon);

			// is volunteer a foster or pilot, both, or neither?
			// both foster and pilot
			if ( (foster == '1') && (pilot == '1') ) 
				{ var markerImage = 'images/icon_plane_house_small.svg' ;
					var pilotInfo = 'Flying distance : <b>' + flyingRadius + 'nm </b><br> Airport: <b>' + airportID + ' - ' + airportName + '</b><br>'  ;
				}
			// just foster
			else if (foster == '1') 
				{ var markerImage = 'images/icon_house_small.svg' ; 
				var pilotInfo = '';
				}
			// just pilot
			else if (pilot == '1')
				{ var markerImage = 'images/icon_plane_blue_small.svg' ; 
				var pilotInfo = 'Flying distance: <b>' + flyingRadius + 'nm </b><br> Airport: <b>' + airportID + ' - ' + airportName + '</b><br> ' ;
				}
			else  // then must be non-foster non-pilot volunteer
				{ var markerImage = 'images/icon_volunteer.svg' ; 
				var pilotInfo = '';
				}
			
			var volunteerMarker = new google.maps.Marker({
				position: volunteerCoordinates,
				radius: flyingRadius * 1852, // 1852 meters in a nautical mile
				icon: markerImage,
				optimized: false,				
				html: '<div style=white-space:nowrap;margin:0 0 10px 10px;>' +  
					'Username: <a href=http://www.pilotsnpaws.org/forum/memberlist.php?mode=viewprofile&u=' + userID +
					' target="_blank" >' + username + '</a> <br>' + 
					' <img align="right" vertical-align="top" src="' + markerImage + '"> ' +
					pilotInfo + 
					'Last visit: ' + lastVisitHuman +
					'</div> ' 
				});
		
			flightPaths.push(volunteerMarker);

			google.maps.event.addListener(volunteerMarker, 'click', function(event) {
			// get the click's latlng and use that as anchor for infoWindow
				var marker = new google.maps.Marker({
					position: event.latLng
					}); 
				
			// set the info popup content as the html from polyline above, then open it
				infoWindow.setContent(this.html);
				infoWindow.open(map, marker);
				
			// setup the flying radius
			var circleOptions = {
				strokeColor: 'blue',
				strokeOpacity: 0.5, 
				fillColor: 'green',
				fillOpacity: 0.2,
				map: map,
				center: event.latLng,
				radius: this.radius
			} ;			
				
			var flyingCircle = new google.maps.Circle(circleOptions);				

			google.maps.event.addListener(map, 'click', function(event) {
				flyingCircle.setMap(null) ; 
				infoWindow.close(map, marker);
				} );
			google.maps.event.addListener(flyingCircle, 'click', function(event) {
				flyingCircle.setMap(null) ; 
				infoWindow.close(map, marker);
				} );


			} ) ;


		// not using hover to see infoWindows at this point
		//	google.maps.event.addListener(volunteerMarker, 'mouseover', function() {
		//		var marker = new google.maps.Marker({
		//			position: event.latLng
		//			}); 
		//		infoWindow.setContent(this.html);
		//		infoWindow.open(map, this);
		//		});
		//		
		//	google.maps.event.addListener(volunteerMarker, 'mouseout', function() {
		//		infoWindow.close();
		//		});

			volunteerMarker.setMap(map);	
			}
		});
	}



	function downloadUrl(url, callback) {
		var request = window.ActiveXObject ?
		new ActiveXObject('Microsoft.XMLHTTP') :
		new XMLHttpRequest;

		request.onreadystatechange = function() {
		if (request.readyState == 4) {
			request.onreadystatechange = doNothing;
			callback(request, request.status);
			}
		}

		request.open('GET', url, true);
		request.send(null);
	}

// got this from http://stackoverflow.com/questions/9058911/cant-remove-mvcarray-polylines-using-google-maps-api-v3
function removeFlightPaths() {
           for (var i=0; i < flightPaths.length; i++) {
                flightPaths[i].setMap(null);
            }

            // you probably then want to empty out your array as well
            flightPaths = [];

            // not sure you'll require this at this point, but if you want to also clear out your array of coordinates...
            //routePoints.clear();
    }

	function doNothing() {}
	
	google.maps.event.addDomListener(window, 'load', initialize);

    //]]>

  </script>

  </head>

  <body>

	<style>
	html, body {
		margin:0;
		padding:0;
		height:100%; /* needed for container min-height */
		}	
		
	#legend {
			background: white;
			padding: 5px;
			border-style: solid;
			border-color: black;
			border-width:2px;	
		}

	#optionsBox {
			background: white;
			padding: 10px;
		}	
		
	#clusterNotif {
			background: white;
			padding: 10px;
			border-style: solid;
			border-color: black;
			border-width:2px;	
		}		
	</style>


	<div id="clusterNotif">
	<table>
		<tr valign="bottom" align="center">
			<td >
				<A href="maps_volunteers.php">Take me to the clustered (new) map.</A>
			</td>
		</tr>
	</table>
	</div>

	<div id="legend">
	<div style="margin-bottom:5px;font-weight:500;">Legend:</div>
	<table>
		<tr valign="bottom" align="center">
			<td >
				<img src="images/icon_plane_house_small.svg" >
				<div style="padding-left:5px;"> Foster/Pilot</div>
			</td>
			<td>
				<img src="images/icon_house_small.svg" >
				<div style="padding-left:5px;"> Foster</div>
			</td>
			<td>
				<img src="images/icon_plane_blue_small.svg" >
				<div style="padding-left:5px;"> Pilot</div>
			</td>
			<td>
				<img src="images/icon_volunteer.svg" >
				<div style="padding-left:5px;"> Volunteer</div>
			</td>
		</tr>
	</table>
	</div>


	<div id="optionsBox">
		<div>Show: 
		<input type="radio" name="typesToShow" value="all" checked>Everyone
		<input type="radio" name="typesToShow" value="pilot">Pilots
		<input type="radio" name="typesToShow" value="foster">Fosters
		<input type="radio" name="typesToShow" value="both" >Both foster and fly
		<input type="radio" name="typesToShow" value="volunteer">Non-foster or flying volunteers
		<br></div>
		Only show volunteers who have last visited the forum within...
		<select id="lastVisitAge">
			<option value="7" >a week</option>
			<option value="30" >a month</option>
			<option value="90" selected="selected">3 months</option>
			<option value="180">6 months</option>
			<option value="365">1 year</option>
			<option value="3650">Show me all volunteers</option>
		</select>
		
		<input type="button" onclick="updateVolunteers()" value="Search"/>
	</div>

    <div id="gMap" style="width: 100%; height: 100%;"></div>
    
  
  
  </body>

</html>
