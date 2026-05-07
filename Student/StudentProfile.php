<?php
include("../Includes/StudentHeader.php");
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}

include("../Includes/StudentSidebar.php");

$student_id = $_SESSION['student_id'];

/* Fetch current student data */
$q = mysqli_query($con, "SELECT * FROM tbl_student WHERE student_id='$student_id'");
$student = mysqli_fetch_assoc($q);

$err = $success = "";

/* On Edit Save */
if (isset($_POST['btn_save'])) {

    $name    = $_POST['student_name'];
    $email   = $_POST['student_email'];
    $gender  = $_POST['student_gender'];
    $contact = $_POST['student_contact'];
    $address = $_POST['student_address'];

    $photo = $student['student_photo'];

    /* Photo Upload */
    if ($_FILES['student_photo']['size'] > 0) {
        $file_name = $_FILES['student_photo']['name'];
        $tmp = $_FILES['student_photo']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed = array("jpg", "jpeg", "png");

        if (in_array($ext, $allowed)) {
            $newname = "stud_" . $student_id . "_" . time() . "." . $ext;
            $path = "../Assets/Files/Student/" . $newname;

            if (move_uploaded_file($tmp, $path)) {

                if ($student['student_photo'] != "" &&
                    file_exists("../Assets/Files/Student/" . $student['student_photo'])) {
                    unlink("../Assets/Files/Student/" . $student['student_photo']);
                }

                $photo = $newname;
            }
        }
    }

    /* Update Query */
    $update = "
        UPDATE tbl_student SET
        student_name='$name',
        student_email='$email',
        student_gender='$gender',
        student_contact='$contact',
        student_address='$address',
        student_photo='$photo'
        WHERE student_id='$student_id'
    ";

    if (mysqli_query($con, $update)) {
        $success = "Profile updated successfully!";
        $q = mysqli_query($con, "SELECT * FROM tbl_student WHERE student_id='$student_id'");
        $student = mysqli_fetch_assoc($q);
    } else {
        $err = "Update failed!";
    }
}

$page_title = "My Profile";
?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Header -->
    <div class="glass-card text-center">
        <h2 class="text-gradient"><i class="fas fa-user-graduate"></i> My Profile</h2>
        <p style="color:var(--text-secondary);">View or update your details</p>
    </div>

    <!-- Profile Card -->
    <div class="glass-card" style="max-width:850px;margin:auto;">

        <!-- Top Section: Photo + Name -->
        <div class="text-center mb-3">
            <img id="previewPhoto"
                 src="../Assets/Files/Student/<?= $student['student_photo'] ?: 'default.png' ?>?v=<?= time(); ?>"
                 style="width:140px;height:140px;border-radius:50%;border:4px solid var(--gradient-1);object-fit:cover;box-shadow:0 0 25px rgba(102,126,234,0.4);">

            <h3 class="text-gradient mt-2"><?= $student['student_name'] ?></h3>
            <p style="color:var(--text-muted);"><?= $student['student_email'] ?></p>
        </div>

        <!-- Alerts -->
        <?php if ($err): ?>
            <div class="glass-card" style="border-left:4px solid red;color:red;"><?= $err ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="glass-card" style="border-left:4px solid green;color:green;"><?= $success ?></div>
        <?php endif; ?>

        <!-- FORM START -->
        <form method="post" enctype="multipart/form-data" id="profileForm">

            <!-- PHOTO -->
            <div class="form-group mb-3 edit-only" style="display:none;">
                <label class="form-label">Update Photo</label>
                <input type="file" name="student_photo" accept="image/*" class="form-control"
                       id="photoInput" onchange="loadFile(event)">
                <small class="form-text error-message" id="photoError"></small>
            </div>

            <!-- TWO COLUMN GRID -->
            <div class="profile-grid">
                
                <div>
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="student_name" class="form-control" id="nameInput"
                           value="<?= $student['student_name']; ?>" required readonly>
                    <small class="form-text error-message" id="nameError"></small>
                </div>

                <div>
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="student_email" class="form-control" id="emailInput"
                           value="<?= $student['student_email']; ?>" required readonly>
                    <small class="form-text error-message" id="emailError"></small>
                </div>

                <div>
                    <label class="form-label">Gender <span class="required">*</span></label>
                    <select name="student_gender" class="form-control" id="genderInput" readonly disabled>
                        <option value="">Select Gender</option>
                        <option <?= $student['student_gender']=="Male"?"selected":"" ?>>Male</option>
                        <option <?= $student['student_gender']=="Female"?"selected":"" ?>>Female</option>
                    </select>
                    <small class="form-text error-message" id="genderError"></small>
                </div>

                <div>
                    <label class="form-label">Contact <span class="required">*</span></label>
                    <input type="text" name="student_contact" class="form-control" id="contactInput"
                           value="<?= $student['student_contact']; ?>" required readonly 
                           placeholder="e.g., 1234567890">
                    <small class="form-text error-message" id="contactError"></small>
                </div>

                <!-- ADDRESS (FULL WIDTH) -->
                <div class="full">
                    <label class="form-label">Address <span class="required">*</span></label>
                    <textarea name="student_address" class="form-control" id="addressInput"
                              style="height:120px;" required readonly><?= $student['student_address']; ?></textarea>
                    <small class="form-text error-message" id="addressError"></small>
                </div>

            </div>

            <div class="text-center mt-4">
            
                <button type="button" id="editBtn"
                        class="profile-btn profile-btn-outline">
                    <i class="fas fa-edit"></i> Edit
                </button>

                <button type="submit" name="btn_save" id="saveBtn"
                        class="profile-btn profile-btn-primary" style="display:none;">
                    <i class="fas fa-save"></i> Save
                </button>

                <button type="button" id="cancelBtn"
                        class="profile-btn profile-btn-muted" style="display:none;">
                    <i class="fas fa-times"></i> Cancel
                </button>

                <a href="StudentChangePassword.php"
                   class="profile-btn profile-btn-primary">
                    <i class="fas fa-key"></i> Change Password
                </a>

            </div>

        </form>
        <!-- FORM END -->

    </div>
</div>

<!-- INTERNAL STYLE FOR GRID -->
<style>
.profile-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.2rem;
}
.profile-grid .full {
    grid-column: 1 / 3;
}

@media(max-width:768px){
    .profile-grid {
        grid-template-columns: 1fr;
    }
    .profile-grid .full {
        grid-column: 1 / 2;
    }
}

/* Required field indicator */
.required {
    color: #ff4757;
    font-weight: bold;
}

/* Error message styling */
.error-message {
    color: #ff4757;
    font-size: 0.85rem;
    display: block;
    margin-top: 0.3rem;
    min-height: 1.2rem;
}

/* Input validation states */
.form-control.is-invalid {
    border-color: #ff4757;
    box-shadow: 0 0 0 0.2rem rgba(255, 71, 87, 0.25);
}

.form-control.is-valid {
    border-color: #2ed573;
    box-shadow: 0 0 0 0.2rem rgba(46, 213, 115, 0.25);
}

/* ===============================
   B1 – MODERN ROUNDED BUTTONS
=============================== */

.profile-btn {
    padding: 0.85rem 2.2rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    border: none;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    letter-spacing: 0.3px;
}

/* Primary gradient button */
.profile-btn-primary {
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    color: #fff;
    box-shadow: 0 8px 25px rgba(102,126,234,0.45);
}

.profile-btn-primary:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 12px 35px rgba(102,126,234,0.7);
}

/* Outline glass buttons */
.profile-btn-outline {
    background: rgba(255,255,255,0.03);
    border: 2px solid var(--gradient-1);
    color: var(--gradient-1);
    backdrop-filter: blur(12px);
    box-shadow: 0 5px 12px rgba(102,126,234,0.15);
}

.profile-btn-outline:hover {
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    color: white;
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 12px 28px rgba(102,126,234,0.45);
}

/* Muted grey button (Cancel) */
.profile-btn-muted {
    background: rgba(255,255,255,0.05);
    border: 2px solid rgba(255,255,255,0.15);
    color: var(--text-secondary);
    backdrop-filter: blur(10px);
}

.profile-btn-muted:hover {
    background: rgba(255,255,255,0.12);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(255,255,255,0.12);
}

</style>

<!-- VALIDATION AND EDIT MODE JS -->
<script>

// ========================================
// VALIDATION FUNCTIONS
// ========================================

function validateName(name) {
    const nameRegex = /^[a-zA-Z\s.'-]{2,50}$/;
    return nameRegex.test(name.trim());
}

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email.trim());
}

function validateContact(contact) {
    const contactRegex = /^[0-9]{10}$/;
    return contactRegex.test(contact.trim());
}

function validateAddress(address) {
    return address.trim().length >= 10 && address.trim().length <= 200;
}

function validatePhoto(file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    const maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!allowedTypes.includes(file.type)) {
        return { valid: false, message: 'Only JPG, JPEG, and PNG files are allowed' };
    }
    
    if (file.size > maxSize) {
        return { valid: false, message: 'File size must be less than 2MB' };
    }
    
    return { valid: true, message: '' };
}

// ========================================
// REAL-TIME VALIDATION
// ========================================

function showError(inputId, errorId, message) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    error.textContent = message;
}

function showSuccess(inputId, errorId) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    
    input.classList.add('is-valid');
    input.classList.remove('is-invalid');
    error.textContent = '';
}

function clearValidation(inputId, errorId) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    
    input.classList.remove('is-valid', 'is-invalid');
    error.textContent = '';
}

// ========================================
// INPUT EVENT LISTENERS
// ========================================

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
        if (validateName(name)) {
            showSuccess('nameInput', 'nameError');
        }
    }
});

document.getElementById('emailInput').addEventListener('blur', function() {
    const email = this.value;
    
    if (email === '') {
        showError('emailInput', 'emailError', 'Email is required');
    } else if (!validateEmail(email)) {
        showError('emailInput', 'emailError', 'Please enter a valid email address');
    } else {
        showSuccess('emailInput', 'emailError');
    }
});

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

document.getElementById('genderInput').addEventListener('change', function() {
    if (this.value === '') {
        showError('genderInput', 'genderError', 'Please select a gender');
    } else {
        showSuccess('genderInput', 'genderError');
    }
});

// ========================================
// PHOTO VALIDATION
// ========================================

function loadFile(event) {
    const file = event.target.files[0];
    
    if (file) {
        const validation = validatePhoto(file);
        
        if (!validation.valid) {
            showError('photoInput', 'photoError', validation.message);
            event.target.value = '';
            return;
        }
        
        showSuccess('photoInput', 'photoError');
        document.getElementById('previewPhoto').src = URL.createObjectURL(file);
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
        showError('nameInput', 'nameError', 'Name must be 2-50 characters and contain only letters');
        isValid = false;
    }
    
    // Validate Email
    const email = document.getElementById('emailInput').value;
    if (email === '') {
        showError('emailInput', 'emailError', 'Email is required');
        isValid = false;
    } else if (!validateEmail(email)) {
        showError('emailInput', 'emailError', 'Please enter a valid email address');
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

document.getElementById("editBtn").onclick = function() {
    // Enable all fields except email
    document.querySelectorAll("input, select, textarea").forEach(el => {
        if(el.name !== "student_email") {
            el.removeAttribute("readonly");
            el.removeAttribute("disabled");
        }
    });

    document.querySelectorAll(".edit-only").forEach(el => el.style.display="block");

    document.getElementById("editBtn").style.display="none";
    document.getElementById("saveBtn").style.display="inline-flex";
    document.getElementById("cancelBtn").style.display="inline-flex";
};

document.getElementById("cancelBtn").onclick = function() {
    location.reload();
};

</script>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>