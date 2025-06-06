<?php
session_start();
include 'config.php';

// Verify admin authentication
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get export format
$format = isset($_GET['format']) && in_array($_GET['format'], ['excel', 'csv']) 
          ? $_GET['format'] : 'excel';

// Apply the same filters as in admin_reports.php
$where = [];
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where[] = "(u.name LIKE '%$search%' OR c.name LIKE '%$search%')";
}
// [Add other filters as in admin_reports.php]

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
    SELECT r.report_id,
           u.name as client_name,
           c.name as caretaker_name,
           b.start_datetime as visit_date,
           r.situation_before,
           r.situation_after,
           r.submitted_at as report_date

    FROM caretaker_reports r
    JOIN caretaker_bookings b ON r.booking_id = b.booking_id
    JOIN users u ON b.user_id = u.id
    JOIN caretakers ct ON r.caretaker_id = ct.caretaker_id
    JOIN users c ON ct.user_id = c.id
    $where_clause
    ORDER BY r.submitted_at DESC
";

$result = mysqli_query($conn, $query);

// Set headers based on format
if ($format == 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=caretaker_reports_'.date('Y-m-d').'.xls');
} else {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=caretaker_reports_'.date('Y-m-d').'.csv');
}

// Output header row
$headers = [
    'Report ID', 'Client Name', 'Caretaker Name', 
    'Visit Date', 'Situation Before', 'Situation After', 'Report Date'
];

if ($format == 'excel') {
    echo '<table><tr>';
    foreach ($headers as $header) {
        echo '<th>'.htmlspecialchars($header).'</th>';
    }
    echo '</tr>';
} else {
    echo '"'.implode('","', $headers).'"'."\n";
}

// Output data rows
while ($row = mysqli_fetch_assoc($result)) {
    if ($format == 'excel') {
        echo '<tr>';
        foreach ($row as $value) {
            echo '<td>'.htmlspecialchars($value).'</td>';
        }
        echo '</tr>';
    } else {
        echo '"'.implode('","', array_map(function($value) {
            return str_replace('"', '""', $value);
        }, $row)).'"'."\n";
    }
}

if ($format == 'excel') {
    echo '</table>';
}
exit();