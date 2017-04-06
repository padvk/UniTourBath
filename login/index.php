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
            session_start(); 
            
            $username = $password = $userError = $passError = '';
            
            
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $username = $_POST['username']; $password = $_POST['password'];
                if($username === 'admin' && $password === 'password'){
                    $_SESSION['login'] = true; 
                    header('LOCATION:../admin'); 
                    die();
                }
                if($username !== 'admin')$userError = 'Invalid Username';
                if($password !== 'password')$passError = 'Invalid Password';
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
                        Welcome to UniTour Bath admin login. Please enter your username and password, and press <b>Login</b>
                    </h3>
                    <form class="form-horizontal" method="post" >
                        <div class="form-group">
                            <label class="col-xs-6 control-label" for="username">Username</label>
                            <div class="col-xs-6">
                                <input type="text" name="username" class="form-control" placeholder="Enter Username" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-6 control-label" for="password">Password</label>
                            <div class="col-xs-6">
                                <input type="password" name="password" class="form-control" placeholder="Enter Password" value=""> 
                            </div>
                        </div>                    
                        <div class="row text-center">
                            <input class="btn btn-primary" id="continueButton" type="submit" value="Login" />
                            <br>
                        </div> 
                    </form>
                    
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
