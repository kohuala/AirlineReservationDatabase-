<?php

    require('connect.php');
    session_start();

   
    if(@$_POST['register']){
       
        $username = mysqli_real_escape_string($conn,$_POST['username']);
        //$email = mysqli_real_escape_string($conn,$_POST['email']);
        //$name = mysqli_real_escape_string($conn,$_POST['name']);
        $password = mysqli_real_escape_string($conn,$_POST['password']);
        $repassword = mysqli_real_escape_string($conn,$_POST['repassword']);
        $first_name = mysqli_real_escape_string($conn,$_POST['first_name']);
        $last_name = mysqli_real_escape_string($conn,$_POST['last_name']);
        $date_of_birth = mysqli_real_escape_string($conn,$_POST['date_of_birth']);
        $airline_name = mysqli_real_escape_string($conn,$_POST['airline_name']);
        
        
        if ($username && $first_name && $password && $repassword && $last_name && $date_of_birth){
        $check_db="SELECT * FROM airline_staff WHERE username = '$username'";
        $query = mysqli_query($conn,$check_db);
        // If there is a instance of that username
        if ($password == $repassword){
        if(mysqli_num_rows($query)!=0){
            echo "Username taken";}
                
        else{
            $statement = "INSERT INTO airline_staff VALUES('$username', '$password','$first_name', '$last_name','$date_of_birth', '$airline_name')";
            $query = mysqli_query($conn, $statement);    
           // if(!empty($query)){
                echo"<p> Successfully Registered, continue to login</p>";
                header("Location: login.php");
           // }
            /*
            else{
                echo"<p> Failed to register</p>";
                header("Location: airline_staff_register.php");
            }*/
        }}
        else{
            echo "Passwords must match";
        }
                
    }}
  ?>

<html>

<title>Register</title>
    <head>
    <h2>Register</h2>
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
                <td> Email Address</td>
                <input type="email" name="email"/>
            </tr>
            <tr>
            <br><br>
            <tr>
                <td> First Name</td>
                <input type="text" name="first_name"/>
            </tr>
            
            <tr>
                <td> Last Name</td>
                <input type="text" name="last_name"/>
            </tr>
            <br><br>
            
            <tr>
            <td> Airline Name </td>
            <!--<input type="text" name="passport_country"/>-->
             <form action="" name="airline_form" method="post">
            <select name="airline_name">
            <option value="0">Airline Name...</option>
            <?php
                $airline_names = mysqli_query($conn, "SELECT airline_name FROM airline");
                while ($row = mysqli_fetch_array($airline_names)) {
                    echo '<option value="'.$row['airline_name'].'">'.$row['airline_name'].'</option>';
                }
                ?>
            
        </select>
                 <form/>
            <tr/>
            <br><br>
            <tr>
                <td>Password</td>
                <input type="password" name="password"/>
            </tr>
            <tr>
                <td>Repeat Password</td>
                <input type="password" name="repassword"/>
            </tr
            <br><br>
            
            <tr><br><br>
            <td> Date of Birth </td>
            <input type="date" name="date_of_birth"/>
            <tr/>
 
            <tr>
                <input type="submit" name="register" value="Register"/>
            </tr>
        </form>
        <a href="register.php">Return to main registration</a>
    </body>
</html>