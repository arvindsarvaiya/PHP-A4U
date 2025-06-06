<?php
session_start();
include 'config.php';

// Initialize variables
$message = [];
$show_form = true;
$app_status = null;

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
   header('Location: login.php');
   exit;
}

// Check existing application
$check_query = mysqli_query($conn, "SELECT * FROM caretakers WHERE user_id = '{$_SESSION['user_id']}'");
if(mysqli_num_rows($check_query) > 0){
    $app_status = mysqli_fetch_assoc($check_query);
    $show_form = false;
}

// Handle form submission
if(isset($_POST['submit_application']) && $show_form){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $age = (int)$_POST['age'];
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $hourly_rate = (float)$_POST['hourly_rate'];
    $specialization = mysqli_real_escape_string($conn, $_POST['specialization']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $user_id = $_SESSION['user_id'];

    // Create upload directory if not exists
    $upload_dirs = [
        'uploads/caretaker_profiles',
        'uploads/caretaker_resumes'
    ];

    foreach ($upload_dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            file_put_contents("$dir/index.html", "");
            file_put_contents("$dir/.htaccess", "Deny from all");
        }
    }

    // Handle profile image upload
    $profile_img = 'default.jpg';
    if($_FILES['profile_img']['error'] === UPLOAD_ERR_OK){
        $img_ex = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
        $img_ex_lc = strtolower($img_ex);
        
        if(in_array($img_ex_lc, ['jpg', 'jpeg', 'png'])){
            $new_img_name = uniqid("CT-", true).'.'.$img_ex_lc;
            $img_upload_path = 'uploads/caretaker_profiles/'.$new_img_name;
            
            if(move_uploaded_file($_FILES['profile_img']['tmp_name'], $img_upload_path)){
                $profile_img = $new_img_name;
            } else {
                $message[] = 'Failed to upload profile image!';
            }
        } else {
            $message[] = 'Only JPG, JPEG, PNG images allowed for profile!';
        }
    }

    // Handle resume upload
    $resume_path = '';
    if($_FILES['resume']['error'] === UPLOAD_ERR_OK){
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if($finfo->file($_FILES['resume']['tmp_name']) == 'application/pdf'){
            $resume_name = 'RESUME_'.time().'_'.uniqid().'.pdf';
            $resume_path = 'uploads/caretaker_resumes/'.$resume_name;
            
            if(!move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)){
                $message[] = 'Failed to save resume file!';
            }
        } else {
            $message[] = 'Only PDF files are allowed for resumes!';
        }
    } else {
        $message[] = 'Resume upload error: '.$_FILES['resume']['error'];
    }

    // Only proceed if no errors
    if(empty($message)){
        $insert_query = "INSERT INTO caretakers 
            (user_id, name, age, hourly_rate, specialization, gender, description, profile_img, resume_path, is_approved, applied_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())";
        
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "isidsssss", 
            $user_id, $name, $age, $hourly_rate, $specialization, 
            $gender, $description, $profile_img, $resume_path);
        
        if(mysqli_stmt_execute($stmt)){
            $message[] = 'Application submitted successfully!';
            $show_form = false;
            $app_status = ['is_approved' => 0];
            
            // Refresh to show success message
            header("Refresh:0");
        } else {
            $message[] = 'Database error: '.mysqli_error($conn);
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
   <title>Apply as Caretaker</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .application-container {
         max-width: 800px;
         margin: 2rem auto;
         padding: 2rem;
         background: #fff;
         box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
         border-radius: .5rem;
      }
      
      .application-container h2 {
         font-size: 2.5rem;
         color: #333;
         margin-bottom: 1.5rem;
         text-align: center;
      }
      
      .application-form .input-group {
         margin-bottom: 1.5rem;
      }
      
      .application-form label {
         display: block;
         margin-bottom: .5rem;
         font-size: 1.6rem;
         color: #666;
      }
      
      .application-form input,
      .application-form select,
      .application-form textarea {
         width: 100%;
         padding: 1.2rem;
         font-size: 1.6rem;
         color: #333;
         background: #f7f7f7;
         border: 1px solid #ddd;
         border-radius: .5rem;
      }
      
      .application-form textarea {
         height: 15rem;
         resize: none;
      }
      
      .success-box, .info-box {
         text-align: center;
         padding: 3rem;
         border-radius: .5rem;
      }
      
      .success-box {
         background: #e6f7e6;
         border: 1px solid #4CAF50;
      }
      
      .info-box {
         background: #e6f2ff;
         border: 1px solid #2196F3;
      }
      
      .preview-img {
         width: 15rem;
         height: 15rem;
         object-fit: cover;
         border-radius: 50%;
         margin: 1rem auto;
         display: block;
         border: 1rem solid #eee;
      }
   </style>
</head>
<body>
   
<?php @include 'header.php'; ?>

<section class="heading">
    <h3>apply as caretaker</h3>
    <p> <a href="home.php">home</a> / apply </p>
</section>

<section class="application-container">
    <?php
    if(!empty($message)){
        foreach($message as $msg){
            echo '<div class="message">'.$msg.'</div>';
        }
    }

    if(isset($app_status['is_approved'])){
        if($app_status['is_approved'] == 1){
            echo '
            <div class="success-box">
                <h2>Congratulations!</h2>
                <p>You are now a caretaker.</p>
                <a href="caretaker/dashboard.php" class="btn">Go to Dashboard</a>
            </div>';
            $show_form = false;
        } else {
            echo '
            <div class="info-box">
                <h2>Application Submitted</h2>
                <p>Your application is under review.</p>
            </div>';
            $show_form = false;
        }
    }

    if($show_form): 
    ?>
    <div class="application-form">
        <h2>Become a Caretaker</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="input-group">
                <label>Profile Image</label>
                <input type="file" name="profile_img" accept="image/*" required>
                <div id="image-preview" style="display:none;">
                    <img src="" alt="Preview" class="preview-img" id="preview">
                </div>
            </div>
            
            <div class="input-group">
                <label>Age</label>
                <input type="number" name="age" min="18" max="70" required>
            </div>
            
            <div class="input-group">
                <label>Gender</label>
                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="input-group">
                <label>Expected Hourly Rate ($)</label>
                <input type="number" name="hourly_rate" min="0" step="0.01" required>
            </div>
            
            <div class="input-group">
                <label>Specialization</label>
                <select name="specialization" required>
                    <option value="elderly_care">Elderly Care</option>
                    <option value="child_care">Child Care</option>
                    <option value="medical">Medical Assistance</option>
                    <option value="general">General Care</option>
                </select>
            </div>
            
            <div class="input-group">
                <label>About You</label>
                <textarea name="description" placeholder="Tell us about your experience and skills..." required></textarea>
            </div>
            
            <div class="input-group">
                <label>Resume (PDF only, max 5MB)</label>
                <input type="file" name="resume" accept=".pdf" required>
            </div>
            
            <button type="submit" name="submit_application" class="btn">Submit Application</button>
        </form>
    </div>
    <?php endif; ?>
</section>

<script>
// Image preview functionality
document.querySelector('input[name="profile_img"]').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('image-preview');
    
    if(this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        
        reader.readAsDataURL(this.files[0]);
    }
});
</script>

<?php @include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>