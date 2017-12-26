<?php
    require("connect.php");
    session_start();
    // Check if user is logged in. This is a protected page.

    if(!isset($_SESSION['username'])){
        echo "Not logged in, no permission";
        header("Location: login.php");  
        
    }
    $username = $_SESSION['username'];
    $date = date('Y-m-d');
    echo "<p> Signed in as ".$username." </p>";
    echo "<p> Today is ".$date." </p>";
    
   

    $upcoming_flights_query = "SELECT flight_num, departure_time, arrival_time,departure_airport, arrival_airport,price,status FROM flight WHERE departure_time>'$date'";
    $upcoming_flights = mysqli_query($conn, $upcoming_flights_query);
    
    $purchased_flights_query = "SELECT ticket.ticket_id, flight.airline_name,flight.flight_num,departure_airport,".
    "departure_time, arrival_airport, arrival_time,price,status FROM purchases join customer ".
    "on purchases.customer_email=customer.email join ticket on ticket.ticket_id=purchases.ticket_id join flight on flight.flight_num=ticket.flight_num ".
    "where customer_email='$username'";
    $purchased_flights = mysqli_query($conn, $purchased_flights_query);
    
    
    $avail_to_purchase_query = "SELECT airline_name, flight_num, departure_airport, departure_time,arrival_airport, arrival_time, price FROM flight ".
    "where flight_num not in (select flight_num from purchases natural join ticket where customer_email='$username')";
    $avail_to_purchase=mysqli_query($conn,$avail_to_purchase_query);
    
    if (@$_POST['Purchase']) {
    // echo 'hello';
    $customer_email = $_SESSION['username'];
    $flight_num = $_POST['flight_num'];
    $airline_name = $_POST['airline_name'];
    $departure_airport = $_POST['departure_airport'];
    $departure_time = $_POST['departure_time'];
    $arrival_airport = $_POST['arrival_airport'];
    $arrival_time = $_POST['arrival_time'];
    $booking_agent_id = 'NULL';
    $price = $_POST['price'];
    $existing_ticket_id_array = array();                       
    $sql = "SELECT * FROM customer WHERE email = '$customer_email'";
    
    $existing_ticket_query = "SELECT * from purchases";
    $existing_ticket = mysqli_query($conn, $existing_ticket_query);
    
    $customers_info = mysqli_query($conn, $sql);
    if (mysqli_num_rows($customers_info)) {
        $random_ticket_id = rand(1000, 9999);
        while ($rows=mysqli_fetch_array($existing_ticket)){
            $existing_ticket_id_array[] = $rows['ticket_id']; 
           
        }
        while(in_array($random_ticket_id, $existing_ticket_id_array)){
            $random_ticket_id = rand(1000, 9999);
            
        }
        echo $random_ticket_id;
        
        $sql = "INSERT INTO ticket VALUES ('$random_ticket_id', '$airline_name', '$flight_num')";
        mysqli_query($conn, $sql);
        echo '<br>' .$sql;

        $sql = "INSERT INTO purchases VALUES ('$random_ticket_id', '$customer_email', '$booking_agent_id')";
        mysqli_query($conn, $sql);
        echo '<br>' .$sql;
        
        //$flights = showMyFlights($conn);
        echo "<meta http-equiv='refresh' content='0'>";
    }
}

function showMyFlights($status) {
    $conn = $GLOBALS['conn'];
    $username = $_SESSION['username'];
    $flights  = mysqli_query($conn,"SELECT ticket.ticket_id, flight.airline_name,flight.flight_num,departure_airport, "
            . "departure_time, arrival_airport, arrival_time,price,status FROM purchases join customer ".
    "on purchases.customer_email=customer.email join ticket on ticket.ticket_id=purchases.ticket_id join flight on flight.flight_num=ticket.flight_num ".
    "where customer.email='$username'"." AND status='$status'");
    return $flights;
}

if (@$_POST['status_choice']) { 
    // Gets the choise teh user selected
    $_SESSION['status_selection'] = $_POST['status_choice'];
    echo $_SESSION['status_selection'];
    // $flights = showMyFlights($_SESSION['status_selection']);
}
$flights = showMyFlights($_SESSION['status_selection']);





if (@$_POST['SearchPurchasedFlights']) {
    $departure_airport_select = $_POST['departure_airport_select'];
    $arrival_airport_select = $_POST['arrival_airport_select'];
    $airline_name_select = $_POST['arrival_airport_select'];
    $flight_date = $_POST['flight_date'];
    $flight_date_start = $flight_date . ' 00:00:00';
    $flight_date_end = $flight_date . ' 11:59:59';

    $purchased_flight_num_query = "SELECT flight.flight_num FROM purchases join customer ".
    "on purchases.customer_email=customer.email join ticket on ticket.ticket_id=purchases.ticket_id join flight on flight.flight_num=ticket.flight_num ".
    "where customer_email='$username'";
    // echo $departure_airport_select . $arrival_airport_select . $flight_date .'<br>';
    if ($departure_airport_select){
        $sql = "SELECT * FROM flight WHERE departure_airport = '$departure_airport_select' and flight_num not in "; 
        $search_purchased_flights_results = mysqli_query($conn, $sql);
    }
    elseif ($arrival_airport_select){
        $sql = "SELECT * FROM flight WHERE departure_airport = '$arrival_airport_select'"; 
        $search_purchased_flights_results = mysqli_query($conn, $sql);
    }
    elseif ($airline_name_select){
        $sql = "SELECT * FROM flight WHERE airline_name = '$airline_name_select'"; 
        $search_purchased_flights_results = mysqli_query($conn, $sql);
    }
    elseif ($flight_date){
        $sql = "SELECT * FROM flight WHERE departure_time >= '$flight_date_start' AND departure_time <= '$flight_date_end'"; 
        $search_purchased_flights_results = mysqli_query($conn, $sql);
    }
    elseif ($departure_airport_select && $arrival_airport_select){
        $sql = "SELECT * FROM flight WHERE departure_airport = '$departure_airport_select' and arrival_airport = '$arrival_airport_select'"; 
        $search_purchased_flights_results = mysqli_query($conn, $sql); 
    }
    elseif ($departure_airport_select && $arrival_airport_select && $airline_name_select){
        $sql = "SELECT * FROM flight WHERE departure_airport = '$departure_airport_select' and arrival_airport = '$arrival_airport_select' and airline_name = '$airline_name'"; 
        $search_purchased_flights_results = mysqli_query($conn, $sql); 
    }
    elseif ($departure_airport_select && $arrival_airport_select && $airline_name_select && $flight_date){
        $sql = "SELECT * FROM flight WHERE departure_airport = '$departure_airport_select' and arrival_airport = '$arrival_airport_select' and airline_name = '$airline_name' and departure_time >= '$flight_date_start' AND departure_time <= '$flight_date_end'"; 
        $search_purchased_flights_results = mysqli_query($conn, $sql);
    }
    
   
}
if (@$_POST['confirm_purchase']) {
    // echo 'hello';
    $customer_email = $_POST['customer_email'];
    $flight_num = $_POST['flight_num'];
    $airline_name = $_POST['airline_name'];
    $booking_agent_id = $_SESSION['booking_agent_id'];

    $sql = "SELECT * FROM customer WHERE email = '$customer_email'";
    $customers = mysqli_query($conn, $sql);
    if (mysqli_num_rows($customers)) {

        $random_ticket_id;
        $is_unique = false;

        while($is_unique == false) {
            $random_ticket_id = rand(1000, 9999);
            echo $random_ticket_id;

            $sql = "SELECT ticket_id FROM ticket"; 
            $tickets = mysqli_query($conn, $sql);
            $num_tickets = mysqli_num_rows($tickets);
            // echo '<br>number of tickets: ' . $num_tickets;
            $counter = 1;

            while ($ticket = mysqli_fetch_array($tickets)) {
                if ($ticket['ticket_id'] == $random_ticket_id) {
                    break;
                }
                else if ($counter == $num_tickets) {
                    $is_unique = true;
                }
                $counter += 1;
            }
        }

        $sql = "INSERT INTO `ticket` (`ticket_id`, `airline_name`, `flight_num`) VALUES ('$random_ticket_id', '$airline_name', '$flight_num')";
        mysqli_query($conn, $sql);
        echo '<br>' .$sql;

        $sql = "INSERT INTO `purchases` (`ticket_id`, `customer_email`, `booking_agent_id`) VALUES ('$random_ticket_id', '$customer_email', '$booking_agent_id')";
        mysqli_query($conn, $sql);
        echo '<br>' .$sql;
    }


    // $sql = "SELECT * FROM customer WHERE email = '$customer_email'";
    // $customers = mysqli_query($conn, $sql);
    // if (mysqli_num_rows($customers)) {



        // $sql = "SELECT ticket_id FROM ticket WHERE flight_num = '$flight_num'"; 
        // $tickets = mysqli_query($conn, $sql);
        // if (mysqli_num_rows($tickets)) {
        //     $arrayofrows = array();
        //     while($row = mysqli_fetch_array($tickets)) {
        //         $arrayofrows = $row;
        //     }
        //     echo $arrayofrows[0];
        //     $sql = "INSERT INTO `purchases` (`ticket_id`, `customer_email`, `booking_agent_id`) VALUES ('$arrayofrows[0]', '$customer_email', '$booking_agent_id')";
        //     mysqli_query($conn, $sql);
        // }
    // }
}

if (@$_POST['search_flights']) {
    $departure_airport_select = $_POST['departure_airport_select'];
    $arrival_airport_select = $_POST['arrival_airport_select'];
    $flight_date = $_POST['flight_date'];
    $flight_date_start = $flight_date . ' 00:00:00';
    $flight_date_end = $flight_date . ' 11:59:59';

    // echo $departure_airport_select . $arrival_airport_select . $flight_date .'<br>';

    if (!$departure_airport_select && !$arrival_airport_select && $flight_date) {
        $sql = "SELECT * FROM flight WHERE departure_time >= '$flight_date_start' AND departure_time <= '$flight_date_end'"; 
        $search_flights_results = mysqli_query($conn, $sql);
        echo $sql;
    }

    if ($departure_airport_select && !$arrival_airport_select) {
        $sql = "SELECT * FROM flight WHERE departure_airport = '$departure_airport_select'"; 
        $search_flights_results = mysqli_query($conn, $sql);
        echo $sql;
    }

    if (!$departure_airport_select && $arrival_airport_select) {
        $sql = "SELECT * FROM flight WHERE arrival_airport = '$arrival_airport_select'"; 
        $search_flights_results = mysqli_query($conn, $sql);
        echo $sql;
    }

    if ($departure_airport_select && $arrival_airport_select && !$flight_date) {
        $sql = "SELECT * FROM flight WHERE departure_airport = '$departure_airport_select' AND arrival_airport = '$arrival_airport_select'"; 
        $search_flights_results = mysqli_query($conn, $sql);
        echo $sql;
    }

    if ($departure_airport_select && $arrival_airport_select && $flight_date) {
        $sql = "SELECT * FROM flight WHERE departure_airport = '$departure_airport_select' AND arrival_airport = '$arrival_airport_select' AND departure_time >= '$flight_date_start' AND departure_time <= '$flight_date_end'"; 
        $search_flights_results = mysqli_query($conn, $sql);
        echo $sql;
    }
}

?>


?>

<html>
    <title> Home </title>
    <head>
        <a href="logout.php">Logout</a>
    <h2>Home </h2>
    
    </head>
    <body>
       
        <table name="PurchasedFlights" border="1">
            
            <thead>My purchased flights</thead>
            <tbody>
            <th>Ticket ID</th>
            <th>Flight Number</th>
            <th>Airline Name</th>
            <th>Departure Airport</th>
            <th>Departure Time</th>
            <th>Arrival Airport</th>
            <th>Arrival Time</th>
            <th>Status</th>
            </tbody>
            
            
            <?php
               if (empty($flights)){
                    echo"<tr>";
                    echo"<td>"."None"."</td>";
                    echo"<td>"."None"."</td>";
                    echo"<td>"."None"."</td>";
                    echo"<td>"."None"."</td>";
                    echo"<td>"."None"."</td>";
                    echo"<td>"."None"."</td>";
                   echo"<td>"."None"."</td>";
                   echo"<td>"."None"."</td>";
                    echo"</tr>";
                }
              //  else{
                while($rows= mysqli_fetch_array($flights)){
                    echo"<tr>";
                    echo"<td>".$rows['ticket_id']."</td>";
                    echo"<td>".$rows['flight_num']."</td>";
                    echo"<td>".$rows['airline_name']."</td>";
                    echo"<td>".$rows['departure_airport']."</td>";
                    echo"<td>".$rows['departure_time']."</td>";
                    echo"<td>".$rows['arrival_airport']."</td>";
                    echo"<td>".$rows['arrival_time']."</td>";
                    echo"<td>".$rows['status']."</td>";
                    echo"</tr>";
                }
                
               // }
            ?>
            
        </table>
        
        Search for Flights 
        <form action="" name="status_form" method="post">
            
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="Upcoming" <?php echo ($_SESSION['status_selection'] == 'Upcoming')?'checked':'' ?>>Upcoming
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="In progress" <?php echo ($_SESSION['status_selection'] == 'In progress')?'checked':'' ?>>In progress
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="On time" <?php echo ($_SESSION['status_selection'] == 'On time')?'checked':'' ?>>On time
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="Delayed" <?php echo ($_SESSION['status_selection'] == 'Delayed')?'checked':'' ?>>Delayed
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="Completed" <?php echo ($_SESSION['status_selection'] == 'Completed')?'checked':'' ?>>Completed
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="Cancelled" <?php echo ($_SESSION['status_selection'] == 'Cancelled')?'checked':'' ?>>Cancelled
        </form>

 
        <br><br><br>
        
        
        
        
        <br>

        
        
        <form action="" name="search_form" method="post">
            <select name="departure_airport_select">
                <option value="0" hidden selected>Select departure airport</option>
                <?php
                $airports = mysqli_query($conn, "SELECT airport_name FROM airport");
                while ($row = mysqli_fetch_array($airports)) {
                    echo '<option value="'.$row['airport_name'].'">'.$row['airport_name'].'</option>';
                }
                ?>                    
            </select>
            <select name="arrival_airport_select">
                <option value="0" hidden selected>Select destination</option>
                <?php
                $airports = mysqli_query($conn, "SELECT airport_name FROM airport");
                while ($row = mysqli_fetch_array($airports)) {
                    echo '<option value="'.$row['airport_name'].'">'.$row['airport_name'].'</option>';
                }
                ?>                    
            </select>
            Date
            <input type="date" name="flight_date" placeholder="yyyy-mm-dd"/>
            <input type="submit" name="search_flights" value="Search Flights"/>
        </form>
        
       
        <table name="AvalFlightsToPurchase" border="1">
            <thead>Available Flights to Purchase</thead>
            <tbody>
            <?php
        if (isset($search_flights_results)) {
            if (mysqli_num_rows($search_flights_results)) {
                echo '<table border="1">            
                    <tbody>
                        <th>Airline Name</th>
                        <th>Flight Number</th>
                        <th>Departure Airport</th>
                        <th>Departure Time</th>
                        <th>Arrival Airport</th>
                        <th>Arrival Time</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Purchase Ticket</th>
                    </tbody>';
                    
                    $counter = 0;
                    while ($row = mysqli_fetch_array($search_flights_results)) {
                        $counter += 1;
                        echo "<tr>";
                        echo "<td>".$row['airline_name']."</td>";
                        echo "<td>".$row['flight_num']."</td>";
                        echo "<td>".$row['departure_airport']."</td>";
                        echo "<td>".$row['departure_time']."</td>";
                        echo "<td>".$row['arrival_airport']."</td>";
                        echo "<td>".$row['arrival_time']."</td>";
                        echo "<td>".$row['price']."</td>";
                        //echo "<td>".$row['airplane_id']."</td>";
                        echo "<td>".$row['status']."</td>";
                        echo '<td>
                        <form action="" name="purchase_form" method="post">    
                            <input class="cpb" id="confirm_purchase_button_'.$counter.'" type="submit" name="confirm_purchase" value="Purchase"/>
                            <input type="hidden" name="flight_num" value="'.$row['flight_num'].'"/>
                            <input type="hidden" name="airline_name" value="'.$row['airline_name'].'"/>
                        </form>
                        </td>';
                        echo "</tr>";
                    }
                echo '</table>';
            }
            else {
                echo 'no results';
            }
        }
        ?>
  
            </tbody>
            
            
            
            
      <table/>
        
      
      
      
    </body>
</html>