<?php
session_start();
include 'config.php';

$message = []; // Initialize message array

if(isset($_POST['submit'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']); // Consider using password_hash() in production

    // First check if user exists
    $select = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    
    if(!$select){
        die('Query failed: '.mysqli_error($conn));
    }

    if(mysqli_num_rows($select) > 0){
        $user = mysqli_fetch_assoc($select);
        
        // Verify password (using md5 for now - upgrade to password_verify() later)
        if($user['password'] == $password){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['user_type']; // Make sure this exists in your users table

            // Redirect based on user type
            switch($user['user_type']){
                case 'admin':
                    header('Location: admin_page.php');
                    break;
                    
                case 'caretaker':
                    // Additional check for caretaker approval
                    $caretaker_check = mysqli_query($conn, 
                        "SELECT is_approved FROM caretakers WHERE user_id = ".$user['id']
                    );
                    
                    if(mysqli_num_rows($caretaker_check) > 0){
                        $caretaker = mysqli_fetch_assoc($caretaker_check);
                        if($caretaker['is_approved'] == 1){
                            header('Location: caretaker_dashboard.php');
                        } else {
                            $message[] = 'Your caretaker application is pending approval';
                            header('Location: home.php');
                        }
                    } else {
                        $message[] = 'Caretaker record not found';
                        header('Location: home.php');
                    }
                    break;
                    
                case 'user':
                default:
                    header('Location: home.php');
            }
            exit();
        } else {
            $message[] = 'Incorrect email or password!';
        }
    } else {
        $message[] = 'User not found!';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
    if(!empty($message)){
        foreach($message as $msg){
            echo '<div class="message">'.htmlspecialchars($msg).'</div>';
        }
    }
    ?>
    
    <section class="form-container">
        <form action="" method="post">
            <h3>Login Now</h3>
            <input type="email" name="email" required placeholder="Enter your email" class="box">
            <input type="password" name="password" required placeholder="Enter your password" class="box">
            <input type="submit" name="submit" class="btn" value="Login Now">
            <p><a href="forgotpass.php">Forgot Password?</a></p>
            <p>Don't have an account? <a href="register.php">Register Now</a></p>
        </form>
    </section>
</body>
</html>