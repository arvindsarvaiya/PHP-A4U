<?php
session_start();

include 'admin_header.php';
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


@include 'config.php'; // Include your database connection

// Function to generate a random OTP
function generateOTP($length = 6) {
    $characters = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $otp;
}

$error = "";
$stage = $_SESSION['forgot_password_stage'] ?? 1;
$email = $_SESSION['forgot_password_email'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['start_forgot_password'])) {
        // User clicked a link to initiate forgot password
        unset($_SESSION['forgot_password_stage']);
        unset($_SESSION['forgot_password_otp']);
        unset($_SESSION['forgot_password_email']);
        unset($_SESSION['otp_sent_time']);
        unset($_SESSION['original_password_hash']);
        $stage = 1; // Reset to the initial email input stage
    } elseif ($stage == 1 && isset($_POST['send_otp'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Check if email exists in the database
            $check_email_query = mysqli_query($conn, "SELECT email, password FROM `users` WHERE email = '$email'") or die('query failed');
            if (mysqli_num_rows($check_email_query) == 0) {
                $error = "Email not found.";
                $stage = 1;
            } else {
                $row = mysqli_fetch_assoc($check_email_query);
                $original_password_hash = $row['password']; // Get the original hashed password

                $otp = generateOTP();
                $_SESSION['forgot_password_otp'] = $otp;
                $_SESSION['forgot_password_email'] = $email;
                $_SESSION['forgot_password_stage'] = 2;
                $_SESSION['otp_sent_time'] = time(); // Record when OTP was sent
                $_SESSION['original_password_hash'] = $original_password_hash; // Store the original password hash

                $mail = new PHPMailer(true);

                try {
                    //Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'karinapatel46741@gmail.com';
                    $mail->Password   = 'mdih btjb mipk lgnm';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;

                    //Recipients
                    $mail->setFrom('karinapatel46741@gmail.com', 'A4U');
                    $mail->addAddress($email, 'User');

                    //Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Password Reset OTP';
                    $mail->Body    = 'Your OTP for password reset is: <b>' . $otp . '</b>';

                    $mail->send();
                    echo '<script>alert("OTP has been sent to your email address.");</script>';
                    $stage = 2; // Move to the OTP verification stage
                } catch (Exception $e) {
                    echo "<script>alert('Failed to send OTP. Mailer Error: {$mail->ErrorInfo}');</script>";
                    $error = "Failed to send OTP. Please try again.";
                    $stage = 1; // Stay on the email input stage
                }
            }
        } else {
            $error = "Invalid email format.";
        }
    } elseif ($stage == 2) {
        if (isset($_POST['verify_otp'])) {
            $otp_entered = $_POST['otp'];
            if (isset($_SESSION['forgot_password_otp']) && $otp_entered == $_SESSION['forgot_password_otp']) {
                $_SESSION['forgot_password_stage'] = 3; // Move to the new password input stage
                $stage = 3;
            } else {
                $error = "Invalid OTP.";
            }
        } elseif (isset($_POST['resend_otp'])) {
            // Implement OTP resend logic
            $resend_delay = 30; // seconds
            if (isset($_SESSION['otp_sent_time']) && (time() - $_SESSION['otp_sent_time']) < $resend_delay) {
                $error = "Please wait before requesting a new OTP.";
            } else {
                $new_otp = generateOTP();
                $_SESSION['forgot_password_otp'] = $new_otp;
                $_SESSION['otp_sent_time'] = time(); // Update the sent time

                $mail = new PHPMailer(true);

                try {
                    //Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'karinapatel46741@gmail.com';
                    $mail->Password   = 'mdih btjb mipk lgnm';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;

                    //Recipients
                    $mail->setFrom('karinapatel46741@gmail.com', 'A4U');
                    $mail->addAddress($_SESSION['forgot_password_email'], 'User');

                    //Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Password Reset OTP (Resend)';
                    $mail->Body    = 'Your new OTP for password reset is: <b>' . $new_otp . '</b>';

                    $mail->send();
                    echo '<script>alert("New OTP has been sent to your email address.");</script>';
                    $error = "New OTP sent."; // Optionally provide feedback
                } catch (Exception $e) {
                    echo "<script>alert('Failed to resend OTP. Mailer Error: {$mail->ErrorInfo}');</script>";
                    $error = "Failed to resend OTP. Please try again.";
                }
            }
        }
    } elseif ($stage == 3 && isset($_POST['update_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password == $confirm_password) {
            $hashed_new_password = md5($new_password); // Remember the security warning about md5

            if (isset($_SESSION['forgot_password_email'])) {
                $email_to_update = $_SESSION['forgot_password_email'];
                $update_password_query = mysqli_query($conn, "UPDATE `users` SET password = '$hashed_new_password' WHERE email = '$email_to_update'") or die('query failed');

                if ($update_password_query) {
                    echo "<script>alert('Password has been updated successfully.'); window.location.href = 'login.php';</script>";
                    unset($_SESSION['forgot_password_stage']);
                    unset($_SESSION['forgot_password_otp']);
                    unset($_SESSION['forgot_password_email']);
                    unset($_SESSION['otp_sent_time']);
                    unset($_SESSION['original_password_hash']);
                    exit();
                } else {
                    $error = "Failed to update password.";
                }
            } else {
                $error = "Session error. Please try again.";
            }
        } else {
            $error = "New password and confirm password do not match.";
        }
    } elseif ($stage == 4 && isset($_POST['reset_original_password'])) {
        // Directly reset to the original password and redirect to login
        if (isset($_SESSION['forgot_password_email']) && isset($_SESSION['original_password_hash'])) {
            $email_to_reset = $_SESSION['forgot_password_email'];
            $original_password = $_SESSION['original_password_hash'];

            $update_password_query = mysqli_query($conn, "UPDATE `users` SET password = '$original_password' WHERE email = '$email_to_reset'") or die('query failed');

            if ($update_password_query) {
                echo "<script>alert('Password has been reset to the original password.'); window.location.href = 'login.php';</script>";
                unset($_SESSION['forgot_password_stage']);
                unset($_SESSION['forgot_password_otp']);
                unset($_SESSION['forgot_password_email']);
                unset($_SESSION['otp_sent_time']);
                unset($_SESSION['original_password_hash']);
                exit();
            } else {
                $error = "Failed to reset password.";
            }
        } else {
            $error = "Session error. Please try again.";
        }
    }
}

// Reset session if the user navigates to the page directly
if ($stage > 1 && $_SERVER["REQUEST_METHOD"] !== "POST") {
    unset($_SESSION['forgot_password_stage']);
    unset($_SESSION['forgot_password_otp']);
    unset($_SESSION['forgot_password_email']);
    unset($_SESSION['otp_sent_time']);
    unset($_SESSION['original_password_hash']);
    $stage = 1;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">
    <style>
        body {
            font-family: sans-serif;
    
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
            text-align: center; /* Center align text within the body */
        }
        .container {
            background-color: #fff;
            margin-top: 100px;
            margin-left: 425px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px; /* Adjust width as needed */
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            text-align: left; /* Align labels to the left within the form */
        }
        input[type="email"],
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .otp-actions button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        .otp-actions button:hover {
            background-color: #0056b3;
        }
        button {
            background-color: #28a745; /* Different color for main action */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px; /* Add some space above the buttons */
        }
        button:hover {
            background-color: #1e7e34;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .start-forgot-password {
            margin-bottom: 20px;
        }
        .start-forgot-password a {
            text-decoration: none;
            color: #007bff;
        }
        .start-forgot-password a:hover {
            text-decoration: underline;
        }
        p a {
            text-decoration: none;
            color: #007bff;
        }
        p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if ($stage == 1): ?>
            <form method="post">
                <div class="form-group">
                    <label for="email">Enter your email address:</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>
                </div>
                <button type="submit" name="send_otp">Send OTP</button>
            </form>
        <?php elseif ($stage == 2): ?>
            <form method="post">
                <div class="form-group">
                    <label for="otp">Enter the OTP sent to your email:</label>
                    <input type="text" id="otp" name="otp" required>
                </div>
                <div class="otp-actions">
                    <button type="submit" name="verify_otp">Verify OTP</button>
                    <button type="submit" name="resend_otp">Resend OTP</button>
                </div>
            </form>
        <?php elseif ($stage == 3): ?>
            <form method="post">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="update_password">Update Password</button>
                <!-- <button type="submit" name="reset_original_password" style="background-color: #dc3545;">Reset to Original Password</button>
                 -->
            </form>
        <?php else: ?>
            <div class="start-forgot-password">
                <p><a href="forgot_password.php" onclick="document.forms['startForgotPasswordForm'].submit(); return false;">Start Forgot Password Process</a></p>
                <form id="startForgotPasswordForm" method="post">
                    <input type="hidden" name="start_forgot_password" value="true">
                </form>
            </div>
        <?php endif; ?>
        <p><a href="login.php">Back to Login</a></p>
    </div>
</body>
</html>
