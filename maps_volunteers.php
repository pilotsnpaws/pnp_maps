<!DOCTYPE html >
  <head>
	<link rel="shortcut icon" href="http://www.pilotsnpaws.org/forum/favicon.ico" />
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <title>Pilotsnpaws.org Volunteer Location Map</title>
    <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyD7Dabm2M9XvDVk27xCZomEZ1uJFcJHG4k"></script>
    <script src="markerclusterer.js" type="text/javascript"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
    <script src="js/clipboard.min.js" type="text/javascript"></script>

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

	// support testing visible counter
	var inboundsCounter = 0;
	var inboundsUsers = [];

	
	function initialize() {
		console.log('initialize-start')
		var mapOptions = {
			zoom: 5,
		    zoomControl: true,
		    zoomControlOptions: {
		        position: google.maps.ControlPosition.RIGHT_BOTTOM
		    },
		    streetViewControl: false,
			center: new google.maps.LatLng(37.000000,-95.000000),
			scaleControl: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
		    mapTypeControl: true,
    		mapTypeControlOptions: {
        		style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
        		position: google.maps.ControlPosition.TOP_CENTER
    			}
		  };
		map = new google.maps.Map(document.getElementById('gMap'),mapOptions);

		mc = new MarkerClusterer(map);

		updateVolunteers();

		// add options box
		map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('optionsBox'));

		// add legend table
		map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(document.getElementById('legend'));

		// add data table of displayed volunteers table
		map.controls[google.maps.ControlPosition.TOP_RIGHT].push(document.getElementById('mappedVolunteers'));

		// 2016-03-10
		// listener to fire after a zoom or move event - we need to figure out what markers are displayed on the current view
		google.maps.event.addListener(map, 'idle', function() {
			console.log('map-idle')
			updateMappedVolunteers();

			});

		console.log('initialize-end')
	} // end initialize

	function updateMappedVolunteers() {
			// reset the counter 0 that are inbounds
			console.log('updateMappedVolunteers-start')
			inboundsCounter = 0;
			inboundsUsers = [];
			$('.phpbbUsers').remove();
			$('.usernamesForCopy').remove();
	    	console.log('flightPaths.length: ' + flightPaths.length);

			for(var i = 0; i < flightPaths.length; i++) {
			   if( map.getBounds().contains(flightPaths[i].getPosition()) ){
			    	inboundsCounter = inboundsCounter + 1;
					//console.log('inboundsCounter: ' + inboundsCounter);
					inboundsUsers.push(flightPaths[i].inboundHtml);
					// console.log(flightPaths[i].airportLink)
					// console.log('inboundsUsers.length: ' + inboundsUsers.length);
					// add to Mapped Volunteers div

					$('<div class=phpbbUsers>' + flightPaths[i].airportLink + ' ' + flightPaths[i].inboundHtml + '</div>').appendTo('#mappedVolunteers');
					$('<div class=usernamesForCopy >' + flightPaths[i].usernameForCopy + '</div>').appendTo('#hiddenUsernames');

			    }
			}
			console.log('updateMappedVolunteers-end')
		} // end updateMappedVolunteers

	function get_checked_radio(radios) {
	    for (var i = 0; i < radios.length; i++) {
		var current = radios[i];
		if (current.checked) {
		    return current;
		}
	    }
	} // end get_checked_radio

	function updateVolunteers() {

		console.log('updateVolunteers-start')
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
						'</div> ' ,
					inboundHtml: '<a href=/forum/memberlist.php?mode=viewprofile&u=' + userID +
						' target="_blank" >' + username + '</a>' ,
					airportLink: '<a href="http://www.aopa.org/airports/' + airportID + '" target="_blank" >' + airportID + '</a>',
					usernameForCopy: username + '<br>'
					});  // end volunteerMarker


				flightPaths.push(volunteerMarker);
				// console.log('volunteerMarker pushed')
				if( map.getBounds().contains(volunteerMarker.getPosition()) ){
					    	inboundsCounter = inboundsCounter + 1;
					    	// console.log('inboundsCounter: ' + inboundsCounter);
					    	// console.log('flightPaths.length: ' + flightPaths.length);
					    }

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

			//  cluster the markers
			var mcOptions = {
				gridSize: 50, 
				maxZoom: 9
				};
			mc = new MarkerClusterer(map, flightPaths, mcOptions);

			updateMappedVolunteers();

			}); // end downloadUrl

		console.log('updateVolunteers-end');

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
  console.log('deleteMarkers-start');
  setAllMap(null);
  flightPaths = [];
  mc.clearMarkers();
  console.log('deleteMarkers-end');
}

  // Sets the map on all markers in the array.
function setAllMap(map) {
  for (var i = 0; i < flightPaths.length; i++) {
	flightPaths[i].setMap(null);
  }
}

	google.maps.event.addDomListener(window, 'load', initialize);

	console.log('inboundsCounter: ' + inboundsCounter);


  </script>

  </head>

  <body >

	<style>
	html, body {
		margin:0;
		padding:0;
		height:100%; /* needed for container min-height */
		}

	#optionsBox {
			background: white;
			padding: 10px;
			border-style: solid;
			border-color: black;
			border-width:2px;
		}

	#legend {
			background: white;
			padding: 10px;
			border-style: solid;
			border-color: black;
			border-width:2px;
		}

	#mappedVolunteers {
			background: white;
			padding: 10px;
			border-style: solid;
			border-color: black;
			border-width:2px;
		}

	#hiddenUsernames {
			width: 0px;
			height: 0px;
	}

	</style>

	<div id="hiddenUsernames" >
	</div>

	<div id="mappedVolunteers"  >
			<div style="margin-bottom:5px;font-weight:500;">Mapped volunteers:</div>


			<script>
				var clipboard = new Clipboard('.btn');

				clipboard.on('success', function(e) {
				    console.log(e);
				});

				clipboard.on('error', function(e) {
				    console.log(e);
				});
			</script>

			<div></div>
			<button class="btn" data-clipboard-action="copy" data-clipboard-target="#hiddenUsernames">Copy usernames to clipboard</button>

	</div>

	<div id="legend">
	<table>
		<tr valign="bottom" align="center">
			<td align="left">
				<A href="/maps_more/mapsmore_volunteersAirport.php">Search pilots by airport code</A><br>
				<A href="/maps_more/mapsmore_volunteersZip.php">Search pilots by zip code</A>
			</td>
		</tr>
	</table>
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