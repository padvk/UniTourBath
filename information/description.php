<?php
    include "globals.php";
    require_once "DB.php";

    // Opens a connection to a MySQL server
	$dsn="mysql://$user:$password@$host/$database";
    $connection = DB::connect($dsn);
	if (!$connection) {
	  die('Not connected : ' . mysql_error());
	}

    //Create SQL query
    $entry = "EDGE"; //Change for a variable that is passed into the code.
    $query = "SELECT name, fullinfo, links FROM utbpoi WHERE name = '$entry' ";
    $result = $connection->query($query);
    $result->fetchInto($col);
    $name = $col[0];
    $description = $col[1];
    $links = $col[2];
?>

<head>
    <title> Descriptions </title>
<head>

<body>
    <h1>
        <?php
            echo $name;
        ?>
    </h1>
    <p>
        <?php
            echo $description;
        ?>
    </p>
    <p>
        <a href = <?php echo $links; ?> > More information </a> 
    </p>
</body>
