<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;

if(!isset($user_id)){
   header('location:login.php');
};

// Get available caretakers with their next available time
$query = "SELECT c.*, 
          MIN(b.end_datetime) AS next_available
          FROM caretakers c
          LEFT JOIN caretaker_bookings b ON c.caretaker_id = b.caretaker_id 
             AND b.end_datetime > NOW() 
             AND b.status IN ('pending', 'confirmed')
          WHERE c.is_approved = 1
          GROUP BY c.caretaker_id";
$caretakers = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Our Caretakers</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .booking-modal {
         display: none;
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(0,0,0,0.5);
         z-index: 1000;
         align-items: center;
         justify-content: center;
      }
      .booking-form {
         background: #fff;
         padding: 2rem;
         border-radius: .5rem;
         width: 90%;
         max-width: 500px;
         box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
      }
      .booking-form h3 {
         font-size: 2rem;
         color: #333;
         margin-bottom: 1.5rem;
         text-align: center;
      }
      .booking-form .input-group {
         margin-bottom: 1.5rem;
      }
      .booking-form label {
         display: block;
         margin-bottom: .5rem;
         font-size: 1.6rem;
         color: #666;
      }
      .booking-form input, 
      .booking-form select {
         width: 100%;
         padding: 1.2rem;
         font-size: 1.6rem;
         color: #333;
         background: #f7f7f7;
         border: 1px solid #ddd;
         border-radius: .5rem;
      }
   </style>
</head>
<body>
   
<?php @include 'header.php'; ?>

<section class="heading">
    <h3>our caretakers</h3>
    <p> <a href="home.php">home</a> / caretakers </p>
</section>

<section class="products">
   <h1 class="title">Available Caretakers</h1>
   <div class="box-container">
      <?php
         if(mysqli_num_rows($caretakers) > 0){
            while($fetch_caretaker = mysqli_fetch_assoc($caretakers)){
                $is_available = isCaretakerAvailable($fetch_caretaker['caretaker_id'], $conn);
      ?>
      <div class="box">
         <a href="caretaker_details.php?id=<?php echo $fetch_caretaker['caretaker_id']; ?>" class="fas fa-eye"></a>
         <div class="price">$<?php echo $fetch_caretaker['hourly_rate']; ?>/hr</div>
         <img src="uploads/caretaker_profiles/<?php echo $fetch_caretaker['profile_img']; ?>" alt="" class="image">
         <div class="name"><?php echo $fetch_caretaker['name']; ?></div>
         <div class="specialization"><?php echo ucfirst(str_replace('_', ' ', $fetch_caretaker['specialization'])); ?></div>
         
         <div class="availability" style="margin-bottom: 1rem;">
            <span style="background: <?php echo $is_available ? '#4CAF50' : '#F44336'; ?>; color: white; padding: 0.25rem 0.5rem; border-radius: 50px; font-size: 0.8rem;">
               <?php echo $is_available ? 'Available Now' : 'Booked Until '.date('M j, g:i a', strtotime($fetch_caretaker['next_available'])); ?>
            </span>
         </div>
         
         <button class="btn book-btn" 
                 data-caretaker-id="<?php echo $fetch_caretaker['caretaker_id']; ?>"
                 data-hourly-rate="<?php echo $fetch_caretaker['hourly_rate']; ?>"
                 <?php echo !$is_available ? 'disabled' : ''; ?>>
            Book Now
         </button>
      </div>
      <?php
            }
         }else{
            echo '<p class="empty">no caretakers available yet!</p>';
         }
      ?>
   </div>
</section>

<!-- Booking Modal -->
<div class="booking-modal" id="bookingModal">
   <div class="booking-form">
      <h3>Book Caretaker</h3>
      <form id="bookingForm" method="POST" action="process_booking.php">
         <input type="hidden" name="caretaker_id" id="modalCaretakerId">
         <input type="hidden" name="hourly_rate" id="modalHourlyRate">
         
         <div class="input-group">
            <label>Date</label>
            <input type="date" name="date" id="bookingDate" required min="<?php echo date('Y-m-d'); ?>">
         </div>
         
         <div class="input-group">
            <label>Start Time</label>
            <input type="time" name="start_time" id="startTime" required min="08:00" max="20:00">
         </div>
         
         <div class="input-group">
            <label>Hours Needed</label>
            <input type="number" name="hours" min="1" max="8" value="1" required>
         </div>
         
         <div class="input-group">
            <label>Total Estimate</label>
            <input type="text" id="totalEstimate" readonly>
         </div>
         
         <button type="submit" class="btn">Confirm Booking</button>
      </form>
   </div>
</div>

<?php @include 'footer.php'; ?>

<script>
// Booking Modal Functionality
document.querySelectorAll('.book-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const caretakerId = this.getAttribute('data-caretaker-id');
        const hourlyRate = this.getAttribute('data-hourly-rate');
        
        document.getElementById('modalCaretakerId').value = caretakerId;
        document.getElementById('modalHourlyRate').value = hourlyRate;
        document.getElementById('bookingModal').style.display = 'flex';
    });
});

// Close modal when clicking outside
document.getElementById('bookingModal').addEventListener('click', function(e) {
    if(e.target === this) {
        this.style.display = 'none';
    }
});

// Calculate total estimate
document.querySelectorAll('input[name="hours"], #modalHourlyRate').forEach(input => {
    input.addEventListener('input', calculateTotal);
});

function calculateTotal() {
    const hours = document.querySelector('input[name="hours"]').value || 0;
    const rate = document.getElementById('modalHourlyRate').value || 0;
    document.getElementById('totalEstimate').value = '$' + (hours * rate).toFixed(2);
}
</script>

</body>
</html>

<?php
function isCaretakerAvailable($caretaker_id, $conn) {
    $query = "SELECT COUNT(*) as count FROM caretaker_bookings 
             WHERE caretaker_id = $caretaker_id
             AND end_datetime > NOW() 
             AND status IN ('pending', 'confirmed')";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['count'] == 0;
}
?>