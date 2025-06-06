<?php

$conn = mysqli_connect('localhost','root','','shop_db');

if (!$conn) {
    die("DATABASE CONNECTION FAILED: " . mysqli_connect_error());
}
// Test query to verify connection works
$test = mysqli_query($conn, "SELECT 1");
if (!$test) {
    die("DATABASE TEST QUERY FAILED: " . mysqli_error($conn));
}
?>