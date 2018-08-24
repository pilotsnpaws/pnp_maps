<!DOCTYPE html >
  <head> 
	<link rel="shortcut icon" href="/favicon.ico" />
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
	<title>Pilots-N-Paws single trip map</title>

	<script src="js/jquery.min.js" type="text/javascript"></script>
	<script src="js/clipboard.min.js" type="text/javascript"></script>
	<script type="text/javascript">

    //<![CDATA[
		
	var map;
	var flightPaths = [];
	var volunteerMarkers = [];
	var topic;
	var min = .999965;
	var max = 1.000035;	
	var sendZip = '00000';
	var recZip = '00000' ;
  var defaultTopic = '41217' // this is in case one isn't provided, we'll show an old one

  // get URL parameter 
	function gup( name ){
		name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");  
		var regexS = "[\\?&]"+name+"=([^&#]*)";  
		var regex = new RegExp( regexS );  
		var results = regex.exec( window.location.href ); 
		 if( results == null )    return "";  
		else    return results[1];}

	function initialize() {
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
        		position: google.maps.ControlPosition.BOTTOM_CENTER
    			}
		  };
		map = new google.maps.Map(document.getElementById('gMap'),mapOptions);
		updateTrips();

		// add data table of displayed volunteers table
		map.controls[google.maps.ControlPosition.TOP_RIGHT].push(document.getElementById('mappedVolunteers'));
		
		// add trip details table
		map.controls[google.maps.ControlPosition.TOP_RIGHT].push(document.getElementById('details'));

		// add options box
		map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('optionsBox'));

		// add legend table
		map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(document.getElementById('legend'));

		// new 2016-05-18
		// listener to fire after a zoom or move event - we need to figure out what markers are displayed on the current view
		google.maps.event.addListener(map, 'idle', function() {
			// console.log('map-idle')
			updateMappedVolunteers();

			});
		// end new 2016-05-18


		} // end initialize

	function updateMappedVolunteers() {
			// reset the counter 0 that are inbounds
			console.log('updateMappedVolunteers-start')
			inboundsCounter = 0;
			inboundsUsers = [];
			$('.phpbbUsers').remove();
			$('.usernamesForCopy').remove();
	    	console.log('volunteerMarkers.length: ' + volunteerMarkers.length);

			for(var i = 0; i < volunteerMarkers.length; i++) {
			   if( map.getBounds().contains(volunteerMarkers[i].getPosition()) ){
			    	inboundsCounter = inboundsCounter + 1;
					//console.log('inboundsCounter: ' + inboundsCounter);
					inboundsUsers.push(volunteerMarkers[i].inboundHtml);
					// console.log(flightPaths[i].airportLink)
					// console.log('inboundsUsers.length: ' + inboundsUsers.length);
					// add to Mapped Volunteers div

					$('<div class=phpbbUsers>' + volunteerMarkers[i].airportLink + ' ' + volunteerMarkers[i].inboundHtml + '</div>').appendTo('#mappedVolunteers');
					$('<div class=usernamesForCopy >' + volunteerMarkers[i].usernameForCopy + '</div>').appendTo('#hiddenUsernames');

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
  
	function updateTrips() {

		removeFlightPaths();

		//topic = document.getElementById("topic").value;
		// alert(topic);
		
		var topic_param = gup('topic');
		// if topic isn't provided, use an old trip as not to fail
		if (topic_param.length == 0)
      {
        console.log('No topic provided in URL. Showing old trip as default.');
        topic_param = defaultTopic;
      }
    
		var searchURL = "maps_create_single_trip_xml.php?topic=" + topic_param;
		downloadUrl(searchURL, function(data) {
		var xml = data.responseXML;
		
		var pathInfoWindow = new google.maps.InfoWindow();
			
		var trips = xml.documentElement.getElementsByTagName("trip");
    // check if query returned anything
      if (trips.length == 0)
        {
          console.log('xml returned 0 results. expecting single trip detail. nothing to display ');
        }
      
		// radians to degrees
		function toDeg(rad) {
			return rad * 180 / Math.PI;
		}

		// degrees to radians
		function toRad(deg) {
			return deg * Math.PI / 180;
		}

		// calulate the flight direction
		function getBearing(lat1, lon1, lat2, lon2) {
			var d = toRad((lon2 - lon1));
			var y = Math.sin(d) * Math.cos(toRad(lat2));
			var x = Math.cos(toRad(lat1)) * Math.sin(toRad(lat2)) - Math.sin(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.cos(d);
			var brng = toDeg(Math.atan2(y, x));
			return (360 + brng) % 360;
		}

			for (var i = 0; i < trips.length; i++) {
				var topic = trips[i].getAttribute("topicTitle");
				var topicID = trips[i].getAttribute("topicID");
				var lastPost = trips[i].getAttribute("lastPost");
				var lastPostHuman = trips[i].getAttribute("lastPostHuman");
				var sendLat = trips[i].getAttribute("sendLat");
				var sendLon = trips[i].getAttribute("sendLon");
				var recLat = trips[i].getAttribute("recLat");
				var recLon = trips[i].getAttribute("recLon");
				sendZip = trips[i].getAttribute("sendZip");
				recZip = trips[i].getAttribute("recZip");
				var sendCity = trips[i].getAttribute("sendCity");
				var recCity = trips[i].getAttribute("recCity");
				var flightPlanCoordinates = [
					new google.maps.LatLng(sendLat, sendLon),
					new google.maps.LatLng(recLat, recLon),
					];

				// determine color for flight direction
				var bearing = getBearing(sendLat, sendLon, recLat, recLon);
				if (bearing >= 0 && bearing < 180)
					// 0-179 deg (Eastbound)
					{ var directionColor = '#00AD6E'; // greenish
					}
				else
					// 180-359 deg (Westbound)
					{ var directionColor = '#8D00DE'; // purple
					}

				// show direction of flight
				var lineType = { path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW };
				
				var flightPath = new google.maps.Polyline({
					path: flightPlanCoordinates,
					strokeColor: directionColor,
					icons: [{ icon: lineType, offset: '100%' }],
					strokeOpacity: 1.0,
					strokeWeight: 2,
					geodesic : true,
					});

				flightPaths.push(flightPath);

				// from http://stackoverflow.com/questions/16642451/center-and-auto-zoom-google-map
				//  Make an array of the LatLng's of the markers you want to show
				var LatLngList = new Array (new google.maps.LatLng(sendLat, sendLon), new google.maps.LatLng(recLat, recLon));
				//  Create a new viewpoint bound
				var bounds = new google.maps.LatLngBounds ();
				//  Go through each...
				for (var i = 0, LtLgLen = LatLngList.length; i < LtLgLen; i++) {
				  //  And increase the bounds to take this point
				  bounds.extend (LatLngList[i]);
					}
				//  Fit these bounds to the map
				map.fitBounds(bounds);

				// do length calcs, make a dummy path to get length.. if not, cant calc length in the html string below
				var lengthFlightPath = new google.maps.Polyline({
					path: flightPlanCoordinates
					});
				var lengthMeters = google.maps.geometry.spherical.computeLength(lengthFlightPath.getPath())  ;
				var lengthMiles = Math.round(lengthMeters / 1609.344);
				var lengthNM = Math.round(lengthMeters / 1852);

				var strHTML = '<a href=/forum/viewtopic.php?t=' + topicID +
						' target="_blank" >' + topic + '</a><br>' + 
						'From ' + sendCity + ' to ' + recCity + '<br>' + 
						'Distance: ' + lengthMiles + ' miles / ' + lengthNM  + ' nm' + '<br>' + 
						'Topic last updated: ' + lastPostHuman

				// update the html window with trip details
				document.getElementById("tripHTML").innerHTML = strHTML;
				// document.getElementById("zipCode").value = sendZip;

					google.maps.event.addListener(flightPath, 'click', function(event) {
										
					// get the click's latlng and use that as anchor for infoWindow
					// found here: http://stackoverflow.com/questions/9998003/calling-infowindow-w-google-map-v3-api
						var marker = new google.maps.Marker({
							position: event.latLng,
							map: map
							}); 

					// set the info popup content as the html from polyline above
						pathInfoWindow.setContent(this.html);
						pathInfoWindow.open(map, marker);

						google.maps.event.addListener(map, 'click', function(event) {
							pathInfoWindow.close(map, marker);
						} );

					});

          flightPath.setMap(map);
          // not showing volunteers at request of folks that it makes it cluttered. have to click search
          // updateVolunteers();
				}
			});
	} // end updateTrips
		
	function updateVolunteers() {
	
		removeVolunteers();
	
		// get volunteer data
		lastVisitAge = document.getElementById("lastVisitAge").value;
		typeToShow = get_checked_radio(document.getElementsByName("typesToShow")).value;
		//zipCode = sendZip; // document.getElementById("zipCode").value;
		distance = document.getElementById("distance").value;
		
		var volSearchURL = "maps_create_volunteer_locations_xml.php?lastVisitAge=" + lastVisitAge + "&typesToShow=" + typeToShow + "&zipCode=" + sendZip + ',' + recZip + "&distance=" + distance ;
		
		downloadUrl(volSearchURL, function(data) {
			var xml = data.responseXML

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

				volunteerMarkers.push(volunteerMarker);
				
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
				volunteerMarker.setMap(map);	

				} // end of for

				updateMappedVolunteers();

			});  	 // end downloadUrl

		// here is where adding to the downloadUrl needs to go

		var latLonSearchURL = "maps_create_volunteer_locations_latlon_xml.php?lastVisitAge=" + lastVisitAge + "&typesToShow=" + typeToShow + "&zipCode=" + sendZip + ',' + recZip + "&distance=" + distance ;

		downloadUrl(latLonSearchURL, function(data) {
			var xml = data.responseXML

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

				volunteerMarkers.push(volunteerMarker);
				
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
				volunteerMarker.setMap(map);	

				} // end of for
				
			});  	 // end downloadUrl for lat lon

		//  end downloadLatLon
	
		updateMappedVolunteers();

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
	}  // end downloadUrl

// got this from http://stackoverflow.com/questions/9058911/cant-remove-mvcarray-polylines-using-google-maps-api-v3
function removeFlightPaths() {
           for (var i=0; i < flightPaths.length; i++) {
                flightPaths[i].setMap(null);
            }

            // you probably then want to empty out your array as well
            flightPaths = [];

            // not sure you'll require this at this point, but if you want to also clear out your array of coordinates...
            //routePoints.clear();
    } // end removeFlightPaths
    
    function removeVolunteers() {
           for (var i=0; i < volunteerMarkers.length; i++) {
                volunteerMarkers[i].setMap(null);
            }

            // you probably then want to empty out your array as well
            volunteerMarkers = [];
    } // end removeVoluneers

	function doNothing() {}
	
	//google.maps.event.addDomListener(window, 'load', initialize);

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

  #gMap {
      height: 100%;
    }
    
	#legend {
			background: white;
			padding: 10px;
			border-style: solid;
			border-color: black;
			border-width:2px;
		}
		
	#close {
			float:right;
			display:inline-block;
			padding:2px 5px;
			background:#ccc;
		}

	#details {
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
			font-size: 1%;
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

	<div id="details">
		<div style="font-weight:500;font-size:125%">Trip request details:</div>
		<div style="font-size:105%" id="tripHTML"></div>
	</div>

	<script>
			window.onload = function(){
				document.getElementById('close').onclick = function(){
						this.parentNode.parentNode
						.removeChild(this.parentNode);
						return false;
				};
			};	
	</script>
		
	<div id="legend">
		<span id='close'>X</span>
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
	<table width="100%"> 
		<tr valign="bottom" align="center">
			<td align="left">
				<A href="/maps_more/mapsmore_volunteersAirport.php">Search pilots by airport code</A><br>
				<A href="/maps_more/mapsmore_volunteersZip.php">Search pilots by zip code</A>
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
		Radius to show:  
			<select id="distance">
				<option > </option>
				<option value="10" >10 miles</option>
				<option value="30" >30 miles</option>
				<option selected="selected" value="50" >50 miles</option>
				<option value="100">100 miles</option>
				<option value="150">150 miles</option>
				<option value="200">200 miles</option>
			</select>
		
		
		<input type="button" onclick="updateVolunteers()" value="Search"/>
	</div>
	
<!--	<div id="beta" >
		<div style="margin-bottom:5px;font-weight:500;">This map is still under development!</div>
		<div> Please submit your feedback <a href="/forum/viewtopic.php?f=17&t=26362" target="_blank" >here</a> </div>
		<div>Note: This map currently only shows volunteers within the <br> distance around the starting and ending points of the request.   <br>You can expand those circles using the "Radius to show" setting above.</div>
	</div>	
-->

    <div id="gMap"></div>
    
  </body>

</html>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC58B4aQpLjLXUuNGonLJV9G0tP3BDMZJ4&libraries=geometry&callback=initialize">
</script>

	<!-- Google analytics added 2016-03-29 --> 
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-62646402-1', 'auto');
	  ga('send', 'pageview');

	</script>
	<!-- End Google analytics added 2016-03-29 -->
