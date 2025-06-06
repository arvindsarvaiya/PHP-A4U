<?php
session_start();
include 'config.php';

if(!isset($_GET['id'])) {
    header("Location: caretakers.php");
    exit();
}

$caretaker_id = (int)$_GET['id'];
$caretaker = mysqli_query($conn, "
    SELECT c.*, 
           GROUP_CONCAT(s.service_name) as services
    FROM caretakers c
    LEFT JOIN caretaker_services s ON c.caretaker_id = s.caretaker_id
    WHERE c.caretaker_id = $caretaker_id
")->fetch_assoc();

// Check availability
$is_available = true;
$bookings = mysqli_query($conn, "
    SELECT * FROM caretaker_bookings
    WHERE caretaker_id = $caretaker_id
    AND end_datetime > NOW()
    AND status IN ('pending', 'confirmed')
");

if(mysqli_num_rows($bookings) > 0) {
    $is_available = false;
    $next_available = mysqli_query($conn, "
        SELECT MIN(end_datetime) as next_available
        FROM caretaker_bookings
        WHERE caretaker_id = $caretaker_id
        AND end_datetime > NOW()
    ")->fetch_assoc()['next_available'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $caretaker['name'] ?> - Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <section class="caretaker-profile">
        <div class="profile-header">
            <img src="uploads/caretaker_profiles/<?= $caretaker['profile_img'] ?>" 
                 alt="img">
            <div>
                
                <div class="rating">★★★★☆ (4.2)</div>
                <div class="price">$<?= number_format($caretaker['hourly_rate'], 2) ?>/hr</div>
                <?php if(!$is_available): ?>
                    <div class="availability">
                        Next Available: <?= date('M j, g:i a', strtotime($next_available)) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="profile-details">
            <div class="about">
                <h2>About</h2>
                <p>Gender: <?= ucfirst($caretaker['gender']) ?></p>
                <p>Specialization: <?= ucfirst($caretaker['specialization']) ?></p>
                
                <?php if($caretaker['services']): ?>
                    <h3>Services Offered</h3>
                    <ul>
                        <?php foreach(explode(',', $caretaker['services']) as $service): ?>
                            <li><?= htmlspecialchars(trim($service)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <?php if($is_available): ?>
            <div class="booking-form">
                <h2>Book This Caretaker</h2>
                <form action="book_caretaker.php" method="POST">
                    <input type="hidden" name="caretaker_id" value="<?= $caretaker_id ?>">
                    <input type="hidden" name="hourly_rate" value="<?= $caretaker['hourly_rate'] ?>">
                    
                    <label>Date</label>
                    <input type="date" name="date" min="<?= date('Y-m-d') ?>" required>
                    
                    <label>Start Time</label>
                    <input type="time" name="start_time" min="08:00" max="20:00" required>
                    
                    <label>Hours Needed</label>
                    <select name="hours" required>
                        <option value="1">1 hour</option>
                        <option value="2">2 hours</option>
                        <option value="3">3 hours</option>
                        <option value="4">4 hours</option>
                        <option value="5">5 hours</option>
                    </select>
                    
                    <div class="price-preview">
                        Estimated Total: $<span id="total-price"><?= number_format($caretaker['hourly_rate'], 2) ?></span>
                    </div>
                    
                    <button type="submit" class="btn">Confirm Booking</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </section>