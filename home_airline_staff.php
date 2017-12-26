<?php
require('connect.php');
include("chartphp_dist.php");
session_start();

if (@$_POST['booking_agent_choice']) { 
    $_SESSION['booking_agent_selection'] = $_POST['booking_agent_choice'];
}

echo 'booking_agent_selection: ' . $_SESSION['booking_agent_selection'] . '<br>';

$departure_airports = mysqli_query($conn, "SELECT * FROM airport");
$arrival_airports = mysqli_query($conn, "SELECT * FROM airport");

$airplanes = mysqli_query($conn, "SELECT `airplane_id` FROM `airplane` WHERE `airline_name` = '".$_SESSION['airline_name']."'");

$tickets_sold = mysqli_query($conn, "SELECT COUNT(`ticket_id`) as total FROM `purchases`");
while ($row = mysqli_fetch_array($tickets_sold)) {
    echo "total tickets sold = " . $row['total'] . "<br>";
}

if (@$_POST['search_flights']) {
    $departure_airport_select = $_POST['departure_airport_select'];
    $arrival_airport_select = $_POST['arrival_airport_select'];
    $departure_date = $_POST['departure_date'];
    $arrival_date = $_POST['arrival_date'];

    $departure_time = $departure_date . ' 00:00:00';
    $arrival_time = $arrival_date . ' 11:59:59';

   
}

if (@$_POST['add_airplane']) {
    $airline_name = $_SESSION['airline_name'];
    $airplane_id = $_POST['airplane_id'];
    $seats = $_POST['seats'];

    if ($airline_name && $airplane_id && $seats) {
        $sql = "INSERT INTO `airplane` (`airline_name`, `airplane_id`, `seats`) VALUES ('$airline_name', '$airplane_id', '$seats')"; 
        mysqli_query($conn, $sql);
        echo $sql;
    }
}

if (@$_POST['add_airport']) {
    $airport_name = $_POST['airport_name'];
    $airport_city = $_POST['airport_city'];

    if ($airport_name && $airport_city) {
        $sql = "INSERT INTO `airport` (`airport_name`, `airport_city`) VALUES ('$airport_name', '$airport_city')";
        mysqli_query($conn, $sql);
        echo $sql;
    }
}

if (@$_POST['create_flight']) {
    $airline_name = $_SESSION['airline_name'];
    $flight_num = $_POST['flight_num'];
    $departure_airport = $_POST['departure_airport'];
    $departure_date = $_POST['departure_date'];
    $departure_time = $_POST['departure_time'];
    $arrival_airport = $_POST['arrival_airport'];
    $arrival_date = $_POST['arrival_date'];
    $arrival_time = $_POST['arrival_time'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $airplane_id = $_POST['airplane_id'];

    $departure_date_time = $departure_date . ' ' . $departure_time;
    $arrival_date_time = $arrival_date . ' ' . $arrival_time;

    if ($airline_name && $flight_num && $departure_airport && $departure_date && $departure_time && 
        $arrival_airport && $arrival_date && $arrival_time &&
        $price && $status && $airplane_id) {
        $sql = "INSERT INTO `flight` (`airline_name`, `flight_num`, `departure_airport`, `departure_time`, `arrival_airport`, `arrival_time`, `price`, `status`, `airplane_id`) VALUES
('$airline_name', '$flight_num', '$departure_airport', '$departure_date_time', '$arrival_airport', '$arrival_date_time', '$price', '$status', '$airplane_id')";

        mysqli_query($conn, $sql);
        echo $sql;
    }
}

if (@$_POST['status_type']) {
    $airline_name = $_SESSION['airline_name'];
    $flight_num = $_POST['flight_num'];
    $status_type = $_POST['status_type'];

    echo $airline_name;
    echo $flight_num;
    echo $status_type;

    $sql = "UPDATE `flight` SET `status` = '$status_type' WHERE `flight`.`airline_name` = '$airline_name' AND `flight`.`flight_num` = '$flight_num'";
    mysqli_query($conn, $sql);
    echo $sql;
}

function showBookingAgents($selection) {
    $conn = $GLOBALS['conn'];

    if ($selection == "Past Month") {
        $start_date = date('Y-m-d', strtotime("-30 Days"));
        $end_date = date('Y-m-d');

        $start_time = $start_date . ' 00:00:00';
        $end_time = $end_date . ' 11:59:59';

        echo 'start time: ' . $start_time;
        echo '<br>end time: ' . $end_time;
    }

    else if ($selection == "Past Year") {
        $start_date = date('Y-m-d', strtotime("-1 Year"));
        $end_date = date('Y-m-d');

        $start_time = $start_date . ' 00:00:00';
        $end_time = $end_date . ' 11:59:59';

        echo 'start time: ' . $start_time;
        echo '<br>end time: ' . $end_time;
    }

    $booking_agents = mysqli_query($conn, "SELECT booking_agent.email, SUM(flight.price) as total_commission 
    FROM booking_agent, purchases, ticket, flight
    WHERE booking_agent.booking_agent_id = purchases.booking_agent_id
    AND purchases.ticket_id = ticket.ticket_id
    AND flight.flight_num = ticket.flight_num
    AND flight.departure_time > '$start_time'
    AND flight.departure_time < '$end_time'
    GROUP BY booking_agent.booking_agent_id
    ORDER BY total_commission DESC");
    return $booking_agents;
}

$booking_agents = showBookingAgents($_SESSION['booking_agent_selection']);

// SELECT customer_email, COUNT(customer_email) as num_tickets
// FROM purchases
// GROUP BY customer_email
// ORDER BY num_tickets DESC

$hold = mysqli_query($conn, "SELECT COUNT(ticket_id) from ticket natural join flight where departure_time between 20170401 and 20170431");

$customers = mysqli_query($conn, "SELECT customer_email, COUNT(customer_email) as num_tickets
FROM purchases
GROUP BY customer_email
ORDER BY num_tickets DESC");


$num_tickets_array = array();
while($rows=  mysqli_fetch_array($hold)){
    $num_tickets_array[] = $rows['num_tickets'];    
}
/*
echo json_encode($num_tickets_array);
$fp = fopen('numtickdata.json', 'w');
    fwrite($fp, json_encode($emparray));
    fclose($fp);
*/
function showFlights($departure_airport, $arrival_airport, $departure_date, $arrival_date) {
    $conn = $GLOBALS['conn'];
    $staff_airline = $_SESSION['airline_name'];

    if ($departure_airport == "all") {
        $d_date = $_SESSION['departure_date'];
        $a_date = $_SESSION['arrival_date'];

        $d_time = $d_date . ' 00:00:00';
        $a_time = $a_date . ' 11:59:59';

        echo '<br>' . $d_date;
        echo '<br>' . $a_date;
        echo '<br>' . $d_time;
        echo '<br>' . $a_time;

        $flights = mysqli_query($conn, "SELECT * 
            FROM flight 
            WHERE flight.airline_name = '$staff_airline' 
            AND flight.departure_time >= '$d_time' 
            AND flight.arrival_time <= '$a_time'");
        return $flights;
    }

    else {
        $d_airport = $departure_airport;
        $a_airport = $arrival_airport;

        $d_date = $departure_date;
        $a_date = $arrival_date;

        $d_time = $d_date . ' 00:00:00';
        $a_time = $a_date . ' 11:59:59';

         $flights = mysqli_query($conn, "SELECT * 
            FROM flight 
            WHERE flight.airline_name = '$staff_airline'
            AND flight.departure_airport = '$d_airport' 
            AND flight.arrival_airport = '$a_airport' 
            AND flight.departure_time > '$d_time' 
            AND flight.arrival_time > '$a_time'");
        return $flights;
    }
}

// $flights = mysqli_query($conn, "SELECT * FROM flight WHERE flight.airline_name = '".$_SESSION['airline_name']."'");
$flights = showFlights($_SESSION['departure_airport'], $_SESSION['arrival_airport'], $_SESSION['departure_date'], $_SESSION['arrival_date']);

echo"kasdjfhklsd".$num_tickets_array;
$p = new chartphp();
$p->data = array($num_tickets_array);

$p->chart_type = "bar"; 


$out = $p->render("c1");
?>
<!DOCTYPE html>
<html >
<head>
	<title>Welcome <?php echo $_SESSION['username'];?></title>
        <script src="fusioncharts/fusioncharts.js"></script>
        <script src="jquery.min.js"></script> 
        <script src="chartphp.js"></script> 
        <link rel="stylesheet" href="../../lib/js/chartphp.css"> 
</head>

<body>
	<div class="form">
		<h1>Airline Staff Home</h1>
          
        <p>
        <?php 
        echo 'name: '.$_SESSION['first_name']
        . ' ' .$_SESSION['last_name']
        . "<br> dob: " .$_SESSION['date_of_birth']
        . '<br> airline: ' .$_SESSION['airline_name'];

        ?>
        </p>

        <h3>Flights</h3>
        <table border="1">            
            <tbody>
                <th>airline_name</th>
                <th>flight_num</th>
                <th>departure_airport</th>
                <th>departure_time</th>
                <th>arrival_airport</th>
                <th>arrival_time</th>
                <th>price</th>
                <th>airplane_id</th>
                <th>status</th>
                <th>customers</th>
            </tbody>
                 
            <?php
            while ($row = mysqli_fetch_array($flights)) {
                echo "<tr>";
                echo "<td>".$row['airline_name']."</td>";
                echo "<td>".$row['flight_num']."</td>";
                echo "<td>".$row['departure_airport']."</td>";
                echo "<td>".$row['departure_time']."</td>";
                echo "<td>".$row['arrival_airport']."</td>";
                echo "<td>".$row['arrival_time']."</td>";
                echo "<td>".$row['price']."</td>";
                echo "<td>".$row['airplane_id']."</td>";
                echo '<td>
                        <form action="" name="status_form" method="post">
                            <select name="status_type" onchange="this.form.submit()">                      
                                <option value="0" hidden selected>'.$row['status'].'</option>
                                <option value="Upcoming">Upcoming</option>
                                <option value="In progress">In progress</option>
                                <option value="On time">On time</option>
                                <option value="Delayed">Delayed</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                            <input type="hidden" name="flight_num" value="'.$row['flight_num'].'"/>
                        </form>
                    </td>';

                echo "<td>";

                $customers_on_flight = mysqli_query($conn, "SELECT DISTINCT(purchases.customer_email) 
                    FROM purchases NATURAL JOIN ticket NATURAL JOIN flight 
                    WHERE purchases.ticket_id = ticket.ticket_id 
                    AND ticket.flight_num = flight.flight_num");

                while ($row2 = mysqli_fetch_array($customers_on_flight)) {
                    echo $row2['customer_email'] . '<br>';
                }

                echo "</td>";

                echo "</tr>";
            }
            ?>         
        </table>
        <form action="" name="flight_search_form" method="post">
            <select name="departure_airport_select">
                <option value="0" hidden selected>Select starting point</option>
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
            Departure Date<input type="date" name="departure_date" placeholder="yyyy-mm-dd"/>
            Arrival Date<input type="date" name="arrival_date" placeholder="yyyy-mm-dd"/>
            <input type="submit" name="search_flights" value="filter_flights"/>
        </form>

        <h3>Create Flight</h3>
        <form action="" method="post">
            <tr>
                <td>Flight Number</td>
                <input type="number" name="flight_num"/>
            </tr>
            <br>
       
            <tr>
                <td>Departure Airport</td>
                <?php
                echo '<select name="departure_airport">';
                while ($departure_airport = mysqli_fetch_array($departure_airports)) {
                    echo '<option>'.$departure_airport['airport_name'].'</option>';                
                }
                echo '</select>';
                ?>   
            </tr>

            <tr>
                <td>Departure Date</td>
                <input type="date" name="departure_date" placeholder="yyyy-mm-dd"/>
            </tr>

            <tr>
                <td>Departure Time</td>
                <input type="time" name="departure_time" placeholder="hh:mm:ss"/>
            </tr>
            <br>

            <tr>
                <td>Arrival Airport</td>
                <?php
                echo '<select name="arrival_airport">';
                while ($arrival_airport = mysqli_fetch_array($arrival_airports)) {
                    echo '<option>'.$arrival_airport['airport_name'].'</option>';                
                }
                echo '</select>';
                ?>   
            </tr>

            <tr>
                <td>Arrival Date</td>
                <input type="date" name="arrival_date" placeholder="yyyy-mm-dd"/>
            </tr>

            <tr>
                <td>Arrival Time</td>
                <input type="time" name="arrival_time" placeholder="hh:mm:ss"/>
            </tr>
            <br>

            <tr>Price</td>
                <input type="number" name="price"/>
            </tr>
            <br>

            <tr>
                <td>Status</td>
                <select name="status">                      
                    <option value="Upcoming">Upcoming</option>
                    <option value="In progress">In progress</option>
                    <option value="On time">On time</option>
                    <option value="Delayed">Delayed</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </tr>
            <br>

            <tr>
                <td>Airplane ID</td>
                <?php
                echo '<select name="airplane_id">';
                // echo '<option disabled selected>ID</option>';
                while ($airplane = mysqli_fetch_array($airplanes)) {
                    echo '<option>'.$airplane['airplane_id'].'</option>';                
                }
                echo '</select>';
                echo '<input type="hidden" />';
                ?>  
            </tr>
            <br>

            <tr>
                <input type="submit" name="create_flight" value="create_flight"/>
            </tr>
        </form>

        <h3>Add Airplane</h3>
        <form action="" method="post">
            <tr>
                <td>Airplane ID</td>
                <input type="number" name="airplane_id"/>
            </tr>
            <br>

            <tr>
                <td>Number of Seats</td>
                <input type="number" name="seats"/>
            </tr>
            <br>

            <tr>
                <input type="submit" name="add_airplane" value="add_airplane"/>
            </tr>
        </form>

        <h3>Add Airport</h3>
        <form action="" method="post">
            <tr>
                <td>Airport Name</td>
                <input type="text" name="airport_name"/>
            </tr>
            <br>
       
            <tr>
                <td>Airport City</td>
                <input type="text" name="airport_city"/>
            </tr>
            <br>

            <tr>
                <input type="submit" name="add_airport" value="add_airport"/>
            </tr>
        </form>

        <h3>Top Booking Agents</h3>
        <table border="1">            
            <tbody>
                <th>booking_agent_email</th>
                <th>total_commission</th>
            </tbody>
                 
            <?php
            $counter = 0;
            while ($row = mysqli_fetch_array($booking_agents)) {
                echo "<tr>";
                echo "<td>".$row['email']."</td>";
                echo "<td>".$row['total_commission']."</td>";
                echo "</tr>";
                $counter += 1;
                if ($counter ==  5) {
                    break;
                }
            }
            if ($counter < 5) {
                $diff = 5 - $counter;
                for ($i = 0; $i < $diff; $i++) {
                    echo "<tr>";
                    echo "<td>other agents</td>";
                    echo "<td>0</td>";
                    echo "</tr>";
                }
            }
            ?>         
        </table>
        <form action="" name="status_form" method="post">
            <input type="radio" name="booking_agent_choice" onclick="this.form.submit()" value="Past Month" <?php echo ($_SESSION['booking_agent_selection'] == 'Past Month')?'checked':'' ?>>Past Month
            <input type="radio" name="booking_agent_choice" onclick="this.form.submit()" value="Past Year" <?php echo ($_SESSION['booking_agent_selection'] == 'Past Year')?'checked':'' ?>>Past Year
        </form>

        <h3>Frequent Customers</h3>
        <table border="1">            
            <tbody>
                <th>customer_email</th>
                <th>num_tickets</th>
                <th>flights at this airline</th>
            </tbody>
                 
            <?php
            while ($row = mysqli_fetch_array($customers)) {
                $c_email = $row['customer_email'];
                $airline_name = $_SESSION['airline_name'];
                echo "<tr>";
                echo "<td>".$row['customer_email']."</td>";
                echo "<td>".$row['num_tickets']."</td>";
                echo "<td>";

                $customer_flights = mysqli_query($conn, "SELECT flight.flight_num 
                    FROM purchases NATURAL JOIN ticket NATURAL JOIN flight 
                    WHERE purchases.customer_email = '$c_email' 
                    AND purchases.ticket_id = ticket.ticket_id 
                    AND ticket.flight_num = flight.flight_num 
                    AND flight.airline_name = '$airline_name'");

                while ($row2 = mysqli_fetch_array($customer_flights)) {
                    echo $row2['flight_num'] . '<br>';
                }

                echo "</td>";
                echo "</tr>";
            }
            ?>         
        </table>

        
        <div style="width:40%; min-width:450px;"> 
            <?php echo $out; ?> 
        </div> 
        <h3>Reports</h3>
        <?php


        ?>
        
        <br>
        <a href="logout.php"><button name="logout"/>Log Out</button></a>

    </div>
    
    
    
    
 
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

</body>



</html>
