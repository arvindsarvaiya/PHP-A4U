<?php

require 'vendor/autoload.php'; // Adjust this path to your mPDF autoloader
require_once 'config.php'; // Include your database connection

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Access Denied"); // Or redirect
}

if (isset($_GET['order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
    $user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);

    $select_order = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id' AND user_id = '$user_id'") or die('query failed');

    if (mysqli_num_rows($select_order) > 0) {
        $order = mysqli_fetch_assoc($select_order);

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->SetTitle('Order Bill #' . $order['id']);

        $html = '<h1>Order Bill #' . htmlspecialchars($order['id']) . '</h1>';
        $html .= '<p><strong>Placed On:</strong> ' . htmlspecialchars($order['placed_on']) . '</p>';
        $html .= '<p><strong>Name:</strong> ' . htmlspecialchars($order['name']) . '</p>';
        $html .= '<p><strong>Email:</strong> ' . htmlspecialchars($order['email']) . '</p>';
        $html .= '<p><strong>Address:</strong> ' . nl2br(htmlspecialchars($order['address'])) . '</p>';
        $html .= '<p><strong>Payment Method:</strong> ' . htmlspecialchars($order['method']) . '</p>';
        $html .= '<h2>Order Items:</h2>';
        $product_list = explode(',', trim($order['total_products'], ','));
        $html .= '<ul>';
        foreach ($product_list as $product) {
            $html .= '<li>' . htmlspecialchars(trim($product)) . '</li>';
        }
        $html .= '</ul>';
        $html .= '<h2>Total Price: $' . htmlspecialchars($order['total_price']) . '/-</h2>';
        $html .= '<p><strong>Payment Status:</strong> ' . htmlspecialchars($order['payment_status']) . '</p>';

        $mpdf->WriteHTML($html);
        $mpdf->Output('bill_order_' . $order['id'] . '.pdf', 'D'); // 'D' forces download

    } else {
        echo "Order not found.";
    }
} else {
    echo "Order ID not provided.";
}

?>