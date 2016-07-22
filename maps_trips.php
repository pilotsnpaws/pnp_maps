<!DOCTYPE html >
<head>
	<link rel="shortcut icon" href="/favicon.ico" />
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
	<title>Pilotsnpaws.org trip request map</title>
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD7Dabm2M9XvDVk27xCZomEZ1uJFcJHG4k&v=3&libraries=geometry"></script>
	<script type="text/javascript">
    //<![CDATA[
		
	var map;
	var flightPaths = [];
	var lastPostAge;
	function initialize() {
		var mapOptions = {
			zoom: 5,
			center: new google.maps.LatLng(38.000000,-98.000000),
			scaleControl: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		  };
		map = new google.maps.Map(document.getElementById('gMap'),mapOptions);
		updateTrips();

		// add options box
		map.controls[google.maps.ControlPosition.TOP_LEFT].push(document.getElementById('optionsBox'));


		// add legend table
		map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(document.getElementById('legend'));

	}

	function updateTrips() {

	removeFlightPaths();

	lastPostAge = document.getElementById("lastPostAge").value;
	// alert(lastPostAge);
	
	var searchURL = "maps_create_trips_xml.php?lastPostAge=" + lastPostAge;
	downloadUrl(searchURL, function(data) {
	var xml = data.responseXML;
	
	var pathInfoWindow = new google.maps.InfoWindow();
		
	var trips = xml.documentElement.getElementsByTagName("trip");
		for (var i = 0; i < trips.length; i++) {
			var topic = trips[i].getAttribute("topicTitle");
			var topicID = trips[i].getAttribute("topicID");
			var lastPost = trips[i].getAttribute("lastPost");
			var lastPostHuman = trips[i].getAttribute("lastPostHuman");
			var sendLat = trips[i].getAttribute("sendLat");
			var sendLon = trips[i].getAttribute("sendLon");
			var recLat = trips[i].getAttribute("recLat");
			var recLon = trips[i].getAttribute("recLon");
			var sendZip = trips[i].getAttribute("sendZip");
			var recZip = trips[i].getAttribute("recZip");
			var sendCity = trips[i].getAttribute("sendCity");
			var recCity = trips[i].getAttribute("recCity");			
			var flightPlanCoordinates = [
				new google.maps.LatLng(sendLat, sendLon),
				new google.maps.LatLng(recLat, recLon),
				];
			if (sendLat < recLat) 
					// south to north trip
					{ var directionColor = '#8D00DE' ; // purple
					}
				else  // north to south trip
					{ var directionColor = '#00AD6E'; // greenish
					}

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

			var flightPath = new google.maps.Polyline({
				path: flightPlanCoordinates,
				strokeColor: directionColor,
				strokeOpacity: 1.0,
				strokeWeight: 3,
				html: strHTML
				});

			flightPaths.push(flightPath);

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
			padding: 10px;
			width: 100px;;
		}

	#optionsBox {
			background: white;
			padding: 10px;
		}		
		
	</style>

	<div id="optionsBox">
		Filter by recent activity:
		<select id="lastPostAge">
			<option value="0" selected>Today only</option>
			<option value="3" selected="selected">In the last 3 days</option>
			<option value="5">In the last 5 days</option>
			<option value="7">In the last 7 days</option>
			<option value="14">In the last 14 days</option>
			<option value="30">In the last 30 days</option>
			<option value="365">Last 12 months</option>
		</select>
		
		<input type="button" onclick="updateTrips()" value="Search"/>
	</div>
	
	<div id="legend">
		<div style="margin-bottom:5px;font-weight:500;">Legend:</div>
		<div style="float:left;width:30px;height:1em;background-color:#8D00DE;border: 1px solid black;;"></div>
		<div style="float:left;padding-left:5px;"> Northbound</div>
		<div style="float:left;width:30px;height:1em;background-color:#00AD6E;border: 1px solid black;"></div>
		<div style="float:left;padding-left:5px;"> Southbound</div>
	</div>

    <div id="gMap" style="width: 100%; height: 100%;"></div>
  </body>

</html>
