<?php
session_start();
include 'config.php';
date_default_timezone_set('Asia/Kolkata');

// 1. VERIFY USER IS PROPERLY LOGGED IN
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'caretaker') {
    $_SESSION['message'] = 'You must login as a caretaker first';
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. CHECK IF USER EXISTS IN DATABASE
$user_check = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
if (mysqli_num_rows($user_check) == 0) {
    session_destroy(); // Clear invalid session
    $_SESSION['message'] = 'User account not found';
    header("Location: login.php");
    exit();
}

// 3. CHECK CARETAKER PROFILE EXISTS
$caretaker_query = mysqli_query($conn, "SELECT * FROM caretakers WHERE user_id = $user_id");
if (mysqli_num_rows($caretaker_query) == 0) {
    $_SESSION['message'] = 'Please complete your caretaker profile first';
    header("Location: create_caretaker_profile.php");
    exit();
}

$caretaker_data = mysqli_fetch_assoc($caretaker_query);

// 4. VERIFY CARETAKER IS APPROVED
if ($caretaker_data['is_approved'] != 1) {
    $_SESSION['message'] = 'Your caretaker application is pending approval';
    header("Location: home.php");
    exit();
}

// 5. SET CARETAKER ID FOR SESSION
$_SESSION['caretaker_id'] = $caretaker_data['caretaker_id'];
$caretaker_id = $caretaker_data['caretaker_id'];

// 6. UPDATE COMPLETED BOOKINGS STATUS
mysqli_query($conn, "
    UPDATE caretaker_bookings 
    SET status = 'completed',
        report_deadline = DATE_ADD(end_datetime, INTERVAL 24 HOUR)
    WHERE caretaker_id = '$caretaker_id'
    AND end_datetime <= NOW()
    AND status = 'pending'
");

// 7. GET BOOKINGS NEEDING REPORTS
$current_bookings = mysqli_query($conn, "
    SELECT b.*, u.name as client_name, u.email as client_email,
           NOW() > report_deadline as is_late
    FROM caretaker_bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.caretaker_id = '$caretaker_id'
    AND b.status = 'completed'
    AND (b.report_status = 'pending' OR b.report_status IS NULL)
    ORDER BY b.end_datetime ASC
");

// 8. PROCESS REPORT SUBMISSION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_report'])) {
    $booking_id = (int)$_POST['booking_id'];
    
    // Verify booking belongs to this caretaker
    $booking = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT *, NOW() > report_deadline as is_late
         FROM caretaker_bookings 
         WHERE booking_id = '$booking_id' 
         AND caretaker_id = '$caretaker_id'"));
    
    if ($booking && !$booking['is_late']) {
        // Handle file upload
        $upload_dir = 'uploads/reports/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $pdf_path = '';
        if ($_FILES['report_pdf']['error'] == UPLOAD_ERR_OK) {
            $file_ext = pathinfo($_FILES['report_pdf']['name'], PATHINFO_EXTENSION);
            if (strtolower($file_ext) == 'pdf') {
                $pdf_name = 'REPORT_'.$booking_id.'_'.time().'.pdf';
                $pdf_path = $upload_dir.$pdf_name;
                move_uploaded_file($_FILES['report_pdf']['tmp_name'], $pdf_path);
            } else {
                $error = "Only PDF files are allowed!";
            }
        } else {
            $error = "Please upload a PDF report";
        }
        
        if (!isset($error)) {
            mysqli_begin_transaction($conn);
            try {
                // Insert report
                mysqli_query($conn, "
                    INSERT INTO caretaker_reports (
                        booking_id, caretaker_id, client_name, client_address,
                        client_email, situation_before, situation_after, report_pdf
                    ) VALUES (
                        '$booking_id', '$caretaker_id', 
                        '".mysqli_real_escape_string($conn, $_POST['client_name'])."',
                        '".mysqli_real_escape_string($conn, $_POST['client_address'])."',
                        '".mysqli_real_escape_string($conn, $_POST['client_email'])."',
                        '".mysqli_real_escape_string($conn, $_POST['situation_before'])."',
                        '".mysqli_real_escape_string($conn, $_POST['situation_after'])."',
                        '$pdf_path'
                    )");
                
                // Update booking status
                mysqli_query($conn, "
                    UPDATE caretaker_bookings 
                    SET report_status = 'submitted'
                    WHERE booking_id = '$booking_id'");
                
                mysqli_commit($conn);
                $_SESSION['success'] = "Report submitted successfully!";
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } catch(Exception $e) {
                mysqli_rollback($conn);
                $error = "Failed to submit report: ".$e->getMessage();
            }
        }
    } else {
        $error = $booking ? "Deadline for this report has passed!" : "Invalid booking!";
    }
}

// Check for success message from redirect
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Caretaker Dashboard</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        h1 {
            color: #2c3e50;
            margin: 0;
        }
        .welcome-message {
            color: #7f8c8d;
            font-size: 1.1em;
        }
        .report-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
        }
        input[type="text"], 
        input[type="email"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        textarea {
            min-height: 120px;
        }
        .booking-info {
            background: #f1f8fe;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .deadline-message {
            color: #e74c3c;
            font-weight: bold;
            margin: 15px 0;
            padding: 10px;
            background: #fdeaea;
            border-radius: 4px;
        }
        .btn-submit {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-submit:hover {
            background: #2980b9;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .no-reports {
            text-align: center;
            padding: 50px 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .no-reports-icon {
            font-size: 50px;
            color: #bdc3c7;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div>
            <h1>Caretaker Dashboard</h1>
            <p class="welcome-message">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
        </div>
        <div>
            <a href="logout.php" style="color: #e74c3c;">Logout</a>
        </div>
    </div>
    
    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if(mysqli_num_rows($current_bookings) > 0): ?>
        <?php while($booking = mysqli_fetch_assoc($current_bookings)): ?>
            <div class="report-form <?= $booking['is_late'] ? 'disabled-form' : '' ?>">
                <div class="booking-info">
                    <h2>Report for: <?= htmlspecialchars($booking['client_name']) ?></h2>
                    <p><strong>Visit Date:</strong> <?= date('M j, Y', strtotime($booking['start_datetime'])) ?></p>
                    <p><strong>Completed At:</strong> <?= date('M j, Y g:i A', strtotime($booking['end_datetime'])) ?></p>
                </div>
                
                <?php if($booking['is_late']): ?>
                    <div class="deadline-message">
                        ⚠️ Report submission deadline passed on <?= date('M j, Y g:i A', strtotime($booking['report_deadline'])) ?>
                    </div>
                <?php else: ?>
                    <p><strong>Submission Deadline:</strong> <?= date('M j, Y g:i A', strtotime($booking['report_deadline'])) ?></p>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                        
                        <div class="form-group">
                            <label>Client Name</label>
                            <input type="text" name="client_name" value="<?= htmlspecialchars($booking['client_name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Client Address</label>
                            <input type="text" name="client_address" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Client Email</label>
                            <input type="email" name="client_email" value="<?= htmlspecialchars($booking['client_email']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Situation Before Visit</label>
                            <textarea name="situation_before" required placeholder="Describe the client's condition when you arrived"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Situation After Visit</label>
                            <textarea name="situation_after" required placeholder="Describe the client's condition when you left"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Upload Report (PDF only, max 5MB)</label>
                            <input type="file" name="report_pdf" accept=".pdf" required>
                        </div>
                        
                        <button type="submit" name="submit_report" class="btn-submit">Submit Report</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-reports">
            <div class="no-reports-icon">📋</div>
            <h3>No reports pending submission</h3>
            <p>You currently don't have any completed visits requiring reports.</p>
        </div>
    <?php endif; ?>
</body>
</html>