<!DOCTYPE html >
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <title>Pilotsnpaws.org Volunteer Location Map</title>
    <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyD7Dabm2M9XvDVk27xCZomEZ1uJFcJHG4k&sensor=false"></script>
    <script src="markerclusterer.js" type="text/javascript"></script>
    <script type="text/javascript">
    //<![CDATA[
		
	var map;
	var mcOptions;
	var flightPaths = [];
	var lastVisitAge;
	var typeToShow;
	var zipCode = "00000";
	var distance;

	var min = .999965;
	var max = 1.000035;
	
	function initialize() {
		var mapOptions = {
			zoom: 5,
			center: new google.maps.LatLng(37.000000,-95.000000),
			scaleControl: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		  };
		map = new google.maps.Map(document.getElementById('gMap'),mapOptions);

		mc = new MarkerClusterer(map);

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
	
	lastVisitAge = document.getElementById("lastVisitAge").value;
	typeToShow = get_checked_radio(document.getElementsByName("typesToShow")).value;
	zipCode = document.getElementById("zipCode").value;
	distance = document.getElementById("distance").value;
	
	var searchURL = "maps_create_volunteer_locations_xml.php?lastVisitAge=" + lastVisitAge + "&typesToShow=" + typeToShow + "&zipCode=" + zipCode + "&distance=" + distance ;
	//alert(searchURL);
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
			var lat = volunteers[i].getAttribute("lat") * (Math.random() * (max - min) + min);
			var lon = volunteers[i].getAttribute("lon") * (Math.random() * (max - min) + min);
			var city = volunteers[i].getAttribute("city");
			var state = volunteers[i].getAttribute("state");
			var volunteerCoordinates = new google.maps.LatLng(lat,lon);

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
					'Username: <a href=/forum/memberlist.php?mode=viewprofile&u=' + userID +
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
						position: event.latLng,
						map: map
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

			} // end of for

		//  testing cluster
		var mcOptions = {
			gridSize: 50, 
			maxZoom: 9};
		mc = new MarkerClusterer(map, flightPaths, mcOptions);

		});  
	}  // end updateVolunteers

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

	function doNothing() {}

// Deletes all markers in the array by removing references to them.
function deleteMarkers() {
  //alert(mc);
  setAllMap(null);
  flightPaths = [];
  mc.clearMarkers();
}

  // Sets the map on all markers in the array.
function setAllMap(map) {
  for (var i = 0; i < flightPaths.length; i++) {
	flightPaths[i].setMap(null);
  }
}

	google.maps.event.addDomListener(window, 'load', initialize);

    //]]>

  </script>

  </head>

  <body >

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
			border-style: solid;
			border-color: black;
			border-width:2px;	
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
				<A href="maps_volunteers_noncluster.php">Take me back to the old (non-clustered) map.</A>
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
		<br>
		Search for pilots: <input id="zipCode" type='text' maxlength="5" size="7" placeholder="Zip Code"/>  Radius to show:  
			<select id="distance">
				<option selected="selected"> </option>
				<option value="10" >10 miles</option>
				<option value="30" >30 miles</option>
				<option value="50" >50 miles</option>
				<option value="100">100 miles</option>
				<option value="150">150 miles</option>
				<option value="200">200 miles</option>
			</select>
		
		
		<input type="button" onclick="deleteMarkers();updateVolunteers()" value="Search"/>
	</div>

    <div id="gMap" style="width: 100%; height: 100%;"></div>
  </body>

</html>
