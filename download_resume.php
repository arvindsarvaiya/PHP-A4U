<?php
include 'config.php';
session_start();

if(!isset($_SESSION['user_id'])){
    die('<h2 style="color:red">Access Denied!</h2>');
}

if(isset($_GET['id'])) {
    $caretaker_id = (int)$_GET['id'];
    $query = mysqli_query($conn, 
        "SELECT resume_path FROM caretakers WHERE caretaker_id = $caretaker_id");
    
    if(mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $file_path = $data['resume_path'];
        
        if(file_exists($file_path)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="resume_'.$caretaker_id.'.pdf"');
            readfile($file_path);
            exit;
        } else {
            die('<h2 style="color:red">Resume file missing!</h2>');
        }
    }
}
die('<h2 style="color:red">Invalid request!</h2>');
?>