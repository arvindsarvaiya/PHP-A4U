<?php
session_start();
include 'config.php';



$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10; // Reports per page
$offset = ($page - 1) * $per_page;


$where = [];
$query_params = [];

// Search by client or caretaker name
if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where[] = "(u.name LIKE '%$search%' OR c.name LIKE '%$search%')";
    $query_params['search'] = $_GET['search'];
}

// Filter by date range
if (!empty($_GET['date_from'])) {
    $date_from = mysqli_real_escape_string($conn, $_GET['date_from']);
    $where[] = "b.start_datetime >= '$date_from 00:00:00'";
    $query_params['date_from'] = $_GET['date_from'];
}

if (!empty($_GET['date_to'])) {
    $date_to = mysqli_real_escape_string($conn, $_GET['date_to']);
    $where[] = "b.start_datetime <= '$date_to 23:59:59'";
    $query_params['date_to'] = $_GET['date_to'];
}

// Filter by caretaker
if (!empty($_GET['caretaker_id'])) {
    $caretaker_id = (int)$_GET['caretaker_id'];
    $where[] = "r.caretaker_id = $caretaker_id";
    $query_params['caretaker_id'] = $caretaker_id;
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';


$count_query = "SELECT COUNT(*) as total FROM caretaker_reports r
                JOIN caretaker_bookings b ON r.booking_id = b.booking_id
                JOIN users u ON b.user_id = u.id
                JOIN caretakers ct ON r.caretaker_id = ct.caretaker_id
                JOIN users c ON ct.user_id = c.id
                $where_clause";

$total_result = mysqli_query($conn, $count_query);
$total = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total / $per_page);


$reports_query = "
    SELECT r.*, 
           u.name as client_name,
           c.name as caretaker_name,
           ct.caretaker_id,
           b.start_datetime,
           b.end_datetime
    FROM caretaker_reports r
    JOIN caretaker_bookings b ON r.booking_id = b.booking_id
    JOIN users u ON b.user_id = u.id
    JOIN caretakers ct ON r.caretaker_id = ct.caretaker_id
    JOIN users c ON ct.user_id = c.id
    $where_clause
    ORDER BY r.submitted_at DESC

    LIMIT $offset, $per_page
";

$reports = mysqli_query($conn, $reports_query);

// Get all caretakers for filter dropdown
$caretakers_query = "SELECT ct.caretaker_id, u.name 
                     FROM caretakers ct
                     JOIN users u ON ct.user_id = u.id
                     ORDER BY u.name";
$caretakers = mysqli_query($conn, $caretakers_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Caretaker Reports</title>
   
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fa;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #6921d4;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px #6921d4;
        }
        .search-filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        .filter-group {
            margin-bottom: 0;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #34495e;
        }
        .filter-group input, .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-filter {
            background: #6921d4;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-reset {
            color: #34495e;
            margin-left: 10px;
            text-decoration: none;
        }
        .report-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .report-title {
            font-size: 1.2em;
            color: #6921d4;
            margin: 0;
        }
        .report-date {
            color: #7f8c8d;
        }
        .report-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .detail-group label {
            display: block;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 5px;
        }
        .detail-group p {
            margin: 0;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .actions {
            margin-top: 15px;
        }
        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-view {
            background: #6921d4;
            color: white;
        }
        .btn-view:hover {
            background: #79adf7;
        }
        .no-reports {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .export-options {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .export-options h3 {
            margin-top: 0;
            color: #6921d4;
        }
        .btn-export {
            display: inline-block;
            padding: 8px 15px;
            background: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
        .btn-export:hover {
            background: #219653;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
            padding: 20px 0;
        }
        .pagination .btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #6921d4;
        }
        .pagination .btn:hover {
            background: #f1f8fe;
        }
        .pagination .btn.active {
            background: #79adf7;
            color: white;
            border-color: #79adf7;
        }
        .results-count {
            color: #7f8c8d;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Caretaker Reports</h1>
        
        <!-- ============================================= -->
        <!-- SEARCH/FILTER FORM -->
        <!-- ============================================= -->
        <div class="search-filters">
            <form method="GET" action="">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="search">Search:</label>
                        <input type="text" name="search" id="search" 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                               placeholder="Client or Caretaker name">
                    </div>
                    
                    <div class="filter-group">
                        <label for="caretaker_id">Caretaker:</label>
                        <select name="caretaker_id" id="caretaker_id">
                            <option value="">All Caretakers</option>
                            <?php while($caretaker = mysqli_fetch_assoc($caretakers)): ?>
                                <option value="<?= $caretaker['caretaker_id'] ?>"
                                    <?= isset($_GET['caretaker_id']) && $_GET['caretaker_id'] == $caretaker['caretaker_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($caretaker['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_from">From Date:</label>
                        <input type="date" name="date_from" id="date_from"
                               value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_to">To Date:</label>
                        <input type="date" name="date_to" id="date_to"
                               value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn-filter">Apply Filters</button>
                        <a href="admin_reports.php" class="btn-reset">Reset</a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="results-count">
            Showing <?= ($offset + 1) ?>-<?= min($offset + $per_page, $total) ?> of <?= $total ?> reports
        </div>
        
        <!-- ============================================= -->
        <!-- EXPORT OPTIONS -->
        <!-- ============================================= -->
        <div class="export-options">
            <h3>Export Reports:</h3>
            <a href="export_reports.php?format=excel&<?= http_build_query($query_params) ?>" class="btn-export">
                Export to Excel
            </a>
            <a href="export_reports.php?format=csv&<?= http_build_query($query_params) ?>" class="btn-export">
                Export to CSV
            </a>
        </div>
        
        <!-- ============================================= -->
        <!-- REPORTS LIST -->
        <!-- ============================================= -->
        <?php if(mysqli_num_rows($reports) > 0): ?>
            <?php while($report = mysqli_fetch_assoc($reports)): ?>
                <div class="report-card">
                    <div class="report-header">
                        <h2 class="report-title">
                            Report #<?= $report['report_id'] ?> - <?= htmlspecialchars($report['client_name']) ?>
                        </h2>
                        <div class="report-date">
                            Submitted on <?= date('M j, Y g:i A', strtotime($report['submitted_at'])) ?>
                        </div>
                    </div>
                    
                    <div class="report-details">
                        <div class="detail-group">
                            <label>Caretaker</label>
                            <p><?= htmlspecialchars($report['caretaker_name']) ?></p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Visit Date</label>
                            <p><?= date('M j, Y', strtotime($report['start_datetime'])) ?></p>
                        </div>
                        
                        <div class="detail-group">
                            <label>Visit Duration</label>
                            <p>
                                <?= date('g:i A', strtotime($report['start_datetime'])) ?> - 
                                <?= date('g:i A', strtotime($report['end_datetime'])) ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="detail-group">
                        <label>Situation Before Visit</label>
                        <p><?= nl2br(htmlspecialchars($report['situation_before'])) ?></p>
                    </div>
                    
                    <div class="detail-group">
                        <label>Situation After Visit</label>
                        <p><?= nl2br(htmlspecialchars($report['situation_after'])) ?></p>
                    </div>
                    
                    <div class="actions">
                        <a href="<?= $report['report_pdf'] ?>" class="btn btn-view" target="_blank">
                            View PDF Report
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-reports">
                <h3>No reports found</h3>
                <p>There are no reports matching your criteria.</p>
            </div>
        <?php endif; ?>
        
        <!-- ============================================= -->
        <!-- PAGINATION -->
        <!-- ============================================= -->
        <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($query_params, ['page' => $page - 1])) ?>" class="btn">&laquo; Previous</a>
                <?php endif; ?>
                
                <?php 
                // Show page numbers (shows 2 pages before and after current)
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <a href="?<?= http_build_query(array_merge($query_params, ['page' => $i])) ?>" 
                       class="btn <?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($query_params, ['page' => $page + 1])) ?>" class="btn">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>