<!DOCTYPE html>
<html>
    
	<head>
		<title>UniTour Bath</title>
		<link rel="icon" href="unitourlogo.PNG">
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        
        <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,700' rel='stylesheet' type='text/css'>
        
        <link rel="stylesheet" type="text/css" href="../main.css">
            
    </head>
    
	<body>
        <header class = "container">
            <div class = "row">
                <h1 class = "col-sm-10">
                    UniTour Bath
                </h1>
                <div class = "col-sm-2 text-right">
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
			var mapFollow = true;
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
                        var description = markerElem.getAttribute('description');
                        var fullinfo = markerElem.getAttribute('fullinfo');
                        var type = markerElem.getAttribute('type');
                        var photo = markerElem.getAttribute('photo');
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
                        text.textContent = description;
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
                        markers.push({point: marker, name: name, fullinfo: fullinfo, photo: photo});
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
                        if (mapFollow) { // if the map should follow the user
                            map.setCenter(pos);
                        }
                        
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
                        m = markers[closest];
                        markers[currentPOI].point.setIcon('markers/previous.png');
                        m.point.setIcon('markers/current.png');
                        
                        // play notification sound
                        audio.play();
                        
                        // update info page
                        document.getElementById("infotitle").innerHTML = m.name;
                        document.getElementById("infopage").innerHTML = m.fullinfo;
                        if (m.photo) {
                            console.log("There is a photo");
                            var imgstring = "<br/><img id='photo'src='../information/photos/" + m.photo + "' />";
                            document.getElementById("infopage").innerHTML = m.fullinfo + imgstring;
                        }
                        
                        document.getElementById("buttonText").innerHTML = "This Stop: " + closestName;
                        currentPOI = closest;
                        if (currentPOI == initialPOI) { // tour has looped, user is back at the beginning
                            
                            // END OF TOUR - take user to final screen where they can see info for all POIs (?)
                            document.getElementById("buttonText").innerHTML = "Tour finished.";
                            document.getElementById("infotitle").innerHTML = "Tour completed!";
                            document.getElementById("infopage").innerHTML = "Your tour is now over. We hope you enjoyed your time at the University of Bath.";
                            
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
                var m = markers[closest];
                var name = m.name;
                var next = (closest+1)%markers.length;
                m.point.setIcon('markers/current.png');
                markers[next].point.setIcon('markers/next.png');
                currentPOI = initialPOI = closest;
                initialised = true;
                document.getElementById("buttonText").innerHTML = "This Stop: " + name;
                document.getElementById("infotitle").innerHTML = name;
                document.getElementById("infopage").innerHTML = m.fullinfo;
                if (m.photo) {
                    console.log("There is a photo");
                    var imgstring = "<br/><img id='photo' src='../information/photos/" + m.photo + "' />";
                    document.getElementById("infopage").innerHTML = m.fullinfo + imgstring;
                }
            }
            
            function toggleFollow() {
                mapFollow = !mapFollow;
                console.log("Toggled map following");
            }
            
            function help() {
                document.getElementById("infotitle").innerHTML = "Help Page";
                document.getElementById("infopage").innerHTML = "Walk towards the icon with the star, when you are close enough the icon will become a flag. You can now click the button at the bottom of the page and see information about this place. Once you walk away from the point it will become footprints. Once you have finished your tour, a summary of all your points will appear. If you have a point that includes a floorplan, the information should be in order of it appears if you follow the route. Have fun!";
            }
            
            function clearAll() {
                document.getElementById("infotitle").innerHTML = "";
                document.getElementById("infopage").innerHTML = "";
            }
            
            function doNothing() {}
            
        </script>
        <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBdds_EpEfCnpojHMvMUvntJj1gCx9moOA&callback=initMap">
        </script>
        
        
        <section class="container">
            <div class="row" id="menu">
                <figure class="col-xs-8" >
                    <p data-toggle="modal" data-target="#infoModal" class="quote" id="buttonText">
                        
                        This Stop:
                        
                    </p>
                </figure>
                
                <figure class="col-xs-2" onclick="toggleFollow()">
                    <a id="btn-menu">
                        <img src="followme.png" style="width:45px;height:45px;"/>
                    </a>
                </figure>
                
                <figure class="col-xs-2" onclick="help()">
                    <a data-toggle="modal" data-target="#infoModal" id="btn-menu">
                        <img src="help.png" style="width:45px;height:45px;"/>
                    </a>
                </figure>
            </div>
            
            <div id="infoModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title" id="infotitle"></h4>
                        </div>
                        <div class="modal-body">
                            <p id="infopage"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal" onclick="clearAll()">Close</button>
                        </div>
                    </div>
                    
                </div>
            </div>
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
