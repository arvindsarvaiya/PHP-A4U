<?php
// Form for caretakers to apply
include 'header.php';

if(isset($_POST['apply_caretaker'])){
    // Process application (upload resume, save details)
    // Set is_approved = FALSE until admin approves
}

?>
<section class="add-products">
   <form action="" method="POST" enctype="multipart/form-data">
      <h3>Apply as Caretaker</h3>
      <input type="number" class="box" required placeholder="Hourly rate" name="hourly_rate" step="0.01">
      <select class="box" required name="specialization">
         <option value="">Select Specialization</option>
         <option value="elderly_care">Elderly Care</option>
         <option value="post_surgery">Post-Surgery Care</option>
         <!-- Add more options -->
      </select>
      <select class="box" required name="gender">
         <option value="">Select Gender</option>
         <option value="male">Male</option>
         <option value="female">Female</option>
         <option value="other">Other</option>
      </select>
      <input type="file" class="box" required name="resume" accept=".pdf,.doc,.docx">
      <input type="submit" value="Submit Application" name="apply_caretaker" class="btn">
   </form>
</section>
<?php include 'footer.php'; ?>