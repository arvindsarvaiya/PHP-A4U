<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

session_start();

// Debug log file
file_put_contents('booking_debug.log', "=== NEW REQUEST ===\n", FILE_APPEND);

// Database connection check
include 'config.php';
if(!$conn) {
    $error = "DB Connection Failed: " . mysqli_connect_error();
    file_put_contents('booking_debug.log', $error."\n", FILE_APPEND);
    die(json_encode(['status' => 'error', 'message' => $error]));
}

// Validate session
if(!isset($_SESSION['user_id'])) {
    $error = "No user session";
    file_put_contents('booking_debug.log', $error."\n", FILE_APPEND);
    die(json_encode(['status' => 'error', 'message' => 'Please login first']));
}

// Validate POST data
$required = ['caretaker_id', 'hours', 'date', 'start_time', 'hourly_rate'];
foreach($required as $field) {
    if(empty($_POST[$field])) {
        $error = "Missing $field";
        file_put_contents('booking_debug.log', $error."\n", FILE_APPEND);
        die(json_encode(['status' => 'error', 'message' => "All fields are required"]));
    }
}

// Process data
try {
    $caretaker_id = (int)$_POST['caretaker_id'];
    $user_id = (int)$_SESSION['user_id'];
    $hours = (int)$_POST['hours'];
    $hourly_rate = (float)$_POST['hourly_rate'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    
    // Debug: Log received data
    file_put_contents('booking_debug.log', "Data Received: ".print_r($_POST, true)."\n", FILE_APPEND);
    
    // Validate datetime
    $start_datetime = date('Y-m-d H:i:s', strtotime("$date $start_time"));
    $end_datetime = date('Y-m-d H:i:s', strtotime("$start_datetime + $hours hours"));
    
    if(strtotime($start_datetime) < time()) {
        throw new Exception("Cannot book in the past");
    }

    // Check availability
    $conflict_check = mysqli_query($conn, 
        "SELECT * FROM caretaker_bookings 
         WHERE caretaker_id = $caretaker_id
         AND (
            (start_datetime < '$end_datetime' AND end_datetime > '$start_datetime')
         )
         AND status IN ('pending', 'confirmed')"
    );

    if(!$conflict_check) {
        throw new Exception("Availability check failed: ".mysqli_error($conn));
    }

    // Start transaction
    mysqli_begin_transaction($conn);
    file_put_contents('booking_debug.log', "Transaction started\n", FILE_APPEND);

    // 1. Create booking
    $stmt = mysqli_prepare($conn,
        "INSERT INTO caretaker_bookings 
         (caretaker_id, user_id, start_datetime, end_datetime, hours, total_price, status)
         VALUES (?, ?, ?, ?, ?, ?, 'pending')"
    );
    $total_price = $hourly_rate * $hours;
    mysqli_stmt_bind_param($stmt, "iissid", $caretaker_id, $user_id, $start_datetime, $end_datetime, $hours, $total_price);
    
    if(!mysqli_stmt_execute($stmt)) {
        throw new Exception("Booking creation failed: ".mysqli_error($conn));
    }
    file_put_contents('booking_debug.log', "Booking created\n", FILE_APPEND);

    // 2. Add to cart
    $booking_id = mysqli_insert_id($conn);
    $cart_query = mysqli_query($conn,
        "INSERT INTO cart 
         (user_id, booking_id, item_type, price, quantity)
         VALUES ($user_id, $booking_id, 'caretaker', $total_price, 1)"
    );
    
    if(!$cart_query) {
        throw new Exception("Cart update failed: ".mysqli_error($conn));
    }
    file_put_contents('booking_debug.log', "Added to cart\n", FILE_APPEND);

    // Commit transaction
    mysqli_commit($conn);
    file_put_contents('booking_debug.log', "Transaction committed\n", FILE_APPEND);

    // Success response
    header("Location: checkout.php");
exit;

    

} catch(Exception $e) {
    mysqli_rollback($conn);
    file_put_contents('booking_debug.log', "ERROR: ".$e->getMessage()."\n", FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}
?>