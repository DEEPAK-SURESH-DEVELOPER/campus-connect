<?php
include("../Includes/TeacherHeader.php");

if(!isset($_SESSION['teacher_id']) || empty($_SESSION['is_class_teacher']) || !$_SESSION['is_class_teacher']){
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/TeacherSidebar.php");
$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];
$class_id = $_SESSION['class_id'];

// --- Fetch class name ---
$classRes = $con->query("SELECT class_name FROM tbl_class WHERE class_id='$class_id'");
$classRow = $classRes ? $classRes->fetch_assoc() : null;
$class_name = $classRow ? $classRow['class_name'] : '';

$err = $success = "";

// --- Handle form submission ---
if(isset($_POST['btn_register'])){
    $name = mysqli_real_escape_string($con, $_POST['student_name']);
    $email = mysqli_real_escape_string($con, $_POST['student_email']);
    $contact = mysqli_real_escape_string($con, $_POST['student_contact']);
    $address = mysqli_real_escape_string($con, $_POST['student_address']);
    $gender = $_POST['student_gender'];
    $password = mysqli_real_escape_string($con, $_POST['student_password']);

    $photoName = null;

    // Handle photo upload
    if(isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] == 0){
        $allowed = ['jpg','jpeg','png','gif'];
        $fileName = $_FILES['student_photo']['name'];
        $fileTmp = $_FILES['student_photo']['tmp_name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if(in_array($ext, $allowed)){
            $photoName = "student_".time().".".$ext;
            $uploadPath = "../Assets/Files/Student/".$photoName;

            if(!move_uploaded_file($fileTmp, $uploadPath)){
                $err = "Error uploading student photo!";
            }
        } else {
            $err = "Invalid file type! Only JPG, PNG, or GIF allowed.";
        }
    }

    // Insert student if no error
    if(!$err){
        $insertQry = "INSERT INTO tbl_student (student_name, student_email, student_contact, student_address, student_gender, student_password, class_id, student_photo)
                      VALUES ('$name', '$email', '$contact', '$address', '$gender', '$password', '$class_id', ".($photoName ? "'$photoName'" : "NULL").")";
        if(mysqli_query($con, $insertQry)){
            $success = "🎉 Student registered successfully!";
        } else {
            $err = "Registration failed! Email might already exist.";
        }
    }
}

$page_title = "Register Student";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Register Student</span>';

?>

<div class="main-content">
  <div class="glass-card fade-in">
      <div class="card-header">
          <h3 class="card-title"><i class="fas fa-user-plus"></i> Register Student - <?= htmlspecialchars($class_name) ?></h3>
          <a href="TeacherHome.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
      </div>

      <?php if($err): ?>
          <div class="toast-notification error">
              <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($err) ?>
          </div>
      <?php endif; ?>
      <?php if($success): ?>
          <div class="toast-notification success">
              <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
          </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="profile-form mt-3">
          <div class="form-group">
              <label class="form-label">Name:</label>
              <input type="text" name="student_name" class="form-control" required>
          </div>

          <div class="form-group">
              <label class="form-label">Email:</label>
              <input type="email" name="student_email" class="form-control" required>
          </div>

          <div class="form-group">
              <label class="form-label">Contact:</label>
              <input type="text" name="student_contact" class="form-control">
          </div>

          <div class="form-group">
              <label class="form-label">Address:</label>
              <textarea name="student_address" class="form-control" rows="3"></textarea>
          </div>

          <div class="form-group">
              <label class="form-label">Gender:</label>
              <select name="student_gender" class="form-control" required>
                  <option value="">Select Gender</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
              </select>
          </div>

          <div class="form-group">
              <label class="form-label">Password:</label>
              <input type="password" name="student_password" class="form-control" required>
          </div>

          <div class="form-group">
              <label class="form-label">Photo (optional):</label>
              <input type="file" name="student_photo" class="form-control" accept="image/*">
          </div>

          <div class="text-center mt-3">
              <button type="submit" name="btn_register" class="btn-primary">
                  <i class="fas fa-user-check"></i> Register Student
              </button>
          </div>
      </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="../Assets/JS/universal.js"></script>

<style>
.profile-form {
    max-width: 700px;
    margin: 0 auto;
    text-align: left;
}
.form-group {
    margin-bottom: 1rem;
}
.form-label {
    display: block;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.4rem;
    text-align: left;
}
.form-control, select, textarea {
    width: 100%;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--border-glass);
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
}
.form-control:focus {
    outline: none;
    border-color: var(--gradient-1);
    box-shadow: 0 0 8px rgba(102,126,234,0.4);
}
textarea {
    resize: vertical;
}
.btn-primary i {
    margin-right: 8px;
}
.text-center.mt-3 {
    display: flex;
    justify-content: center;
}
.toast-notification {
    margin-bottom: 1rem;
}
</style>
</body>
</html>
