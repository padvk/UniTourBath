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
        
        <section class ="container">
            <div class="container">
                <div class="row text-center">
                    <h3>
                        Change POI information here, choose the point you wish to change and then edit the text. Then click <b> save </b>.
                    </h3>
                    
                    <?php
                        require_once "DB.php";
                        include "../globals.php";
                        $dsn="mysql://$user:$password@$host/$database";
                        $db = DB::connect($dsn);
                        if (DB::isError($db)) {
                            die ($db->getMessage());
                        }
                        
                        if ($_POST != NULL){
                            $name = $_POST['name'];
                            $fullinfo = $_POST['info'];
                            
                            $query = <<<ENDOFSTRING
                            UPDATE utbpoi SET fullinfo = '$fullinfo' WHERE name = '$name';
ENDOFSTRING;
                            $result = $db->query($query);
                            if (DB::isError($result)) {
                                echo "<h4>Not successful: " . $result->getMessage() . "</h4>";
                            }
                            else {
                                echo "<h4>Change successful.</h4>\n";
                            }
                        }
                        
                    ?>	
                    
                    <form class="form-horizontal" method="post" >
                        <div class="form-group">
                            <label class="col-xs-6 control-label" for="name">Name</label>
                            <div class="col-xs-6">
                                <select list="names" type="list" name="name" class="form-control" value="">
                                    <datalist id="names">
                                        <?php
                                            $query = <<<ENDOFSTRING
                                            SELECT name FROM utbpoi; 
ENDOFSTRING;
                                            $result = $db->query($query);
                                            while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                                                echo '<option value="'.$row['name'].'">'.$row['name'].'</option>';
                                            } echo '</select>';
                                        ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-6 control-label" for="info">Information</label>
                                <div class="col-xs-6">
                                    <input type="info" name="info" class="form-control" placeholder="Enter New Info" value=""> 
                                </div>
                            </div>                    
                            <div class="row text-center">
                                <input class="btn btn-primary" id="continueButton" type="submit" value="Save" />
                                <br>
                            </div> 
                        </form>
                        
                    </div>
                </div>
            </section>  
            <?php
                $result->free();
                $db->disconnect();
            ?>
            
            <footer class="container">
                <div class="row">
                    <p class="col-xs-6">
                        &copy; 2017 UniTour Bath
                    </p>
                </div>
            </footer>
        </body>
    </html>
