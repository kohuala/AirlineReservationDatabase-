<?php
    require('connect.php');    
    session_start();
 
    if (@$_POST['user_type']) {
        $user_type = $_POST['user_type'];
        header("Location: ".$user_type."_register.php");
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
                <td>User Type </td>
            <form action="" name="user_type_form" method="post">    
            <select name='user_type'onchange=this.form.submit()>
                <!--<option value="0">--Select User Type--</option>-->
                <option value="0"><?php $user_type?></option>
                <option value="airline_staff">Airline Staff</option>
                <option value="customer"> Customer </option>
                <option value="booking_agent"> Booking Agent </option>
        
            </select>
              
            <form/>
                
            
            
            <br><br>
        <a href="index.php">Return to index</a>
    </body>
</html>