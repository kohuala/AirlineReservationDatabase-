<?php

    require('connect.php');
    session_start();

   
    if(@$_POST['register']){
        
        
        //$username = mysqli_real_escape_string($conn,$_POST['username']);
        $email = mysqli_real_escape_string($conn,$_POST['email']);
        echo $email;
        //$name = mysqli_real_escape_string($conn,$_POST['name']);
        $password = mysqli_real_escape_string($conn,$_POST['password']);
        $repassword = mysqli_real_escape_string($conn,$_POST['repassword']);
        /*
        $building_number=mysqli_real_escape_string($conn,$_POST['building_number']);
        $street =mysqli_real_escape_string($conn,$_POST['street']);
        $city = mysqli_real_escape_string($conn,$_POST['city']);
        $state = mysqli_real_escape_string($conn,$_POST['state']);
        $phone_number = mysqli_real_escape_string($conn,$_POST['phone_number']);
        $passport_number = mysqli_real_escape_string($conn,$_POST['passport_number']);
        $passport_expiration = mysqli_real_escape_string($conn,$_POST['passport_expiration']);
        //$passport_country = mysqli_real_escape_string($conn,$_POST['passport_country']);
        $date_of_birth = mysqli_real_escape_string($conn,$_POST['date_of_birth']);
        */
        if ($email && $password && $repassword){
        $check_db="SELECT email, password, booking_agent_id FROM booking_agent WHERE email = '$email'";
        $query = mysqli_query($conn,$check_db);
        echo $check_db;
        // If there is a instance of that username
        if(mysqli_num_rows($query)!=0){
            echo "Username taken";}
                
        else{
            if($password == $repassword){
                $booking_agent_id = mt_rand(0,99);
                
                
                $existing_booking_agent_id_array = array();                       
    
                $existing_booking_agent_id_query = "SELECT * from booking_agent";
                $existing_booking_agent = mysqli_query($conn, $existing_booking_agent_id_query);
    
                echo "Random: ".$booking_agent_id;
                while ($rows = mysqli_fetch_array($existing_booking_agent)) {
                    $existing_booking_agent_id_array[] = $rows['booking_agent_id'];
                    
                    
                }
                    print_r($existing_booking_agent_id_array);
                while(in_array($booking_agent_id, $existing_booking_agent_id_array)){
                    $booking_agent_id = rand(0, 99);
                    echo $booking_agent_id;
                }
                
                $statement = "INSERT INTO booking_agent VALUES('$email', '$password', '$booking_agent_id')";
                    $query = mysqli_query($conn, $statement);    
                    if($query){
                        echo"<p> Successfully Registered, continue to login</p>";
//                        header("Location: login.php");
                    }
                    else{
                        echo"<p> Failed to register</p>";
//                        header("Location: booking_agent_register.php");
                    }
        }
        else{
            echo "Passwords must match";
        }
            }
                
    }
    else{
        echo"Not all fields entered";
    }
            }
  ?>

<html>

<title>Register</title>
    <head>
    <h2>Register</h2>
    </head>
    <body>
       
        <form action="" method="post">
            <tr>
            <!--    <td>User Type </td> -->
            <!--<form action="" name="user_type_form" method="post">    
            <select name='user_type'onchange=this.form.submit()>
                <option value="0">--Select User Type--</option>
                <option value="airline_staff">Airline Staff</option>
                <option value="customer"> Customer </option>
                <option value="booking_agent"> Booking Agent </option>
        
            </select>
            <form/>-->
                
            </tr>
            <tr>
                <td> Email Address</td>
                <input type="email" name="email"/>
            </tr>
            <tr>
                <td>Password</td>
                <input type="password" name="password"/>
            </tr>
            <tr>
                <td>Repeat Password</td>
                <input type="password" name="repassword"/>
            </tr
            <br><br>
           
            <tr>
                <input type="submit" name="register" value="Register"/>
            </tr>
        </form>
        <a href="register.php">Return to main registration</a>
    </body>
</html>