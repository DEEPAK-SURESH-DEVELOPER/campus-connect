<?php
include("../Includes/TeacherHeader.php");

if(!isset($_SESSION['teacher_id']) || empty($_SESSION['is_class_teacher']) || !$_SESSION['is_class_teacher']){
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/TeacherSidebar.php");
$teacher_id = $_SESSION['teacher_id'];
$class_id = $_SESSION['class_id'] ?? null;

$student_id = (int)($_GET['student_id'] ?? 0);
if(!$student_id){
    echo "<script>alert('Invalid student ID'); window.location='StudentList.php';</script>";
    exit;
}

$studentRes = $con->query("SELECT * FROM tbl_student WHERE student_id='$student_id' AND class_id='$class_id'");
$student = $studentRes ? $studentRes->fetch_assoc() : null;

if(!$student){
    echo "<script>alert('Access denied or student not found!'); window.location='StudentList.php';</script>";
    exit;
}

$err = $success = "";

if(isset($_POST['btn_save'])){
    $name = mysqli_real_escape_string($con,$_POST['student_name']);
    $gender = $_POST['student_gender'];
    $address = mysqli_real_escape_string($con,$_POST['student_address']);
    $contact = mysqli_real_escape_string($con,$_POST['student_contact']);

    $photoName = $student['student_photo'];

    if(isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] == 0){
        $allowed = ['jpg','jpeg','png','gif'];
        $fileName = $_FILES['student_photo']['name'];
        $fileTmp = $_FILES['student_photo']['tmp_name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if(in_array($ext, $allowed)){
            $newName = "student_".$student_id."_".time().".".$ext;
            $uploadPath = "../Assets/Files/Student/".$newName;

            if(move_uploaded_file($fileTmp, $uploadPath)){
                if($student['student_photo'] && file_exists("../Assets/Files/Student/".$student['student_photo'])){
                    unlink("../Assets/Files/Student/".$student['student_photo']);
                }
                $photoName = $newName;
            } else {
                $err = "Error uploading the photo!";
            }
        } else {
            $err = "Invalid file type! Only JPG, PNG, or GIF allowed.";
        }
    }

    if(!$err){
        $updateQry = "UPDATE tbl_student SET 
            student_name='$name', 
            student_gender='$gender', 
            student_address='$address', 
            student_contact='$contact', 
            student_photo='$photoName'
            WHERE student_id='$student_id' AND class_id='$class_id'";

        if(mysqli_query($con, $updateQry)){
            $success = "Profile updated successfully!";
            $studentRes = $con->query("SELECT * FROM tbl_student WHERE student_id='$student_id' AND class_id='$class_id'");
            $student = $studentRes->fetch_assoc();
        } else {
            $err = "Update failed!";
        }
    }
}

$page_title = "Student Profile";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Student Profile</span>';
?>

<div class="main-content">
    <div class="glass-card fade-in">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-graduate"></i> Student Profile</h3>
            <a href="StudentList.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?php if($err): ?>
            <div class="toast-notification error">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="toast-notification success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="profile-card text-center mt-3">
            <div class="profile-avatar">
                <img src="../Assets/Files/Student/<?= htmlspecialchars($student['student_photo'] ?: 'default.png') ?>" alt="Profile Photo">
            </div>
            <h2 class="text-gradient"><?= htmlspecialchars($student['student_name']) ?></h2>
            <p style="color:var(--text-secondary); margin-bottom:1rem;">Student ID: <?= $student['student_id'] ?></p>

            <form method="post" enctype="multipart/form-data" id="profileForm" class="profile-form">
                <div class="form-group">
                    <label class="form-label">Change Photo:</label>
                    <input type="file" name="student_photo" class="form-control" accept="image/*" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label">Name:</label>
                    <input type="text" name="student_name" class="form-control" value="<?= htmlspecialchars($student['student_name']) ?>" disabled required>
                </div>

                <div class="form-group">
                    <label class="form-label">Gender:</label>
                    <select name="student_gender" class="form-control" disabled required>
                        <option value="Male" <?= $student['student_gender']=='Male'?'selected':'' ?>>Male</option>
                        <option value="Female" <?= $student['student_gender']=='Female'?'selected':'' ?>>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Address:</label>
                    <textarea name="student_address" class="form-control" rows="3" disabled><?= htmlspecialchars($student['student_address']) ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Contact:</label>
                    <input type="text" name="student_contact" class="form-control" value="<?= htmlspecialchars($student['student_contact']) ?>" disabled>
                </div>

                <div class="form-group">
                    <label class="form-label">Email:</label>
                    <input type="email" name="student_email" class="form-control" value="<?= htmlspecialchars($student['student_email']) ?>" disabled>
                </div>

                <div class="text-center mt-3">
                    <button type="button" class="btn-outline" id="editBtn" onclick="enableEdit()">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                    <button type="submit" name="btn_save" id="saveBtn" class="btn-primary" style="display:none;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function enableEdit(){
    document.querySelectorAll('#profileForm input, #profileForm select, #profileForm textarea').forEach(el=>{
        if(el.name !== 'student_email') el.disabled = false;
    });
    document.getElementById('editBtn').style.display = 'none';
    document.getElementById('saveBtn').style.display = 'inline-block';
}
</script>

<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script src="../Assets/JS/universal.js"></script>

<style>
/* Base profile card styling */
.profile-card {
    max-width: 700px;
    margin: 0 auto;
    background: var(--card-bg);
    border: 1px solid var(--border-glass);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
    backdrop-filter: blur(20px);
}

/* Avatar section */
.profile-avatar {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    border: 3px solid var(--gradient-1);
    overflow: hidden;
    margin: 0 auto 1rem;
    box-shadow: 0 0 25px rgba(102, 126, 234, 0.4);
    transition: transform 0.3s ease;
}
.profile-avatar:hover {
    transform: scale(1.05);
}
.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ✅ Align labels to the left */
.profile-form {
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
    text-align: left; /* key fix */
}
.form-control, select, textarea {
    width: 100%;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--border-glass);
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
}
.form-control[disabled] {
    opacity: 0.7;
    cursor: not-allowed;
}
.text-center {
    text-align: center;
}
.toast-notification {
    margin-bottom: 1rem;
}
</style>
</body>
</html>
