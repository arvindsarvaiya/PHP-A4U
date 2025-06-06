<?php 
include 'config.php';

session_start();

$admin_id = $_SESSION['user_id'];


if(isset($_POST['add_admin'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
   $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));

   $select_admin = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'") or die('query failed');

   if(mysqli_num_rows($select_admin) > 0){
      $message[] = 'Admin already exists!';
   }else{
      if($pass != $cpass){
         $message[] = 'Confirm password not matched!';
      }else{
         $insert_admin = mysqli_query($conn, "INSERT INTO users(name, email, password, user_type) VALUES('$name', '$email', '$cpass', 'admin')") or die('query failed');
         
         if($insert_admin){
            $message[] = 'New admin added successfully!';
            header('location:admin_users.php');
         }
      }
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php @include 'admin_header.php'; ?>

<section class="add-products">
   <form action="" method="POST">
      <h3>add new admin</h3>
      <input type="text" class="box" required placeholder="enter admin name" name="name">
      <input type="email" class="box" required placeholder="enter admin email" name="email">
      <input type="password" class="box" required placeholder="enter password" name="password">
      <input type="password" class="box" required placeholder="confirm password" name="cpassword">
      <input type="submit" value="add admin" name="add_admin" class="btn">
   </form>
</section>
<script src="js/admin_script.js"></script>
