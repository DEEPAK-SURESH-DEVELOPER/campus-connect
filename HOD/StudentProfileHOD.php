<?php
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
$return_url = $_GET['return_url'] ?? "ManageStudents.php";
$student_id = (int)($_GET['student_id'] ?? 0);

// --- Validation Errors Array ---
$errors = [];

// --- Fetch Student Details ---
$studentQry = "
    SELECT s.*, cl.course_id, cl.class_name, c.course_name
    FROM tbl_student s
    JOIN tbl_class cl ON s.class_id = cl.class_id
    JOIN tbl_course c ON cl.course_id = c.course_id
    WHERE s.student_id = '$student_id'
";
$studentRes = mysqli_query($con, $studentQry);

if(!$studentRes || mysqli_num_rows($studentRes) == 0) {
    echo "<script>alert('Student not found'); window.location='ManageStudents.php';</script>";
    exit;
}

$student = mysqli_fetch_assoc($studentRes);
$photo = $student['student_photo'] ?? '';
$photo_path = "../Assets/Files/Student/" . $photo;
if(empty($photo) || !file_exists($photo_path)){
    $photo_path = "../Assets/Images/default.png";
}

// --- Fetch Active Classes for Student's Course ---
$classRes = mysqli_query($con, "
    SELECT cl.*, c.course_name
    FROM tbl_class cl
    JOIN tbl_course c ON cl.course_id = c.course_id
    WHERE cl.course_id = '{$student['course_id']}' AND cl.is_completed = 0
    ORDER BY cl.class_name ASC
");

// --- Handle Profile Update ---
if(isset($_POST['btn_update'])){
    $name = trim($_POST['student_name']);
    $email = trim($_POST['student_email']);
    $password = trim($_POST['student_password']);
    $contact = trim($_POST['student_contact']);
    $address = trim($_POST['student_address']);
    $class_id = (int)$_POST['class_id'];
    
    // Server-side Validation
    if(empty($name)){
        $errors[] = "Name is required";
    } elseif(strlen($name) < 3){
        $errors[] = "Name must be at least 3 characters";
    }
    
    if(empty($email)){
        $errors[] = "Email is required";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Invalid email format";
    } else {
        // Check if email exists for other students
        $emailCheckQry = "SELECT student_id FROM tbl_student WHERE student_email='".mysqli_real_escape_string($con, $email)."' AND student_id != '$student_id'";
        $emailCheckRes = mysqli_query($con, $emailCheckQry);
        if(mysqli_num_rows($emailCheckRes) > 0){
            $errors[] = "Email already exists";
        }
    }
    
    if(empty($password)){
        $errors[] = "Password is required";
    } elseif(strlen($password) < 6){
        $errors[] = "Password must be at least 6 characters";
    }
    
    if(empty($contact)){
        $errors[] = "Contact is required";
    } elseif(!preg_match('/^[0-9]{10}$/', $contact)){
        $errors[] = "Contact must be 10 digits";
    }
    
    if(empty($address)){
        $errors[] = "Address is required";
    }
    
    if($class_id <= 0){
        $errors[] = "Please select a valid class";
    }
    
    // Photo Upload Validation
    $photo_name = $student['student_photo'];
    if(isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] === 0){
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 2 * 1024 * 1024; // 2MB
        
        $file_size = $_FILES['student_photo']['size'];
        $file_tmp = $_FILES['student_photo']['tmp_name'];
        $file_name = $_FILES['student_photo']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if(!in_array($file_ext, $allowed_extensions)){
            $errors[] = "Photo must be jpg, jpeg, png, or gif";
        } elseif($file_size > $max_file_size){
            $errors[] = "Photo size must be less than 2MB";
        } else {
            // Delete old photo if it exists and is not default
            if(!empty($student['student_photo']) && file_exists("../Assets/Files/Student/".$student['student_photo'])){
                unlink("../Assets/Files/Student/".$student['student_photo']);
            }
            
            $photo_name = "student_{$student_id}_".time().".".$file_ext;
            if(!move_uploaded_file($file_tmp, "../Assets/Files/Student/".$photo_name)){
                $errors[] = "Failed to upload photo";
            }
        }
    }
    
    // If no errors, update database
    if(empty($errors)){
        $name = mysqli_real_escape_string($con, $name);
        $email = mysqli_real_escape_string($con, $email);
        $password = mysqli_real_escape_string($con, $password);
        $contact = mysqli_real_escape_string($con, $contact);
        $address = mysqli_real_escape_string($con, $address);
        
        $updateQry = "
            UPDATE tbl_student SET
                student_name='$name',
                student_email='$email',
                student_password='$password',
                student_contact='$contact',
                student_address='$address',
                class_id='$class_id',
                student_photo='$photo_name'
            WHERE student_id='$student_id'
        ";
        
        if(mysqli_query($con, $updateQry)){
            echo "<script>alert('Profile updated successfully'); window.location='StudentProfileHOD.php?student_id=$student_id&return_url=".urlencode($return_url)."';</script>";
            exit;
        } else {
            $errors[] = "Database error: " . mysqli_error($con);
        }
    }
}

$page_title = "Student Profile";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> 
               <a href="ManageStudents.php">Manage Students</a> 
               <i class="fas fa-chevron-right"></i> <span>Profile</span>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo $page_title; ?> - Campus Connect</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
/* === Student Profile Page Inline Styling === */
.profile-card {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    align-items: flex-start;
}
.profile-avatar {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid var(--gradient-1);
    flex-shrink: 0;
    position: relative;
}
.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.profile-info {
    flex: 1;
    min-width: 280px;
}
.form-group {
    text-align: left;       /* Ensures the whole block is left aligned */
}

.form-group label {
    display: block;         /* Forces label to sit above the input */
    text-align: left;       /* Aligns label text to the left */
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 0.3rem;  /* Spacing between label and input */
}

.profile-info label {
    font-weight: 500;
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}
.profile-info input,
.profile-info select,
.profile-info textarea {
    width: 100%;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.08);
    color: #fff;
    font-size: 0.95rem;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}
.profile-info input:focus,
.profile-info select:focus,
.profile-info textarea:focus {
    outline: none;
    border-color: var(--gradient-1);
    box-shadow: 0 0 10px rgba(99,102,241,0.4);
}
.profile-info textarea {
    min-height: 80px;
    resize: vertical;
}
.profile-info button {
    margin-top: 1rem;
    border: none;
    padding: 12px 24px;
    font-size: 1rem;
    color: #fff;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    cursor: pointer;
    transition: all 0.3s ease;
}
.profile-info button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99,102,241,0.4);
}
.error-messages {
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid rgba(239, 68, 68, 0.4);
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.error-messages ul {
    margin: 0;
    padding-left: 1.5rem;
    color: #fca5a5;
}
.error-messages li {
    margin-bottom: 0.3rem;
}
.input-error {
    border-color: rgba(239, 68, 68, 0.6) !important;
}
.file-upload-wrapper {
    position: relative;
}
.file-upload-label {
    display: inline-block;
    padding: 8px 16px;
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.file-upload-label:hover {
    background: rgba(255,255,255,0.15);
}
.file-upload-label i {
    margin-right: 8px;
}
#student_photo {
    display: none;
}
.file-name {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: rgba(255,255,255,0.7);
}
</style>
</head>

<body>
<main class="main-content">
    <div class="glass-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-graduate"></i> Student Profile</h3>
            <button class="btn-outline" onclick="location.href='<?php echo htmlspecialchars($return_url); ?>'">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>

        <?php if(!empty($errors)): ?>
        <div class="error-messages">
            <strong><i class="fas fa-exclamation-circle"></i> Please correct the following errors:</strong>
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="profile-card">
            <div class="profile-avatar">
                <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Student Photo" id="photoPreview">
            </div>

            <div class="profile-info">
                <form method="post" enctype="multipart/form-data" id="profileForm" novalidate>
                    <div class="form-group">
                        <label for="student_photo"><i class="fas fa-camera"></i> Update Photo</label>
                        <div class="file-upload-wrapper">
                            <label for="student_photo" class="file-upload-label">
                                <i class="fas fa-upload"></i> Choose Photo
                            </label>
                            <input type="file" name="student_photo" id="student_photo" accept="image/jpeg,image/jpg,image/png,image/gif">
                            <span class="file-name" id="fileName">No file chosen</span>
                        </div>
                        <small style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Max size: 2MB. Formats: JPG, PNG, GIF</small>
                    </div>

                    <div class="form-group">
                        <label for="student_name">Name: <span style="color: #f87171;">*</span></label>
                        <input type="text" name="student_name" id="student_name"
                               value="<?php echo htmlspecialchars($student['student_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="student_email">Email: <span style="color: #f87171;">*</span></label>
                        <input type="email" name="student_email" id="student_email"
                               value="<?php echo htmlspecialchars($student['student_email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="student_password">Password: <span style="color: #f87171;">*</span></label>
                        <input type="text" name="student_password" id="student_password"
                               value="<?php echo htmlspecialchars($student['student_password']); ?>" required>
                        <small style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">Minimum 6 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="student_contact">Contact: <span style="color: #f87171;">*</span></label>
                        <input type="text" name="student_contact" id="student_contact"
                               value="<?php echo htmlspecialchars($student['student_contact']); ?>" 
                               pattern="[0-9]{10}" maxlength="10" required>
                        <small style="color: rgba(255,255,255,0.6); font-size: 0.85rem;">10 digit mobile number</small>
                    </div>

                    <div class="form-group">
                        <label for="student_address">Address: <span style="color: #f87171;">*</span></label>
                        <textarea name="student_address" id="student_address" required><?php echo htmlspecialchars($student['student_address']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="class_id">Class: <span style="color: #f87171;">*</span></label>
                        <select name="class_id" id="class_id" required>
                            <option value="">-- Select Class --</option>
                            <?php 
                            mysqli_data_seek($classRes, 0); // Reset pointer
                            while($cl = mysqli_fetch_assoc($classRes)): 
                            ?>
                                <option value="<?php echo $cl['class_id']; ?>" <?php echo ($cl['class_id'] == $student['class_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cl['course_name']." - ".$cl['class_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" name="btn_update"><i class="fas fa-save"></i> Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="../Assets/JS/universal.js"></script>
<script>
// Photo preview and file name display
document.getElementById('student_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const fileName = document.getElementById('fileName');
    const photoPreview = document.getElementById('photoPreview');
    
    if (file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, or GIF)');
            e.target.value = '';
            fileName.textContent = 'No file chosen';
            return;
        }
        
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            e.target.value = '';
            fileName.textContent = 'No file chosen';
            return;
        }
        
        fileName.textContent = file.name;
        
        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            photoPreview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        fileName.textContent = 'No file chosen';
    }
});

// Form validation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    let isValid = true;
    const errors = [];
    
    // Name validation
    const name = document.getElementById('student_name').value.trim();
    if (name.length < 3) {
        errors.push('Name must be at least 3 characters');
        document.getElementById('student_name').classList.add('input-error');
        isValid = false;
    } else {
        document.getElementById('student_name').classList.remove('input-error');
    }
    
    // Email validation
    const email = document.getElementById('student_email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        errors.push('Please enter a valid email address');
        document.getElementById('student_email').classList.add('input-error');
        isValid = false;
    } else {
        document.getElementById('student_email').classList.remove('input-error');
    }
    
    // Password validation
    const password = document.getElementById('student_password').value;
    if (password.length < 6) {
        errors.push('Password must be at least 6 characters');
        document.getElementById('student_password').classList.add('input-error');
        isValid = false;
    } else {
        document.getElementById('student_password').classList.remove('input-error');
    }
    
    // Contact validation
    const contact = document.getElementById('student_contact').value.trim();
    const contactRegex = /^[0-9]{10}$/;
    if (!contactRegex.test(contact)) {
        errors.push('Contact must be exactly 10 digits');
        document.getElementById('student_contact').classList.add('input-error');
        isValid = false;
    } else {
        document.getElementById('student_contact').classList.remove('input-error');
    }
    
    // Address validation
    const address = document.getElementById('student_address').value.trim();
    if (address.length === 0) {
        errors.push('Address is required');
        document.getElementById('student_address').classList.add('input-error');
        isValid = false;
    } else {
        document.getElementById('student_address').classList.remove('input-error');
    }
    
    // Class validation
    const classId = document.getElementById('class_id').value;
    if (classId === '') {
        errors.push('Please select a class');
        document.getElementById('class_id').classList.add('input-error');
        isValid = false;
    } else {
        document.getElementById('class_id').classList.remove('input-error');
    }
    
    if (!isValid) {
        e.preventDefault();
        alert('Please correct the following errors:\n\n' + errors.join('\n'));
    }
});

// Remove error styling on input
document.querySelectorAll('input, select, textarea').forEach(element => {
    element.addEventListener('input', function() {
        this.classList.remove('input-error');
    });
});
</script>
</body>
</html>