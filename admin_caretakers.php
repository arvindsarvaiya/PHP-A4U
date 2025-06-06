<?php
include 'admin_header.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


// Initialize variables
$message = [];
$caretakers = null;

// Handle approval
if(isset($_POST['approve'])) {      
    $caretaker_id = (int)$_POST['caretaker_id'];

    mysqli_begin_transaction($conn);
    
    try {
        // Get caretaker details first (for email)
        $caretaker_query = mysqli_query($conn, 
            "SELECT c.*, u.email, u.name, u.id as user_id
             FROM caretakers c 
             JOIN users u ON c.user_id = u.id 
             WHERE c.caretaker_id = $caretaker_id");
             
        if(!$caretaker_query) {
            throw new Exception('Database error: ' . mysqli_error($conn));
        }
        
        $caretaker = mysqli_fetch_assoc($caretaker_query);

        if (!$caretaker) {
            throw new Exception('Caretaker not found.');
        }

        // Update caretaker approval status
        $update_caretaker = mysqli_query($conn, 
            "UPDATE caretakers SET is_approved = 1 WHERE caretaker_id = $caretaker_id");
            
        if(!$update_caretaker) {
            throw new Exception('Update failed: ' . mysqli_error($conn));
        }

        // Update user role
        $update_user = mysqli_query($conn, 
            "UPDATE users SET user_type = 'caretaker' WHERE id = {$caretaker['user_id']}");
            
        if(!$update_user) {
            throw new Exception('User update failed: ' . mysqli_error($conn));
        }

        mysqli_commit($conn);
        $_SESSION['message'] = 'Caretaker approved successfully!';

        // Send approval email
        sendCaretakerEmail(
            $caretaker['email'],
            $caretaker['name'],
            'approved',
            'Congratulations! Your caretaker application has been approved.'
        );
        
        // Refresh to prevent form resubmission
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'] = 'Approval failed: ' . $e->getMessage();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle rejection
if(isset($_POST['reject'])) {
    $caretaker_id = (int)$_POST['caretaker_id'];

    $caretaker_query = mysqli_query($conn, 
        "SELECT c.*, u.email, u.name, u.id as user_id 
         FROM caretakers c 
         JOIN users u ON c.user_id = u.id 
         WHERE c.caretaker_id = $caretaker_id");

    if (!$caretaker_query) {
        $_SESSION['message'] = 'Database error: ' . mysqli_error($conn);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    if (mysqli_num_rows($caretaker_query) > 0) {
        $caretaker = mysqli_fetch_assoc($caretaker_query);

        $delete = mysqli_query($conn, "DELETE FROM caretakers WHERE caretaker_id = $caretaker_id");

        if(!$delete) {
            $_SESSION['message'] = 'Rejection failed: ' . mysqli_error($conn);
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }

        // Optional: Update user table with rejection status
        $check_column = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'application_status'");
        if(mysqli_num_rows($check_column) > 0) {
            mysqli_query($conn, 
                "UPDATE users SET application_status = 'rejected' WHERE id = {$caretaker['user_id']}");
        }

        $_SESSION['message'] = 'Application rejected successfully!';

        // Send rejection email
        sendCaretakerEmail(
            $caretaker['email'],
            $caretaker['name'],
            'rejected',
            'We regret to inform you that your caretaker application has been rejected.'
        );
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['message'] = 'Rejection failed: Caretaker not found.';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch caretaker applications
$caretakers = mysqli_query($conn, "
    SELECT c.*, u.name, u.email 
    FROM caretakers c
    JOIN users u ON c.user_id = u.id
    WHERE c.is_approved = 0
    ORDER BY c.applied_at DESC
");

if(!$caretakers){
    die('Database error: '.mysqli_error($conn));
}

// Email sending function
function sendCaretakerEmail($to, $name, $type, $custom_message = '') {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings for Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Gmail SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'projectai43@gmail.com';  // Your Gmail
        $mail->Password   = 'fshk efkn zcko qfit';    // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL encryption
        $mail->Port       = 465;  // SSL port for Gmail
        
        // Recipients
        $mail->setFrom('projectai43@gmail.com', 'Caretaker System');
        $mail->addAddress($to, $name);
        
        // Content
        $mail->isHTML(true); // Set email format to HTML
        
        if($type == 'approved') {
            $mail->Subject = 'Your Caretaker Application Was Approved!';
            $body = "<h2>Dear $name,</h2>";
            $body .= "<p>We're pleased to inform you that your caretaker application has been <strong>approved</strong>!</p>";
            $body .= "<p>You can now login to your account and start accepting bookings from clients.</p>";
        } else {
            $mail->Subject = 'About Your Caretaker Application';
            $body = "<h2>Dear $name,</h2>";
            $body .= "<p>After careful consideration, we regret to inform you that your application couldn't be approved at this time.</p>";
        }
        
        $body .= "<p>Best regards,<br>The CareTeam</p>";
        $mail->Body = $body;
        
        $mail->send();
        error_log("Email sent successfully to $to");
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caretaker Applications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">
    <style>
        .caretaker-applications {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        .application-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .application-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .card-header {
            padding: 15px;
            background: #3498db;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        
        .app-date {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 15px;
        }
        
        .card-body p {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .approve-btn {
            background: #28a745;
            color: white;
        }
        
        .reject-btn {
            background: #dc3545;
            color: white;
        }
        
        .view-resume {
            background: #17a2b8;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
    </style>
</head>
<body>
    <section class="caretaker-applications">
        <h1>Caretaker Applications</h1>

        <?php if(isset($_SESSION['message'])): ?>
            <div class="message <?= strpos($_SESSION['message'], 'failed') !== false ? 'error' : '' ?>">
                <?= htmlspecialchars($_SESSION['message']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="application-list">
            <?php if(mysqli_num_rows($caretakers) > 0): ?>
                <?php while($app = mysqli_fetch_assoc($caretakers)): ?>
                    <div class="application-card">
                        <div class="card-header">
                            <h3><?= htmlspecialchars($app['name']) ?></h3>
                            <span class="app-date">Applied: <?= date('M d, Y', strtotime($app['applied_at'])) ?></span>
                        </div>

                        <div class="card-body">
                            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($app['email']) ?></p>
                            <p><i class="fas fa-user"></i> 
                                <?= !empty($app['age']) ? 'Age: '.$app['age'].' | ' : '' ?>
                                <?= !empty($app['gender']) ? ucfirst($app['gender']) : 'Gender not specified' ?>
                            </p>
                            <?php if(!empty($app['specialization'])): ?>
                                <p><i class="fas fa-star"></i> Specialization: <?= ucwords(str_replace('_', ' ', $app['specialization'])) ?></p>
                            <?php endif; ?>

                            <div class="action-buttons">
                                <form method="POST" class="approve-form">
                                    <input type="hidden" name="caretaker_id" value="<?= $app['caretaker_id'] ?>">
                                    <button type="submit" name="approve" class="btn approve-btn">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>

                                <form method="POST" class="reject-form">
                                    <input type="hidden" name="caretaker_id" value="<?= $app['caretaker_id'] ?>">
                                    <button type="submit" name="reject" class="btn reject-btn">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>

                                <?php if(!empty($app['resume_path'])): ?>
                                    <a href="download_resume.php?id=<?= $app['caretaker_id'] ?>" class="btn view-resume">
                                        <i class="fas fa-file-pdf"></i> Resume
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <p>No pending applications</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
    document.querySelectorAll('.reject-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if(!confirm('Are you sure you want to reject this application? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    document.querySelectorAll('.approve-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if(!confirm('Approve this caretaker application?')) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>
</html>