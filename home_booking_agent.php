<?php
require('connect.php');
session_start();

echo "start: " .$_SESSION['start_date'];
echo "<br>end: " .$_SESSION['end_date'];

function showMyFlights($status) {
    $conn = $GLOBALS['conn'];
    $flights = mysqli_query($conn, "SELECT ticket.ticket_id, flight.*
    FROM booking_agent NATURAL JOIN purchases NATURAL JOIN ticket NATURAL JOIN flight
    WHERE '1' = purchases.booking_agent_id 
    AND purchases.ticket_id = ticket.ticket_id 
    AND ticket.flight_num = flight.flight_num 
    AND ticket.airline_name = flight.airline_name
    AND flight.status = '$status'");
    return $flights;
}

if (@$_POST['status_choice']) { 
    $_SESSION['status_selection'] = $_POST['status_choice'];
    // $flights = showMyFlights($_SESSION['status_selection']);
}

$flights = showMyFlights($_SESSION['status_selection']);

function showCommission($new_start_date, $new_end_date) {
    $conn = $GLOBALS['conn'];
    $booking_agent_id = $_SESSION['booking_agent_id'];
    $start_date = $new_start_date;
    $end_date = $new_end_date;

    $start_time = $start_date . ' 00:00:00';
    $end_time = $end_date . ' 11:59:59';

    $sql = "SELECT ticket.ticket_id, flight.*
            FROM booking_agent NATURAL JOIN purchases NATURAL JOIN ticket NATURAL JOIN flight
            WHERE '$booking_agent_id' = purchases.booking_agent_id 
            AND purchases.ticket_id = ticket.ticket_id 
            AND ticket.flight_num = flight.flight_num 
            AND ticket.airline_name = flight.airline_name
            AND flight.departure_time >= '$start_time'
            AND flight.departure_time <= '$end_time'
            ORDER BY flight.departure_time";

    $range_of_flights = mysqli_query($conn, $sql);
    return $range_of_flights;
}

if (@$_POST['view_commission']) {
    $_SESSION['start_date'] = $_POST['start_date'];
    $_SESSION['end_date'] = $_POST['end_date'];
}

$range_of_flights = showCommission($_SESSION['start_date'], $_SESSION['end_date']);

if (@$_POST['confirm_purchase']) {
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

        $flights = showMyFlights("Upcoming");
        // echo "<meta http-equiv='refresh' content='0'>";
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
<!DOCTYPE html>
<html >
<head>
	<title>Welcome <?php echo $_SESSION['username'];?></title>
    <style type="text/css">
        .ce, .cpb {
            visibility: hidden;
        }
    </style>

    <script type="text/javascript">
        function showPurchaseInput(purchaseNumber) {
            var ceId = "customer_email_" + purchaseNumber.toString();
            var ce = document.getElementById(ceId);
            ce.style.visibility = "visible";

            var cpbId = "confirm_purchase_button_" + purchaseNumber.toString();
            var cpb = document.getElementById(cpbId);
            cpb.style.visibility = "visible";

            var buttonId = "purchase_button_" + purchaseNumber.toString();
            var purchaseButton = document.getElementById(buttonId);
            purchaseButton.style.visibility = "hidden";
        }
    </script>
</head>

<body>
	<div class="form">
		<h1>Booking Agent Home</h1>
          
        <p>
        <?php 
        echo 'username: '.$_SESSION['username'];
        echo '<br>booking_agent_id: ' .$_SESSION['booking_agent_id'];

        ?>
        </p>

        <h3>My Flights</h3>
        <?php
        if (mysqli_num_rows($flights)) {
            echo '
            <table border="1">            
                <tbody>
                    <th>ticket_id</th>
                    <th>airline_name</th>
                    <th>flight_num</th>
                    <th>departure_airport</th>
                    <th>departure_time</th>
                    <th>arrival_airport</th>
                    <th>arrival_time</th>
                    <th>price</th>
                    <th>airplane_id</th>
                    <th>status</th>
                </tbody>';
        }
        else {
            echo "No flights with status: ". $_SESSION['status_selection'];
            echo "<br><br>";
        }    
        while ($row = mysqli_fetch_array($flights)) {
            echo "<tr>";
            echo "<td>".$row['ticket_id']."</td>";
            echo "<td>".$row['airline_name']."</td>";
            echo "<td>".$row['flight_num']."</td>";
            echo "<td>".$row['departure_airport']."</td>";
            echo "<td>".$row['departure_time']."</td>";
            echo "<td>".$row['arrival_airport']."</td>";
            echo "<td>".$row['arrival_time']."</td>";
            echo "<td>".$row['price']."</td>";
            echo "<td>".$row['airplane_id']."</td>";
            echo "<td>".$row['status']."</td>";
            echo "</tr>";
        }       
        ?>         
        </table>
        <form action="" name="status_form" method="post">
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="Upcoming" <?php echo ($_SESSION['status_selection'] == 'Upcoming')?'checked':'' ?>>Upcoming
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="In progress" <?php echo ($_SESSION['status_selection'] == 'In progress')?'checked':'' ?>>In progress
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="On time" <?php echo ($_SESSION['status_selection'] == 'On time')?'checked':'' ?>>On time
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="Delayed" <?php echo ($_SESSION['status_selection'] == 'Delayed')?'checked':'' ?>>Delayed
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="Completed" <?php echo ($_SESSION['status_selection'] == 'Completed')?'checked':'' ?>>Completed
            <input type="radio" name="status_choice" onclick="this.form.submit()" value="Cancelled" <?php echo ($_SESSION['status_selection'] == 'Cancelled')?'checked':'' ?>>Cancelled
        </form>

        <h3>Search for Flights</h3>
        <form action="" name="search_form" method="post">
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
            Date
            <input type="date" name="flight_date" placeholder="yyyy-mm-dd"/>
            <input type="submit" name="search_flights" value="search_flights"/>
        </form>
        
        <?php
        if (isset($search_flights_results)) {
            if (mysqli_num_rows($search_flights_results)) {
                echo '<h4>Results:</h4>';
                echo '<table border="1">            
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
                        echo "<td>".$row['airplane_id']."</td>";
                        echo "<td>".$row['status']."</td>";
                        echo '<td>';
                        if ($row['status'] != "Completed" && $row['status'] != "Cancelled"  && $row['status'] != "In progress") {
                            echo '<button id="purchase_button_'.$counter.'" onClick="showPurchaseInput('.$counter.')">Purchase</button>';
                        }
                        else {
                            echo 'Unavailable for purchase';
                        }
                        echo '
                        <form action="" name="purchase_form" method="post">    
                            <input class="ce" id="customer_email_'.$counter.'" type="text" name="customer_email" placeholder="Customer email"/>
                            <input class="cpb" id="confirm_purchase_button_'.$counter.'" type="submit" name="confirm_purchase" value="confirm_purchase"/>
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

        <h3>My Commission</h3>
        <form action="" name="commission_form" method="post">    
            Start Date <input type="date" name="start_date" placeholder="yyyy-mm-dd"/>
            End Date <input type="date" name="end_date" placeholder="yyyy-mm-dd"/>
            <input type="submit" name="view_commission" value="view_commission"/>
        </form>

        <?php
        if (isset($range_of_flights)) {
            if (mysqli_num_rows($range_of_flights)) {
                // echo '<h4>Results:</h4>';
                echo 'From ' . $_SESSION['start_date'] . ' to ' . $_SESSION['end_date'] . ':';
                echo '<table border="1">            
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
                    </tbody>';
                
                $tickets_sold = 0;
                $commission = 0;

                $counter = 0;
                while ($row = mysqli_fetch_array($range_of_flights)) {
                    $counter += 1;
                    echo "<tr>";
                    echo "<td>".$row['airline_name']."</td>";
                    echo "<td>".$row['flight_num']."</td>";
                    echo "<td>".$row['departure_airport']."</td>";
                    echo "<td>".$row['departure_time']."</td>";
                    echo "<td>".$row['arrival_airport']."</td>";
                    echo "<td>".$row['arrival_time']."</td>";
                    echo "<td>".$row['price']."</td>";
                    echo "<td>".$row['airplane_id']."</td>";
                    echo "<td>".$row['status']."</td>";
                    echo "</tr>";
                    $tickets_sold += 1;
                    $commission += $row['price'];
                }
                $commission *= 0.1;
                $commission_formatted = number_format($commission, 2, '.', '');
                echo '</table>';
                echo '
                Total number of tickets sold: ' . $tickets_sold . ' 
                <br>
                Total amount of commission received: $' . $commission_formatted . ' 
                ';
            }
            else {
                echo 'no results';
            }
        }
        ?> 

        
    
        <br>
        <br> 
        <a href="logout.php"><button class="button button-block" name="logout"/>Log Out</button></a>

    </div>

</body>

</html>
