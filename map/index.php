
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
        <div class="embed-responsive embed-responsive-4by3">
                <iframe class="embed-responsive-item" src="https://www.google.com/maps/d/embed?mid=1XPNwVRY9qFH2BHH6EOBv_O4Q0Us"></iframe>
        </div>
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
            <div class="row">
                <figure class="col-sm-6">
                    <p href="../information" class="quote">
                        
                        This Stop: <?php echo $row['name'] ?>
                        
                    </p>
                </figure>
                <figure class="col-sm-6">
                    <p href="../information" class="quote">
                        
                        Next Stop: <?php $row = $result->fetchRow(DB_FETCHMODE_ASSOC); echo $row['name']  ?>
                        
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
