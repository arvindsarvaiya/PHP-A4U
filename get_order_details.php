<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

@include 'config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Not logged in.']);
    exit();
}

if (isset($_GET['order_id'])) {
    $user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

    $select_order = mysqli_query($conn, "SELECT * FROM `orders` WHERE user_id = '$user_id' AND id = '$order_id'") or die('Query failed: ' . mysqli_error($conn));

    if (mysqli_num_rows($select_order) > 0) {
        $fetch_order = mysqli_fetch_assoc($select_order);
        echo json_encode($fetch_order);
    } else {
        echo json_encode(['error' => 'Order not found.']);
    }
} else {
    echo json_encode(['error' => 'Order ID not provided.']);
}

?>