<?php
require('connect.php');
    session_start();

    if(@$_POST['login']){
        $username = mysqli_real_escape_string($conn,$_POST['username']);
        $password = mysqli_real_escape_string($conn,$_POST['password']);
       
        // Make user give a username AND password
        if($username && $password){
            $check_db="SELECT email as username, name, password, building_number, street, city, state, phone_number,
                    passport_number, passport_expiration, passport_country, date_of_birth FROM customer WHERE email = '$username'";
            $query = mysqli_query($conn,$check_db);
            // If there is a instance of that username
            if(mysqli_num_rows($query)!=0){
                
                $row = mysqli_fetch_assoc($query);
                $db_user = $row['username'];
                $db_pass = $row['password'];
                
                if($username==$db_user && $password ==$db_pass){
                    $_SESSION['username']=$username;
                    $_SESSION['status_selection'] = "Upcoming";
                    header("Location: home_customer.php");
                    echo'<p><b> Logged In</b></p>';
                }
                else{
                    echo'<p>Username and/or password is wrong</p>';
                }  
            }
                    // The username isnt in customer
            else{
                        
                $check_db="SELECT username, password, first_name, last_name, date_of_birth, airline_name FROM airline_staff WHERE username = '$username'";
                $query = mysqli_query($conn,$check_db);//if(mysqli_num_rows($query)!=0)
                
                if(mysqli_num_rows($query)!=0){
                    $row = mysqli_fetch_assoc($query);
                    $db_user = $row['username'];
                    $db_pass = $row['password'];
                
                    if($username==$db_user && $password ==$db_pass){
                        $_SESSION['username'] = $username;
                        
                        $_SESSION['first_name'] = $row['first_name'];
                        $_SESSION['last_name'] = $row['last_name'];
                        $_SESSION['date_of_birth'] = $row['date_of_birth'];
                        $_SESSION['airline_name'] = $row['airline_name'];
                        header("Location: home_airline_staff.php");
                        echo'<p><b> Logged In</b></p>';
                    
                    }
                    else{
                        echo'<p>Username and/or password is wrong</p>';
                    }
            
                }
            
                // The username isnt in netiher customer nor airline_staff
            
                else{
                       
                    $check_db="SELECT email as username, password, booking_agent_id FROM booking_agent WHERE email = '$username'";
                    $query = mysqli_query($conn,$check_db);
                    // If there is a instance of that username
                    if(mysqli_num_rows($query)!=0){
                
                        $row = mysqli_fetch_assoc($query);
                        $db_user = $row['username'];
                        $db_pass = $row['password'];
                
                        if($username==$db_user && $password ==$db_pass){
                            $_SESSION['username'] = $username;
                            header("Location: home_booking_agent.php");
                            echo'<p><b> Logged In</b></p>';
                    
                        }
                    else{
                        echo'<p>Username and/or password is wrong</p>';
                        }
            
                    }
            
                    // The username isnt in db
                    
                    else{
                        echo'<p> Username not here in database</p>';
                    }
                   
        
                }
            }
        }
        // I fuser does not given username AND password
        else{
            echo'<p>Username and password empty</p>';
    }}
    
 
        
?>

<html>

    <title>Login</title>
    <head>
    <h2>Login</h2>
    </head>
    <body>
       
        <form action="" method="post">
            <tr>
              

            
            </tr>
            <tr>
                <td> Username</td>
            <input type="text" name="username"/>
        </tr>
        <tr>
            <td>Password</td>
            <input type="password" name="password"/>
        </tr>
        <tr>
            <input type="submit" name="login" value="login"/>
        </tr>
        </form>
        
        <a href="index.php">Return to index</a>
        <br>
        <a href="bar_chart.php">Bar Chart</a>
    </body>
</html>