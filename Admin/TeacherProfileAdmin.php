<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");

if(!isset($_GET['teacher_id'])) {
    header("location: AdminTeacherList.php");
    exit();
}

$teacher_id = (int)$_GET['teacher_id'];

// Fetch teacher details
$teacherQry = "SELECT t.*, d.designation_name, dep.department_name 
               FROM tbl_teacher t
               JOIN tbl_designation d ON t.designation_id = d.designation_id
               JOIN tbl_department dep ON t.department_id = dep.department_id
               WHERE t.teacher_id='$teacher_id'";
$teacherRes = mysqli_query($con, $teacherQry);

if(!$teacherRes || mysqli_num_rows($teacherRes) == 0){
    header("location: AdminTeacherList.php");
    exit();
}

$teacher = mysqli_fetch_assoc($teacherRes);

// ---- FIXED PHOTO LOGIC ----
$photoRaw = isset($teacher['teacher_photo']) ? trim($teacher['teacher_photo']) : '';

if ($photoRaw !== '' && filter_var($photoRaw, FILTER_VALIDATE_URL)) {
    $photo_url = $photoRaw;
} 
else {
    $photoFileName = ($photoRaw !== '' ? $photoRaw : 'default.png');
    $relativeUrl = "../Assets/Files/Teacher/" . $photoFileName;
    $fsPath = __DIR__ . "/../Assets/Files/Teacher/" . $photoFileName;

    if (!file_exists($fsPath)) {
        $photoFileName = "default.png";
        $relativeUrl = "../Assets/Files/Teacher/default.png";
        $fsPath = __DIR__ . "/../Assets/Files/Teacher/default.png";
    }

    $ver = file_exists($fsPath) ? filemtime($fsPath) : time();
    $photo_url = $relativeUrl . "?v=" . $ver;
}

$designationRes = mysqli_query($con, "SELECT * FROM tbl_designation");

if(isset($_POST['btn_update'])){
    // NO BACKEND CHANGES — KEPT AS ORIGINAL
    $name = mysqli_real_escape_string($con, $_POST['teacher_name']);
    $email = mysqli_real_escape_string($con, $_POST['teacher_email']);
    $password = mysqli_real_escape_string($con, $_POST['teacher_password']);
    $contact = mysqli_real_escape_string($con, $_POST['teacher_contact']);
    $address = mysqli_real_escape_string($con, $_POST['teacher_address']);
    $designation_id = (int)$_POST['designation_id'];

    $photo_name = $teacher['teacher_photo'];
    if(isset($_FILES['teacher_photo']) && $_FILES['teacher_photo']['error'] === 0){
        $ext = pathinfo($_FILES['teacher_photo']['name'], PATHINFO_EXTENSION);
        $photo_name = "teacher_".$teacher_id.".".$ext;
        move_uploaded_file($_FILES['teacher_photo']['tmp_name'], "../Assets/Files/Teacher/".$photo_name);
    }

    mysqli_query($con, "
        UPDATE tbl_teacher SET
            teacher_name='$name',
            teacher_email='$email',
            teacher_password='$password',
            teacher_contact='$contact',
            teacher_address='$address',
            designation_id='$designation_id',
            teacher_photo='$photo_name'
        WHERE teacher_id='$teacher_id'
    ");

    echo "<script>
        alert('Profile Updated Successfully');
        window.location='TeacherProfileAdmin.php?teacher_id=$teacher_id';
    </script>";
}

$page_title = "Teacher Profile";
$breadcrumb = "<span>Teachers</span> <i class='fas fa-chevron-right'></i> <span>Profile</span>";

?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="glass-card" style="padding: 2.5rem; max-width: 900px; margin: auto;">
        
        <div class="d-flex align-center justify-between mb-3">
            <h2 class="text-gradient">Teacher Profile</h2>
            <a href="AdminTeacherList.php" class="btn-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <!-- PROFILE PHOTO -->
        <div class="text-center mb-4">
            <div class="profile-avatar" style="width:150px; height:150px;">
                <img src="<?= htmlspecialchars($photo_url) ?>" id="teacherPhotoPreview" style="width:150px;height:150px;border-radius:50%;object-fit:cover;">
            </div>
        </div>

        <!-- FORM -->
        <form method="post" enctype="multipart/form-data" id="profileForm">

            <div class="form-group">
                <label class="form-label">Photo</label>
                <input type="file" name="teacher_photo" id="teacher_photo" class="form-control" disabled onchange="previewPhoto(event)">
            </div>

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="teacher_name" value="<?= $teacher['teacher_name'] ?>" class="form-control" disabled required>
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="teacher_email" value="<?= $teacher['teacher_email'] ?>" class="form-control" disabled required>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="text" name="teacher_password" value="<?= $teacher['teacher_password'] ?>" class="form-control" disabled required>
            </div>

            <div class="form-group">
                <label class="form-label">Contact</label>
                <input type="text" name="teacher_contact" value="<?= $teacher['teacher_contact'] ?>" class="form-control" disabled required>
            </div>

            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="teacher_address" class="form-control" disabled required><?= $teacher['teacher_address'] ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Designation</label>
                <select name="designation_id" class="form-control" disabled required>
                    <option value="">-- Select --</option>
                    <?php while($d = mysqli_fetch_assoc($designationRes)){ ?>
                        <option value="<?= $d['designation_id'] ?>" 
                            <?= $d['designation_id']==$teacher['designation_id'] ? "selected" : "" ?>>
                            <?= $d['designation_name'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="d-flex justify-center gap-2 mt-3">
                <button type="button" id="editBtn" class="btn-primary" style="width:180px;">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>

                <button type="submit" name="btn_update" id="updateBtn" 
                        class="btn-primary" style="width:180px; display:none;">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>

        </form>
    </div>
</div>

<script>
// Enable Edit Mode
document.getElementById("editBtn").addEventListener("click", ()=>{
    document.querySelectorAll("#profileForm input, #profileForm textarea, #profileForm select")
    .forEach(el => el.disabled = false);

    document.getElementById("editBtn").style.display = "none";
    document.getElementById("updateBtn").style.display = "inline-block";
});

// Photo Preview
function previewPhoto(event){
    let file = event.target.files[0];
    if(file){
        document.getElementById("teacherPhotoPreview").src = URL.createObjectURL(file);
    }
}

// ========== REAL-TIME INPUT RESTRICTIONS ==========

// Restrict Name field: Only letters and spaces
document.querySelector('[name="teacher_name"]').addEventListener("input", function(e){
    this.value = this.value.replace(/[^A-Za-z ]/g, '');
});

// Restrict Contact field: Only numbers, max 10 digits
document.querySelector('[name="teacher_contact"]').addEventListener("input", function(e){
    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
});

// Restrict Password field: No spaces
document.querySelector('[name="teacher_password"]').addEventListener("input", function(e){
    this.value = this.value.replace(/\s/g, '');
});

// ========== FORM VALIDATION ==========
document.getElementById("profileForm").addEventListener("submit", function(e){
    
    // Get form values
    const name = document.querySelector('[name="teacher_name"]').value.trim();
    const email = document.querySelector('[name="teacher_email"]').value.trim();
    const password = document.querySelector('[name="teacher_password"]').value.trim();
    const contact = document.querySelector('[name="teacher_contact"]').value.trim();
    const address = document.querySelector('[name="teacher_address"]').value.trim();
    const designation = document.querySelector('[name="designation_id"]').value;
    const photoInput = document.getElementById("teacher_photo");

    // Validation: Full Name (only letters and spaces, not empty)
    if(name === ""){
        alert("❌ Full Name is required!");
        e.preventDefault();
        return false;
    }
    const namePattern = /^[A-Za-z ]+$/;
    if(!namePattern.test(name)){
        alert("❌ Full Name must contain only letters and spaces!");
        e.preventDefault();
        return false;
    }

    // Validation: Email format
    if(email === ""){
        alert("❌ Email is required!");
        e.preventDefault();
        return false;
    }
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailPattern.test(email)){
        alert("❌ Please enter a valid email address!");
        e.preventDefault();
        return false;
    }

    // Validation: Password minimum 6 characters
    if(password === ""){
        alert("❌ Password is required!");
        e.preventDefault();
        return false;
    }
    if(password.length < 6){
        alert("❌ Password must be at least 6 characters long!");
        e.preventDefault();
        return false;
    }

    // Validation: Contact (exactly 10 digits, numbers only)
    if(contact === ""){
        alert("❌ Contact number is required!");
        e.preventDefault();
        return false;
    }
    const contactPattern = /^[0-9]{10}$/;
    if(!contactPattern.test(contact)){
        alert("❌ Contact number must be exactly 10 digits (numbers only)!");
        e.preventDefault();
        return false;
    }

    // Validation: Address minimum 5 characters
    if(address === ""){
        alert("❌ Address is required!");
        e.preventDefault();
        return false;
    }
    if(address.length < 5){
        alert("❌ Address must be at least 5 characters long!");
        e.preventDefault();
        return false;
    }

    // Validation: Designation must be selected
    if(designation === "" || designation === null){
        alert("❌ Please select a designation!");
        e.preventDefault();
        return false;
    }

    // Validation: Photo file type (if uploaded)
    if(photoInput.files.length > 0){
        const file = photoInput.files[0];
        const fileName = file.name.toLowerCase();
        const allowedExtensions = ['jpg', 'jpeg', 'png'];
        const fileExtension = fileName.split('.').pop();

        if(!allowedExtensions.includes(fileExtension)){
            alert("❌ Photo must be in JPG, JPEG, or PNG format only!");
            e.preventDefault();
            return false;
        }

        // Optional: Check file size (max 5MB)
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        if(file.size > maxSize){
            alert("❌ Photo size must be less than 5MB!");
            e.preventDefault();
            return false;
        }
    }

    // All validations passed - Show confirmation
    alert("✅ Profile updated successfully!");
    return true;
});
</script>

