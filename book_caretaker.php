<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])) {
    $_SESSION['login_redirect'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $caretaker_id = (int)$_POST['caretaker_id'];
    $user_id = (int)$_SESSION['user_id'];
    $hours = (int)$_POST['hours'];
    $hourly_rate = (float)$_POST['hourly_rate'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    
    // Calculate total price
    $total_price = $hourly_rate * $hours;
    
    // Create datetime objects
    $start_datetime = date('Y-m-d H:i:s', strtotime("$date $start_time"));
    $end_datetime = date('Y-m-d H:i:s', strtotime("$start_datetime + $hours hours"));
    
    // Check for conflicts
    $conflict_check = mysqli_query($conn, "
        SELECT * FROM caretaker_bookings 
        WHERE caretaker_id = $caretaker_id
        AND (
            (start_datetime <= '$end_datetime' AND end_datetime >= '$start_datetime')
        )
        AND status IN ('pending', 'confirmed')
    ");
    
    if(mysqli_num_rows($conflict_check) == 0) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // 1. Create booking
            $stmt = mysqli_prepare($conn, "
                INSERT INTO caretaker_bookings 
                (caretaker_id, user_id, start_datetime, end_datetime, hours, total_price, status)
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            mysqli_stmt_bind_param($stmt, "iissid", $caretaker_id, $user_id, $start_datetime, $end_datetime, $hours, $total_price);
            mysqli_stmt_execute($stmt);
            
            // 2. Add to cart (optional)
            // $booking_id = mysqli_insert_id($conn);
            // addToCart($booking_id, 'caretaker', $total_price);
            
            // Commit transaction
            mysqli_commit($conn);
            
            $_SESSION['booking_success'] = "Booking created successfully!";
            header("Location: checkout.php");
            exit();
            
        } catch(Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['booking_error'] = "Booking failed: " . $e->getMessage();
            header("Location: caretaker_details.php?id=$caretaker_id");
            exit();
        }
    } else {
        $_SESSION['booking_error'] = "Caretaker is not available for the selected time slot.";
        header("Location: caretaker_details.php?id=$caretaker_id");
        exit();
    }
} else {
    header("Location: caretakers.php");
    exit();
}