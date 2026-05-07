<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
if($class_id <= 0){
    echo "<script>alert('Invalid class'); window.location='AdminHome.php';</script>";
    exit;
}
$department_id = (int)$_GET['department_id'];

if(isset($_POST['btn_add_student'])){
    $name = trim($_POST['txt_name']);
    $email = trim($_POST['txt_email']);
    $contact = trim($_POST['txt_contact']);
    $gender = $_POST['sel_gender'];
    $address = trim($_POST['txt_address']);
    $password = password_hash(trim($_POST['txt_password']), PASSWORD_DEFAULT);

    $photo = '';
    if(isset($_FILES['file_photo']) && $_FILES['file_photo']['error'] == 0){
        $ext = pathinfo($_FILES['file_photo']['name'], PATHINFO_EXTENSION);
        $photo = time()."_".rand(1000,9999).".".$ext;
        move_uploaded_file($_FILES['file_photo']['tmp_name'], "../Assets/Files/Student/".$photo);
    }

    $con->query("INSERT INTO tbl_student(student_name, student_email, student_contact, student_address, student_gender, student_password, class_id, student_photo)
                 VALUES('".$name."','".$email."','".$contact."','".$address."','".$gender."','".$password."',".$class_id.",'".$photo."')");

    echo "<script>
     alert('Student added successfully'); 
     window.location='ViewStudents.php?class_id=".$class_id."&department_id=".$department_id."';
    </script>";
    exit;
}

$page_title = "Add Student";
$breadcrumb = '<span>Students</span> <i class="fas fa-chevron-right"></i> <span>Add Student</span>';
?>

<!-- INTERNAL PAGE STYLE -->
<style>
.main-content {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 100vh;
  padding: 2rem 0;
}

.form-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border-glass);
  border-radius: 20px;
  backdrop-filter: blur(15px);
  padding: 2rem 2.5rem;
  width: 500px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
  color: var(--text-primary);
}

.form-card h2 {
  text-align: center;
  color: var(--gradient-1);
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 0.4rem;
  font-weight: 600;
  color: var(--text-secondary);
}

.form-control,
textarea,
select,
input[type="text"],
input[type="email"],
input[type="password"],
input[type="file"] {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  border: 1px solid var(--border-glass);
  background: rgba(255, 255, 255, 0.08);
  color: var(--text-primary);
  font-size: 0.95rem;
  outline: none;
  backdrop-filter: blur(6px);
  transition: 0.3s ease;
}

textarea {
  resize: none;
  min-height: 80px;
}

.form-control:focus,
textarea:focus,
select:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 8px rgba(99,102,241,0.4);
}

select {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  color: var(--text-primary);
}
select option {
  background: var(--secondary-bg);
  color: var(--text-primary);
}

.button-group {
  display: flex;
  justify-content: space-between;
  gap: 0.8rem;
  margin-top: 1.5rem;
}

.btn-primary, .btn-secondary, .btn-back {
  flex: 1;
  text-align: center;
  padding: 0.8rem 1rem;
  border-radius: 8px;
  border: none;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
}
.btn-primary:hover {
  opacity: 0.9;
}

.btn-secondary {
  background: rgba(255,255,255,0.1);
  color: var(--text-primary);
  border: 1px solid var(--border-glass);
}
.btn-secondary:hover {
  background: rgba(255,255,255,0.15);
}

.btn-back {
  background: rgba(255,255,255,0.08);
  color: var(--gradient-1);
  text-decoration: none;
  border: 1px solid var(--border-glass);
}
.btn-back:hover {
  background: rgba(255,255,255,0.15);
}

input[type="file"] {
  border: none;
  padding-left: 0;
  color: var(--text-secondary);
}
</style>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="form-card">
    <h2><i class="fas fa-user-plus"></i> Add New Student</h2>
    <form method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label>Name:</label>
        <input type="text" name="txt_name" class="form-control" placeholder="Enter full name" required>
      </div>

      <div class="form-group">
        <label>Email:</label>
        <input type="email" name="txt_email" class="form-control" placeholder="Enter email" required>
      </div>

      <div class="form-group">
        <label>Contact:</label>
        <input type="text" name="txt_contact" class="form-control" placeholder="Enter phone number">
      </div>

      <div class="form-group">
        <label>Gender:</label>
        <select name="sel_gender" class="form-control" required>
          <option value="">Select Gender</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
      </div>

      <div class="form-group">
        <label>Address:</label>
        <textarea name="txt_address" class="form-control" placeholder="Enter address"></textarea>
      </div>

      <div class="form-group">
        <label>Password:</label>
        <input type="password" name="txt_password" class="form-control" placeholder="Enter student password" required>
      </div>

      <div class="form-group">
        <label>Photo:</label>
        <input type="file" name="file_photo" class="form-control" accept="image/*">
      </div>

      <div class="button-group">
        <input type="submit" name="btn_add_student" value="Add Student" class="btn-primary">
        <input type="reset" value="Clear" class="btn-secondary">
        <a href="ViewStudents.php?class_id=<?= $class_id ?>&department_id=<?= $department_id ?>" class="btn-back">Back</a>
      </div>
    </form>
  </div>
</div>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
