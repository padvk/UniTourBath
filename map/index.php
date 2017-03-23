<!DOCTYPE html>
<html>

	<head>
		<title>UniTour Bath</title>
		<link rel="icon" href="unitourlogo.PNG">
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

        <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,700' rel='stylesheet' type='text/css'>

        <link rel="stylesheet" type="text/css" href="../main.css">

    </head>

	<body>
        <header class = "container">
            <div class = "row">
                <h1 class = "col-sm-4">
                    <a href = "../home/" style = "text-decoration: none;">
                        <img src="back.png" style="width:40px;height:40px;"/>
                    </a>
                    UniTour Bath
                </h1>
                <div class = "col-sm-8 text-right">
                    <img src="../unitourlogo.PNG" style="width:50px;height:55px;"/>
                </div>
            </div>
        </header>

        <div id="map"></div>

	<?php
		// get the time and dep from URL arguments
		// URL will look like https://people.bath.ac.uk/ph471/UTB/map/gogRoute.php?t=30&dep=CS
		$time = $_GET['t'];
		$dep = $_GET['dep'];
	?>
    <script>
		// XML sent to this page will contain the points that should be on this tour
		// Javascript then finds which point is closest to the user's current location
		// begins tour on closest point, then after that, moves to next point
		// all the needed data is loaded from the XML, no need for other interaction with database and php stuff
		var time = <?php echo $time ?>;
		var dep = "<?php echo $dep ?>";

		var customLabel = {
			poi: {
				label: 'P'
			},
			visited: {
			label: 'V'
			}
		};

		var map;
		var initialPOI = 0;
		var currentPOI = 0;
		var initialised = false;
		var audio = new Audio('notification.mp3');

		//Initialises map. Centred about bath uni.
		function initMap() {
			map = new google.maps.Map(document.getElementById('map'), {
				center: new google.maps.LatLng(51.379193, -2.327405),
				zoom: 17
			});
			var infoWindow = new google.maps.InfoWindow;
			var markers = [];
			var pointInfo = [];

			//URL for XML file
			//Takes information from XML file and adds markers to map
			downloadUrl('createXML.php?t=' + time + '&dep=' + dep, function(data) {
				var xml = data.responseXML;
				var pointsOfInterest = xml.documentElement.getElementsByTagName('marker');
				Array.prototype.forEach.call(pointsOfInterest, function(markerElem) {
					var name = markerElem.getAttribute('name');
					var address = markerElem.getAttribute('address');
					var type = markerElem.getAttribute('type');
					var point = new google.maps.LatLng(
						parseFloat(markerElem.getAttribute('lat')),
						parseFloat(markerElem.getAttribute('lng'))
					);

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
					marker.setLabel('');
					marker.setIcon('markers/togo.png');

					// markers is an array containing objects.
					// those objects have properties 'point' (which stores a gmaps marker object) and 'name' (which stores the marker name)
					markers.push({point: marker, name: name});
				});
				getPosition(markers, infoWindow);
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

		//Function that gets and then tracks the position of the user
		function getPosition(markers, infoWindow) {
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
					marker.setIcon('markers/you.png');
					map.setCenter(pos);

					findClosestPoint(markers, pos.lat, pos.lng);

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

		function findClosestPoint(markers, lat, lng) { // called when position is updated
			var closest = 0;
			var lowestDistance = 999;
			var radius = 0.0003;

			for (i = 0; i < markers.length; i++) { // for each POI
				// get distance from current location to point i
				var destLat = markers[i].point.getPosition().lat();
				var destLng = markers[i].point.getPosition().lng();
				var distance = Math.sqrt(Math.pow(lng - destLng, 2) + Math.pow(lat - destLat, 2));

				if (distance < lowestDistance) {
					closest = i;
					lowestDistance = distance;
				}
			}
			var closestName = markers[closest].name;

			if (!initialised) { // get the initial closest point of interest
				if (lowestDistance < radius) {
					initialise(markers, closest);
				} else {
					markers[closest].point.setIcon('markers/next.png');
					document.getElementById("buttonText").innerHTML = "Reach the star to begin tour.";
				}
			}

			if (closest != currentPOI && lowestDistance < radius) { // user now closer to another POI
				if (closest == (currentPOI+1)%markers.length) { // new closest POI is the right one
					// update markers
					markers[currentPOI].point.setIcon('markers/previous.png');
					markers[closest].point.setIcon('markers/current.png');

          // play notification sound
          audio.play();

					document.getElementById("buttonText").innerHTML = "This Stop: " + closestName;
					currentPOI = closest;
					if (currentPOI == initialPOI) { // tour has looped, user is back at the beginning

						// END OF TOUR - take user to final screen where they can see info for all POIs (?)
						document.getElementById("buttonText").innerHTML = "Tour finished.";

					} else { // tour's not over
						// update next marker
						markers[(closest+1)%markers.length].point.setIcon('markers/next.png');
					}
				} else { // new closest POI is the wrong one
					document.getElementById("buttonText").innerHTML = "Wrong stop, go to the star.";
				}
			}
		}

		// initialise variables
		function initialise(markers, closest) {
			var name = markers[closest].name;
			var next = (closest+1)%markers.length;
			markers[closest].point.setIcon('markers/current.png');
			markers[next].point.setIcon('markers/next.png');
			currentPOI = initialPOI = closest;
			initialised = true;
			document.getElementById("buttonText").innerHTML = "This Stop: " + name;
			console.log("Initial closest: " + name);
		}

		// http://stackoverflow.com/questions/728360/how-do-i-correctly-clone-a-javascript-object
		// clone an object (not actually used rn but might come in handy)
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







        <section class="container">
            <?php
                require_once "DB.php";
                include "../globals.php";
                $dsn="mysql://$user:$password@$host/$database";
                $db = DB::connect($dsn);
                if (DB::isError($db)) {
                    die ($db->getMessage());
                }
                $query = <<<ENDOFSTRING
                SELECT * FROM utbpoi;
ENDOFSTRING;
                $result = $db->query($query);
                $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
            ?>
            <div class="row" >
                <figure class="col-sm-6">
                    <p href="../information" class="quote" id="buttonText">

                        This Stop:

                    </p>
                </figure>
            </div>
            <?php
                $result->free();
                $db->disconnect();
            ?>
        </section>

        <footer class="container">
            <div class="row">
                <p class="col-sm-4">
                    &copy; 2017 UniTour Bath
                </p>
            </div>
        </footer>

    </body>
</html>
