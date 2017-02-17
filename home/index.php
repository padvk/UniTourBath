<!DOCTYPE html>
<html>
    <head>
        <title>UniTour Bath</title>
        <link rel="icon" href="../unitourlogo.PNG">
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
        
        <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,700' rel='stylesheet' type='text/css'>
        
        <link rel="stylesheet" type="text/css" href="../main.css">
    </head>
    
    <body>
        
        <?php
            require_once "DB.php";
            include "../globals.php";
            $dsn="mysql://$user:$password@$host/$database";
            $db = DB::connect($dsn);
            if (DB::isError($db)) {
                die ($db->getMessage());
            }
			
            // define variables and set to empty values
            $departmentErr = $tourlengthErr = $disabilityErr = $voiceoverErr = "";
            $department = $tourlength = $disability = $voiceover = "";
            
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (empty($_POST["tourlength"])) {
                    $tourlengthErr = "Tour length is required";
                }
                
                if (empty($_POST["department"])) {
                    $department = "none";
                }
                
                if(!empty($_POST['checkList'])) {
                    foreach($_POST['checkList'] as $selected) {
                        if ($selected == "disability"){
                            $disability = $selected;
                            } else if ($selected == "voiceover"){
                            $voiceover = $selected;
                        }
                    }
                }
                
            }
        
        function test_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }
    ?>						
    
    <header class = "container">
        <div class = "row">
            <h1 class = "col-sm-4">
                Uni Tour Bath
            </h1>
            <div class = "col-sm-8 text-right">
                <img src="../unitourlogo.PNG" style="width:50px;height:55px;"/>
            </div>
        </div>   
    </header>
    
    <section class="jumbotron">
    </section>
    
    <section class ="container">
        <div class="container">
            <div class="row text-center">
                <h3>
                    Welcome to UniTour Bath. This website is a virtual tour assistant, and will guide you on a personalised tour around the UoB campus. 
                    To begin, please insert your tour preferences, and press <b>Begin Tour</b>
                </h3>
                <form action="../map/" class="form-horizontal" method="post" >  
                    <div class="form-group">
                        <label class="col-xs-6 control-label" for="department">Department</label>
                        <div class="col-xs-6">
                            <select list="departments" type="list" name="department"  class="form-control" placeholder="Enter department" value="<?php echo $department;?>">
                            <datalist id="departments">
                                <?php
                                    $query = <<<ENDOFSTRING
                                    SELECT * FROM utbdept; 
ENDOFSTRING;
                                    $result = $db->query($query);
                                    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                                        echo '<option value="'.$row['dept'].'">'.$row['dept'].'</option>';
                                    } echo '</select>';
                                ?>
                            </datalist>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-6 control-label" for="tourlength">Tour Length (minutes)</label>
                        <div class="col-xs-6">
                            <select list="lengths" type="list" name="tourlength"  class="form-control" placeholder="Enter Tour Length" value="<?php echo $department;?>">
                            <datalist id="departments">
                                <?php
                                    $query = <<<ENDOFSTRING
                                    SELECT * FROM utbtourlength; 
ENDOFSTRING;
                                    $result = $db->query($query);
                                    while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) {
                                        echo '<option value="'.$row['length'].'">'.$row['length'].'</option>';
                                    } echo '</select>';
                                ?>
                            </datalist>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-6 control-label" for="disability">Accessibility for disabled</label>
                        <div class="col-xs-6 text-left">
                            <input type="checkbox" name="checkList[]" value ="disability" <?php  if(isset($disability) && $disability=="disability") echo "checked";?>>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-6 control-label" for="voiceover">Voiceover</label>
                        <div class="col-xs-6 text-left">
                            <input type="checkbox" name="checkList[]" value="voiceover" <?php  if(isset($voiceover) && $voiceover=="voiceover") echo "checked";?> >
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <input class="btn btn-primary" id="continueButton" type="submit" value="Begin Tour"/>
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
            <p class="col-sm-4">
                &copy; 2017 UniTour Bath
            </p>
        </div>
    </footer>
</body>
</html>
