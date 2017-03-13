<?php
	$time = $_GET['t'];
	$dep = $_GET['dep'];
		
	// code from https://developers.google.com/maps/documentation/javascript/mysql-to-maps
	
	include "../globals.php";
	require_once "DB.php";
	
	// Start XML file, create parent node
	$dom = new DOMDocument("1.0");
	$node = $dom->createElement("markers");
	$parnode = $dom->appendChild($node);

	// Opens a connection to a MySQL server
	$dsn="mysql://$user:$password@$host/$database";
    $connection = DB::connect($dsn);
	if (!$connection) {
		die('Not connected : ' . mysql_error());
	}

	$priority = $time / 30; // priority depends on time selection
	
	// Select all the rows in the markers table
	$query = "SELECT * FROM utbpoi WHERE priority=$priority ORDER BY orderID"; // will also search for department=$dep. Dep should be ID, not name.
	$result = mysql_query($query);
	if (!$result) {
		die('Invalid query: ' . mysql_error());
	}

	header("Content-type: text/xml");

	// Iterate through the rows, adding XML nodes for each

	while ($row = @mysql_fetch_assoc($result)){
		// Add to XML document node
		$node = $dom->createElement("marker");
		$newnode = $parnode->appendChild($node);
		$newnode->setAttribute("orderID", $row['orderID']);
		$newnode->setAttribute("name", $row['name']);
		$newnode->setAttribute("address", $row['areaOfCampus']);
		$newnode->setAttribute("lat", $row['latitude']);
		$newnode->setAttribute("lng", $row['longitude']);
		$newnode->setAttribute("type", "poi");
		// other attribute, will add when Sam updates gog.html
	}

	echo $dom->saveXML();
?>