<?php

 session_start();
	if($_SESSION['username']) {
		echo "You have logged out";
		session_unset();
                session_destroy();
                //echo $_SESSION['username'];
                
		header("refresh:2; url=index.php");
	}
	else
		header("refresh:2; url=index.php");
        
        
      
 ?>
