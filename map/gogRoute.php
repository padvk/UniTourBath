<!DOCTYPE html >
  <head>
    <title>UniTour Bath</title>
	<meta name = "UTB", content = "initial-scale = 1.0, user-scalable = no"/>
    <style>
      /* Height of map must be set explicitly */
      #map {
        height: 100%;
		width: 100%;
      }
      html, body {
        height: 100%;
      }
    </style>
  </head>

  <body>
    <div id="map"></div>
	
    <script>
		// XML sent to this page will contain the points that should be on this tour
		// Javascript then finds which point is closest to the user's current location
		// begins tour on closest point, then after that, moves to next point
		// all the needed data is loaded from the XML, no need for other interaction with database and php stuff
		
		var customLabel = {
			poi: {
				label: 'P'
			},
			visited: {
			label: 'V'
			}
		};
	  
		var map;
		var currentPOI = 0;
		var initialised = false;
		
		//Initialises map. Centred about bath uni.
		function initMap() {
			map = new google.maps.Map(document.getElementById('map'), {
				center: new google.maps.LatLng(51.379193, -2.327405),
				zoom: 17
			});
			var infoWindow = new google.maps.InfoWindow;
			var latiLong = [];
				
			//URL for XML file
			//Takes information from XML file and adds markers to map
			downloadUrl('createXML.php', function(data) {
				var xml = data.responseXML;
				var pointsOfInterest = xml.documentElement.getElementsByTagName('marker');
				Array.prototype.forEach.call(pointsOfInterest, function(markerElem) {
					var name = markerElem.getAttribute('name');
					var address = markerElem.getAttribute('orderID') + " | " + markerElem.getAttribute('address');
					var type = markerElem.getAttribute('type');
					var point = new google.maps.LatLng(
						parseFloat(markerElem.getAttribute('lat')),
						parseFloat(markerElem.getAttribute('lng'))
					);
						
					//pushing the point (lat and long data) to latiLong - used for the route finding
					latiLong.push(point);
					
					//Info window userd when user clicks on point of interest
					var infowincontent = document.createElement('div');
					var strong = document.createElement('strong');
					strong.textContent = name;
					infowincontent.appendChild(strong);
					infowincontent.appendChild(document.createElement('br'));

					var text = document.createElement('text');
					text.textContent = address
					infowincontent.appendChild(text);
					var icon = customLabel[type] || {};
					var marker = new google.maps.Marker({
						map: map,
						position: point,
						label: icon.label
					});
					marker.addListener('click', function() {
					infoWindow.setContent(infowincontent);
					infoWindow.open(map, marker);
					
					});
				});
				calculateRoute(latiLong);
				getPosition(latiLong);
			});
		}

		//Function to read in XML file
		function downloadUrl(url, callback) {
			var request = window.ActiveXObject ?
				new ActiveXObject('Microsoft.XMLHTTP') :
				new XMLHttpRequest;

			request.onreadystatechange = function() {
			  if (request.readyState == 4) {
				request.onreadystatechange = doNothing;
				callback(request, request.status);
			  }
			};

			request.open('GET', url, true);
			request.send(null);
		}
		  
		  
		//calculate route uses the array latiLong that stores the lat and long of the points. 
		//It loops through each point and draws routes for each one
		function calculateRoute(latiLong) {
			
			//Create Path array
			var path = new google.maps.MVCArray();
			
			//Initialise Google's direction service
			var service = new google.maps.DirectionsService();
			
			//Set path colour
			var poly = new google.maps.Polyline({ strokeColor: '#CC00FF' });
			poly.setMap(map);
			
			//Draw route
			for (var i = 0; i <= latiLong.length; i++) {
				if ((i+1) < latiLong.length) {
					var src = latiLong[i];
					var des = latiLong[i+1];
					poly.setPath(path);
					service.route({
						origin: src,
						destination: des,
						travelMode: google.maps.DirectionsTravelMode.WALKING
					}, function (result, status) {
						if (status == 'OK') {
							for (var i = 0, len = result.routes[0].overview_path.length; i < len; i++) {
								path.push(result.routes[0].overview_path[i]);
							}
						}
					});
				}
			}
		}
		  
		//Function that gets and then tracks the position of the user
		function getPosition(latiLong) {
			var pos;
			if (navigator.geolocation) {
				navigator.geolocation.watchPosition(function(position) {
					pos = {
						lat: position.coords.latitude,
						lng: position.coords.longitude
					};
					
					//Used to remove old markers
					if (typeof(marker) != "undefined") marker.setMap(null);
					marker = new google.maps.Marker({
						position: pos,
						map: map,
						title: "User Position"
					});
					map.setCenter(pos);
					
					// currently findClosestPoint is called in the watchPosition function
					// so it's called everytime your location updates
					// might be what we want?
					findClosestPoint(latiLong, pos.lat, pos.lng);
					
				}, function() {
					handleLocationError(true, infoWindow, map.getCenter());
				});
				
			} else {
				// Browser doesn't support Geolocation
				handleLocationError(false, infoWindow, map.getCenter());
			}
		}
		
		function handleLocationError(browserHasGeolocation, infoWindow, pos) {
			infoWindow.setPosition(pos);
			infoWindow.setContent(browserHasGeolocation ?
								  'Error: Location service failed.' :
								  'Error: Your browser does not support location.');
		}
		
		function findClosestPoint(latiLong, lat, lng) {
			var closest = 0;
			var lowestDistance = 999;
			
			for (i = 0; i < latiLong.length; i++) { // for each POI
				// get distance from current location to point i
				var destLat = latiLong[i].lat();
				var destLng = latiLong[i].lng();
				var distance = Math.sqrt(Math.pow(lng - destLng, 2) + Math.pow(lat - destLat, 2));
				
				if (distance < lowestDistance) {
					closest = i;
					lowestDistance = distance;
				}
			}
			console.log("Your location just updated. Closest arrayIndex: " + closest);
			
			if (!initialised) { // get the initial closest point of interest
				currentPOI = closest;
				initialised = true;
				console.log("Initial closest: " + closest);
			}
			
			if (closest != currentPOI) { // user now closer to the next POI
				// Ask user if they want to view info on the next POI
				console.log("Closer to POI with arrayIndex " + closest + " than POI arrayIndex " + currentPOI);
				currentPOI = closest;
			}
		}
		
		// http://stackoverflow.com/questions/728360/how-do-i-correctly-clone-a-javascript-object
		// clone an object
		function clone(obj) {
			if (null == obj || "object" != typeof obj) {
				console.log("not object");
				return obj;
			}
			
			var copy = obj.constructor();
			for (var attr in obj) {
				if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
			}
			return copy;
		}
		
		function doNothing() {}
	  
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBdds_EpEfCnpojHMvMUvntJj1gCx9moOA&callback=initMap">
    </script>

  </body>
</html>
