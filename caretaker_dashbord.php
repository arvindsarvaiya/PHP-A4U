<?php
session_start();
if($_SESSION['user_type'] != 'caretaker'){
   header('location:login.php');
}

// Fetch caretaker's bookings and availability
?>
<!-- Display caretaker's schedule, bookings, and report submission form -->