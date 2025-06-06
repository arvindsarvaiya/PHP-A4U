<?php
@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Your Orders</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .download-bill-btn {
         display: inline-block;
         margin-top: 1rem;
         padding: 0.8rem 1.5rem;
         border-radius: 0.5rem;
         background-color: #28a745;
         color: white;
         text-decoration: none;
         font-size: 1.4rem;
         transition: all 0.3s ease;
      }
      
      .download-bill-btn:hover {
         background-color: #218838;
         transform: translateY(-2px);
      }
      
      .download-bill-btn i {
         margin-right: 0.5rem;
      }
      
      .download-bill-btn.disabled {
         background-color: #6c757d;
         cursor: not-allowed;
         pointer-events: none;
      }
   </style>
</head>
<body>
   
<?php @include 'header.php'; ?>

<section class="heading">
    <h3>Your Orders</h3>
    <p><a href="home.php">Home</a> / Orders</p>
</section>

<section class="placed-orders">
    <h1 class="title">Placed Orders</h1>
    <div class="box-container">
    <?php
        $select_orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY placed_on DESC") or die('query failed');
        
        if(mysqli_num_rows($select_orders) > 0){
            while($fetch_orders = mysqli_fetch_assoc($select_orders)){
    ?>
    <div class="box">
        <p>Placed on: <span><?php echo $fetch_orders['placed_on']; ?></span></p>
        <p>Name: <span><?php echo $fetch_orders['name']; ?></span></p>
        <p>Number: <span><?php echo $fetch_orders['number']; ?></span></p>
        <p>Email: <span><?php echo $fetch_orders['email']; ?></span></p>
        <p>Address: <span><?php echo $fetch_orders['address']; ?></span></p>
        <p>Payment method: <span><?php echo $fetch_orders['method']; ?></span></p>
        <p>Your orders: <span><?php echo $fetch_orders['total_products']; ?></span></p>
        <p>Total price: <span>$<?php echo $fetch_orders['total_price']; ?>/-</span></p>
        <p>Payment status: 
            <span style="color:<?php echo ($fetch_orders['payment_status'] == 'pending') ? 'tomato' : 'green'; ?>">
                <?php echo $fetch_orders['payment_status']; ?>
            </span>
        </p>
        
        <?php if($fetch_orders['payment_status'] == 'completed'): ?>
            <a href="generate_pdf.php?order_id=<?php echo $fetch_orders['id']; ?>" class="download-bill-btn">
                <i class="fas fa-file-pdf"></i> Download Bill
            </a>
        <?php else: ?>
            <span class="download-bill-btn disabled">
                <i class="fas fa-file-pdf"></i> Download Available After Payment
            </span>
        <?php endif; ?>
    </div>
    <?php
            }
        } else {
            echo '<p class="empty">No orders placed yet!</p>';
        }
    ?>
    </div>
</section>

<?php @include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>