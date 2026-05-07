<?php
include("../Includes/TeacherHeader.php");

if(!isset($_SESSION['teacher_id'])){
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/TeacherSidebar.php");
$teacher_id = (int)$_SESSION['teacher_id'];

// Fetch teacher info
$qry = "SELECT t.*, d.designation_name, dep.department_name 
        FROM tbl_teacher t
        JOIN tbl_designation d ON t.designation_id = d.designation_id
        JOIN tbl_department dep ON t.department_id = dep.department_id
        WHERE t.teacher_id='$teacher_id'";
$res = mysqli_query($con, $qry);
$teacher = mysqli_fetch_assoc($res);

$err = $success = "";

// Handle update
if(isset($_POST['btn_save'])){
    $name = mysqli_real_escape_string($con,$_POST['teacher_name']);
    $gender = $_POST['teacher_gender'];
    $address = mysqli_real_escape_string($con,$_POST['teacher_address']);
    $contact = mysqli_real_escape_string($con,$_POST['teacher_contact']);

    $photoName = $teacher['teacher_photo'];

    // Photo upload
    if(isset($_FILES['teacher_photo']) && $_FILES['teacher_photo']['error'] == 0){
        $allowed = ['jpg','jpeg','png','gif'];
        $fileName = $_FILES['teacher_photo']['name'];
        $tmp = $_FILES['teacher_photo']['tmp_name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if(in_array($ext, $allowed)){
            $newName = "teacher_".$teacher_id."_".time().".".$ext;
            if(move_uploaded_file($tmp, "../Assets/Files/Teacher/".$newName)){
                if($teacher['teacher_photo'] && file_exists("../Assets/Files/Teacher/".$teacher['teacher_photo'])){
                    unlink("../Assets/Files/Teacher/".$teacher['teacher_photo']);
                }
                $photoName = $newName;
            } else $err = "Failed to upload photo!";
        } else $err = "Invalid image format!";
    }

    if(!$err){
        $up = "UPDATE tbl_teacher SET 
              teacher_name='$name', 
              teacher_gender='$gender',
              teacher_address='$address',
              teacher_contact='$contact',
              teacher_photo='$photoName'
              WHERE teacher_id='$teacher_id'";

        if(mysqli_query($con, $up)){
            $success = "Profile updated successfully!";
            $res = mysqli_query($con, $qry);
            $teacher = mysqli_fetch_assoc($res);
        } else $err = "Update failed!";
    }
}

$photo = (!empty($teacher['teacher_photo']) && file_exists("../Assets/Files/Teacher/".$teacher['teacher_photo']))
        ? "../Assets/Files/Teacher/".$teacher['teacher_photo']
        : "../Assets/Files/Teacher/default.png";

$page_title = "My Profile";
$breadcrumb = '<span>Teacher</span> <i class="fas fa-chevron-right"></i> <span>Profile</span>';

?>

<style>
/* -------------------------------
   Modern Profile Redesign
--------------------------------*/
.profile-wrapper {
    max-width: 900px;
    margin: auto;
    animation: fadeIn 0.5s ease;
}

.profile-banner {
    height: 180px;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    border-radius: 20px 20px 0 0;
}

.profile-card-modern {
    margin-top: -80px;
    background: var(--card-bg);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 2.5rem;
    border: 1px solid var(--border-glass);
    box-shadow: var(--shadow-md);
}

.profile-avatar-container {
    position: relative;
    width: 160px;
    height: 160px;
    margin: auto;
}

.profile-avatar-modern {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 4px solid var(--gradient-1);
    overflow: hidden;
    box-shadow: 0 0 25px rgba(102,126,234,0.6);
}

.profile-avatar-modern img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Hover icon for editing */
.edit-photo-hover {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: var(--gradient-1);
    color: white;
    padding: 10px;
    border-radius: 50%;
    cursor: pointer;
    display: none;
    font-size: 1rem;
    box-shadow: var(--shadow-md);
}

.profile-avatar-container:hover .edit-photo-hover {
    display: block;
}

.profile-info-header {
    text-align: center;
    margin-top: 1rem;
}

.profile-info-header h2 {
    margin: 0;
    font-size: 1.8rem;
}

.profile-info-header p {
    color: var(--text-secondary);
}

.profile-form-modern .form-group {
    margin-bottom: 1.2rem;
}

.edit-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.disabled-field {
    opacity: 0.5;
}

/* Smooth transition on enable */
input, select, textarea {
    transition: 0.3s ease;
}

/* Required field indicator */
.required {
    color: #ff4757;
    font-weight: bold;
    margin-left: 3px;
}

/* Error message styling */
.error-message {
    color: #ff4757;
    font-size: 0.85rem;
    display: block;
    margin-top: 0.4rem;
    min-height: 1.2rem;
    font-weight: 500;
}

/* Input validation states */
.form-control.is-invalid {
    border-color: #ff4757 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 71, 87, 0.25) !important;
}

.form-control.is-valid {
    border-color: #2ed573 !important;
    box-shadow: 0 0 0 0.2rem rgba(46, 213, 115, 0.25) !important;
}

/* Photo upload error */
.photo-error {
    text-align: center;
    margin-top: 0.5rem;
}

</style>

<div class="main-content">
<div class="profile-wrapper">

    <!-- Banner -->
    <div class="profile-banner"></div>

    <!-- Profile Card -->
    <div class="profile-card-modern">
        
        <?php if($err): ?>
        <div class="toast-notification error"><i class="fas fa-exclamation-circle"></i> <?= $err ?></div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="toast-notification success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>

        <!-- Avatar -->
        <div class="profile-avatar-container">
            <div class="profile-avatar-modern">
                <img id="previewImg" src="<?= $photo ?>?v=<?= time() ?>">
            </div>
            <label class="edit-photo-hover" for="teacher_photo"><i class="fas fa-camera"></i></label>
        </div>
        <small class="error-message photo-error" id="photoError"></small>

        <!-- Header -->
        <div class="profile-info-header">
            <h2 class="text-gradient"><?= $teacher['teacher_name'] ?></h2>
            <p><?= $teacher['designation_name'] ?> • <?= $teacher['department_name'] ?></p>
        </div>

        <!-- Form -->
        <form method="post" enctype="multipart/form-data" id="profileForm" class="profile-form-modern">

            <input type="file" id="teacher_photo" name="teacher_photo" class="form-control" accept="image/*" style="display:none;" disabled onchange="previewImage(event)">

            <div class="form-group">
                <label>Name <span class="required">*</span></label>
                <input type="text" name="teacher_name" id="nameInput" class="form-control" 
                       value="<?= $teacher['teacher_name'] ?>" disabled required>
                <small class="error-message" id="nameError"></small>
            </div>

            <div class="form-group">
                <label>Email (Read Only)</label>
                <input type="email" class="form-control disabled-field" value="<?= $teacher['teacher_email'] ?>" readonly>
            </div>

            <div class="form-group">
                <label>Gender <span class="required">*</span></label>
                <select name="teacher_gender" id="genderInput" class="form-control" disabled required>
                    <option value="">Select Gender</option>
                    <option <?= $teacher['teacher_gender']=="Male"?"selected":"" ?>>Male</option>
                    <option <?= $teacher['teacher_gender']=="Female"?"selected":"" ?>>Female</option>
                    <option <?= $teacher['teacher_gender']=="Other"?"selected":"" ?>>Other</option>
                </select>
                <small class="error-message" id="genderError"></small>
            </div>

            <div class="form-group">
                <label>Contact <span class="required">*</span></label>
                <input type="text" name="teacher_contact" id="contactInput" class="form-control" 
                       value="<?= $teacher['teacher_contact'] ?>" disabled required 
                       placeholder="e.g., 1234567890">
                <small class="error-message" id="contactError"></small>
            </div>

            <div class="form-group">
                <label>Address <span class="required">*</span></label>
                <textarea name="teacher_address" id="addressInput" rows="3" class="form-control" 
                          disabled required><?= $teacher['teacher_address'] ?></textarea>
                <small class="error-message" id="addressError"></small>
            </div>

            <div class="edit-buttons mt-3">
                <button type="button" class="btn-outline" id="editBtn">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
                <a href="TeacherChangePassword.php" class="btn-primary">
                    <i class="fas fa-lock"></i> Change Password
                </a>

                <button type="submit" name="btn_save" id="saveBtn" class="btn-primary" style="display:none;">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button type="button" id="cancelBtn" class="btn-outline" style="display:none;">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>

    </div>
</div>
</div>

<script>
// ========================================
// VALIDATION FUNCTIONS
// ========================================

function validateName(name) {
    const nameRegex = /^[a-zA-Z\s.'-]{2,50}$/;
    return nameRegex.test(name.trim());
}

function validateContact(contact) {
    const contactRegex = /^[0-9]{10}$/;
    return contactRegex.test(contact.trim());
}

function validateAddress(address) {
    return address.trim().length >= 10 && address.trim().length <= 200;
}

function validatePhoto(file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    const maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!allowedTypes.includes(file.type)) {
        return { valid: false, message: 'Only JPG, JPEG, PNG, and GIF files are allowed' };
    }
    
    if (file.size > maxSize) {
        return { valid: false, message: 'File size must be less than 2MB' };
    }
    
    return { valid: true, message: '' };
}

// ========================================
// VALIDATION UI HELPERS
// ========================================

function showError(inputId, errorId, message) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    
    if (input && error) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        error.textContent = message;
    }
}

function showSuccess(inputId, errorId) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    
    if (input && error) {
        input.classList.add('is-valid');
        input.classList.remove('is-invalid');
        error.textContent = '';
    }
}

function clearValidation(inputId, errorId) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    
    if (input && error) {
        input.classList.remove('is-valid', 'is-invalid');
        error.textContent = '';
    }
}

// ========================================
// REAL-TIME INPUT VALIDATION
// ========================================

// Name Validation
document.getElementById('nameInput').addEventListener('blur', function() {
    const name = this.value;
    
    if (name === '') {
        showError('nameInput', 'nameError', 'Name is required');
    } else if (!validateName(name)) {
        showError('nameInput', 'nameError', 'Name must be 2-50 characters and contain only letters, spaces, dots, hyphens, or apostrophes');
    } else {
        showSuccess('nameInput', 'nameError');
    }
});

document.getElementById('nameInput').addEventListener('input', function() {
    if (this.classList.contains('is-invalid')) {
        const name = this.value;
        if (name !== '' && validateName(name)) {
            showSuccess('nameInput', 'nameError');
        }
    }
});

// Gender Validation
document.getElementById('genderInput').addEventListener('change', function() {
    if (this.value === '') {
        showError('genderInput', 'genderError', 'Please select a gender');
    } else {
        showSuccess('genderInput', 'genderError');
    }
});

// Contact Validation
document.getElementById('contactInput').addEventListener('blur', function() {
    const contact = this.value;
    
    if (contact === '') {
        showError('contactInput', 'contactError', 'Contact number is required');
    } else if (!validateContact(contact)) {
        showError('contactInput', 'contactError', 'Contact must be exactly 10 digits');
    } else {
        showSuccess('contactInput', 'contactError');
    }
});

document.getElementById('contactInput').addEventListener('input', function() {
    // Allow only numbers
    this.value = this.value.replace(/[^0-9]/g, '');
    
    if (this.classList.contains('is-invalid')) {
        const contact = this.value;
        if (validateContact(contact)) {
            showSuccess('contactInput', 'contactError');
        }
    }
});

// Address Validation
document.getElementById('addressInput').addEventListener('blur', function() {
    const address = this.value;
    
    if (address === '') {
        showError('addressInput', 'addressError', 'Address is required');
    } else if (!validateAddress(address)) {
        showError('addressInput', 'addressError', 'Address must be between 10 and 200 characters');
    } else {
        showSuccess('addressInput', 'addressError');
    }
});

document.getElementById('addressInput').addEventListener('input', function() {
    if (this.classList.contains('is-invalid')) {
        const address = this.value;
        if (address !== '' && validateAddress(address)) {
            showSuccess('addressInput', 'addressError');
        }
    }
});

// ========================================
// PHOTO VALIDATION AND PREVIEW
// ========================================

function previewImage(event) {
    const file = event.target.files[0];
    const errorElement = document.getElementById('photoError');
    
    if (file) {
        const validation = validatePhoto(file);
        
        if (!validation.valid) {
            errorElement.textContent = validation.message;
            errorElement.style.color = '#ff4757';
            event.target.value = '';
            return;
        }
        
        errorElement.textContent = '';
        document.getElementById('previewImg').src = URL.createObjectURL(file);
    }
}

// ========================================
// FORM SUBMISSION VALIDATION
// ========================================

document.getElementById('profileForm').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Validate Name
    const name = document.getElementById('nameInput').value;
    if (name === '') {
        showError('nameInput', 'nameError', 'Name is required');
        isValid = false;
    } else if (!validateName(name)) {
        showError('nameInput', 'nameError', 'Name must be 2-50 characters and contain only letters, spaces, dots, hyphens, or apostrophes');
        isValid = false;
    }
    
    // Validate Gender
    const gender = document.getElementById('genderInput').value;
    if (gender === '') {
        showError('genderInput', 'genderError', 'Please select a gender');
        isValid = false;
    }
    
    // Validate Contact
    const contact = document.getElementById('contactInput').value;
    if (contact === '') {
        showError('contactInput', 'contactError', 'Contact number is required');
        isValid = false;
    } else if (!validateContact(contact)) {
        showError('contactInput', 'contactError', 'Contact must be exactly 10 digits');
        isValid = false;
    }
    
    // Validate Address
    const address = document.getElementById('addressInput').value;
    if (address === '') {
        showError('addressInput', 'addressError', 'Address is required');
        isValid = false;
    } else if (!validateAddress(address)) {
        showError('addressInput', 'addressError', 'Address must be between 10 and 200 characters');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
        
        // Scroll to first error
        const firstError = document.querySelector('.is-invalid');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }
});

// ========================================
// EDIT MODE FUNCTIONALITY
// ========================================

document.getElementById("editBtn").addEventListener("click", () => {
    // Enable all fields except readonly email
    document.querySelectorAll("#profileForm input:not([readonly]), #profileForm select, #profileForm textarea")
        .forEach(el => el.disabled = false);

    document.getElementById("saveBtn").style.display = "inline-block";
    document.getElementById("cancelBtn").style.display = "inline-block";
    document.getElementById("editBtn").style.display = "none";
});

// Cancel Button
document.getElementById("cancelBtn").addEventListener("click", () => {
    location.reload();
});

</script>