<!DOCTYPE html >
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <title>Pilotsnpaws.org trip request map</title>
    <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyD7Dabm2M9XvDVk27xCZomEZ1uJFcJHG4k&sensor=false"></script>
    <script type="text/javascript">
    //<![CDATA[
		
	var map;
	var flightPaths = [];
	var lastPostAge;
	function initialize() {
		var mapOptions = {
			zoom: 4,
			center: new google.maps.LatLng(37.000000,-95.000000),
			scaleControl: true,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		  };
		map = new google.maps.Map(document.getElementById('gMap'),mapOptions);
		updateTrips();
	}

	function updateTrips() {

	removeFlightPaths();

	lastPostAge = document.getElementById("lastPostAge").value;
	// alert(lastPostAge);
	
	var searchURL = "create_trips_xml.php?lastPostAge=" + lastPostAge;
	downloadUrl(searchURL, function(data) {
	var xml = data.responseXML;
	
	var pathInfoWindow = new google.maps.InfoWindow();
		
	var trips = xml.documentElement.getElementsByTagName("trip");
		for (var i = 0; i < trips.length; i++) {
			var topic = trips[i].getAttribute("topicTitle");
			var topicID = trips[i].getAttribute("topicID");
			var sendLat = trips[i].getAttribute("sendLat");
			var sendLon = trips[i].getAttribute("sendLon");
			var recLat = trips[i].getAttribute("recLat");
			var recLon = trips[i].getAttribute("recLon");
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
			
			var flightPath = new google.maps.Polyline({
				path: flightPlanCoordinates,
				strokeColor: directionColor,
				strokeOpacity: 1.0,
				strokeWeight: 2,
				html: '<div style=width:330px;margin:0 0 20px 20px;height:110px;>' + topic + '<BR/>' +  
					'<a href=http://www.pilotsnpaws.org/forum/viewtopic.php?f=5&amp;t=' + topicID +
					' target="_blank" >Topic: ' + topicID + '</a>'			
				});
			
			flightPaths.push(flightPath);

				google.maps.event.addListener(flightPath, 'click', function(event) {
									
				// get the click's latlng and use that as anchor for infoWindow
				// found here: http://stackoverflow.com/questions/9998003/calling-infowindow-w-google-map-v3-api
					var marker = new google.maps.Marker({
						position: event.latLng
						}); 

				// set the info popup content as the html from polyline above
					pathInfoWindow.setContent(this.html);
				// we dont need to define setPosition anymore, as we're making the infoWindow with the click's marker location now
					pathInfoWindow.open(map, marker);
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

  </head>

  <body onload="load()">

	<div>
		Filter by recent activity:
		<select id="lastPostAge">
			<option value="0" selected>Today only</option>
			<option value="3" selected="selected">In the last 3 days</option>
			<option value="5">In the last 5 days</option>
			<option value="7">In the last 7 days</option>
			<option value="14">In the last 14 days</option>
			<option value="30">In the last 30 days</option>
			<option value="365">Last year</option>
		</select>
		
		<input type="button" onclick="updateTrips()" value="Search"/>
		<input type="button" onclick="removeFlightPaths()" value="Clear flightPaths"/>
	</div>

    <div id="gMap" style="width:80%;height:600px;"></div>
  </body>

</html>
