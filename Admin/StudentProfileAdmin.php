<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");

$return_url = $_GET['return_url'] ?? 'AdminHome.php';
$student_id = (int)$_GET['student_id'];

$errors = [];

// Fetch student details
$studentQry = "
    SELECT s.*, cl.course_id, cl.class_name, c.course_name
    FROM tbl_student s
    JOIN tbl_class cl ON s.class_id = cl.class_id
    JOIN tbl_course c ON cl.course_id = c.course_id
    WHERE s.student_id='$student_id'
";
$studentRes = mysqli_query($con, $studentQry);

if(!$studentRes || mysqli_num_rows($studentRes) == 0) {
    echo "<script>alert('Student not found'); window.location='ViewStudents.php';</script>";
    exit;
}

$student = mysqli_fetch_assoc($studentRes);

// FIXED: Use unique variable name to avoid conflicts with AdminHeader/Sidebar
$student_photo_filename = 'default.png'; // Default

if(!empty($student['student_photo']) && $student['student_photo'] !== 'default.png') {
    // Student has a specific photo set
    $check_student_photo = "../Assets/Files/Student/" . $student['student_photo'];
    
    if(file_exists($check_student_photo)) {
        // Photo file exists, use it
        $student_photo_filename = $student['student_photo'];
    } else {
        // Photo filename in DB but file doesn't exist, use default
        $student_photo_filename = 'default.png';
    }
} else {
    // No photo set in database, use default
    $student_photo_filename = 'default.png';
}

// Final photo path for display - using unique variable name
$student_display_photo = "../Assets/Files/Student/" . $student_photo_filename;

// Double-check default exists
if(!file_exists($student_display_photo)) {
    $student_display_photo = "../Assets/Files/Student/default.png";
}

// Fetch classes
$classRes = mysqli_query($con, "
    SELECT cl.*, c.course_name
    FROM tbl_class cl
    JOIN tbl_course c ON cl.course_id = c.course_id
    WHERE cl.course_id = '{$student['course_id']}'
      AND cl.is_completed = 0
    ORDER BY cl.class_name ASC
");

// Handle update
if(isset($_POST['btn_update'])) {
    
    $name = trim($_POST['student_name']);
    $email = trim($_POST['student_email']);
    $password = trim($_POST['student_password']);
    $contact = trim($_POST['student_contact']);
    $address = trim($_POST['student_address']);
    $class_id = (int)$_POST['class_id'];

    // Server-side validation
    if(empty($name)){
        $errors[] = "Name is required";
    } elseif(strlen($name) < 3){
        $errors[] = "Name must be at least 3 characters";
    }

    if(empty($email)){
        $errors[] = "Email is required";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[] = "Invalid email format";
    }

    if(empty($password)){
        $errors[] = "Password is required";
    } elseif(strlen($password) < 6){
        $errors[] = "Password must be at least 6 characters";
    }

    if(empty($contact)){
        $errors[] = "Contact is required";
    } elseif(!preg_match('/^[0-9]{10}$/', $contact)){
        $errors[] = "Contact must be exactly 10 digits";
    }

    if(empty($address)){
        $errors[] = "Address is required";
    } elseif(strlen($address) < 5){
        $errors[] = "Address must be at least 5 characters";
    }

    if($class_id <= 0){
        $errors[] = "Please select a valid class";
    }

    // Photo upload handling
    $new_photo_name = $student['student_photo']; // Keep existing photo by default
    
    // Check if file was uploaded
    if(isset($_FILES['student_photo']) && !empty($_FILES['student_photo']['name'])){
        
        $file_error = $_FILES['student_photo']['error'];
        
        // Check for upload errors
        if($file_error === UPLOAD_ERR_OK){
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $file_tmp = $_FILES['student_photo']['tmp_name'];
            $file_name = $_FILES['student_photo']['name'];
            $file_size = $_FILES['student_photo']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Validate file type
            if(!in_array($file_ext, $allowed_ext)){
                $errors[] = "Photo must be JPG, JPEG, PNG, or GIF";
            }
            // Validate file size (5MB)
            elseif($file_size > 5 * 1024 * 1024){
                $errors[] = "Photo size must be less than 5MB";
            }
            // Check if temp file exists
            elseif(!file_exists($file_tmp)){
                $errors[] = "Temporary file not found. Upload failed.";
            }
            else {
                // Create upload directory if it doesn't exist
                $upload_dir = "../Assets/Files/Student/";
                if(!is_dir($upload_dir)){
                    mkdir($upload_dir, 0777, true);
                }

                // Create new filename with timestamp to ensure uniqueness
                $new_photo_name = "student_".$student_id."_".time().".".$file_ext;
                $upload_path = $upload_dir.$new_photo_name;

                // Attempt to move uploaded file
                if(move_uploaded_file($file_tmp, $upload_path)){
                    // Successfully uploaded, now delete old photo
                    if(!empty($student['student_photo']) && 
                       $student['student_photo'] !== 'default.png' && 
                       file_exists($upload_dir.$student['student_photo'])){
                        @unlink($upload_dir.$student['student_photo']);
                    }
                    
                    // Set proper permissions
                    @chmod($upload_path, 0644);
                } else {
                    $errors[] = "Failed to move uploaded file. Check folder permissions (chmod 777 on Student folder).";
                    $new_photo_name = $student['student_photo']; // Keep old photo
                }
            }
        } elseif($file_error === UPLOAD_ERR_INI_SIZE || $file_error === UPLOAD_ERR_FORM_SIZE){
            $errors[] = "File is too large. Maximum size is 5MB.";
        } elseif($file_error === UPLOAD_ERR_PARTIAL){
            $errors[] = "File was only partially uploaded. Please try again.";
        } elseif($file_error === UPLOAD_ERR_NO_TMP_DIR){
            $errors[] = "Missing temporary folder. Contact administrator.";
        } elseif($file_error === UPLOAD_ERR_CANT_WRITE){
            $errors[] = "Failed to write file to disk. Check server permissions.";
        }
    }

    // Update database if no errors
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
                student_photo='$new_photo_name'
            WHERE student_id='$student_id'
        ";
        
        if(mysqli_query($con, $updateQry)){
            echo "<script>alert('Profile updated successfully'); window.location='StudentProfileAdmin.php?student_id=$student_id&return_url=".urlencode($return_url)."';</script>";
            exit;
        } else {
            $errors[] = "Database update failed: " . mysqli_error($con);
        }
    }
}

$page_title = "Student Profile";
$breadcrumb = '<span>Students</span> <i class="fas fa-chevron-right"></i> <span>Profile</span>';
include("../Includes/Header.php");
include("../Includes/Sidebar.php");
?>

<!-- INTERNAL FIXED CSS -->
<style>
/* Scoped only to this page */
.profile-section {
  width: 100%;
  max-width: 850px;
  margin: 2rem auto;
}

.profile-card-fixed {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 1.5rem 2rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
  color: var(--text-primary);
  display: flex;
  gap: 2rem;
  align-items: center;
  justify-content: center;
  min-height: 400px;
}

.profile-image-fixed {
  flex: 1 1 200px;
  text-align: center;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
.profile-image-fixed img {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--gradient-1);
  box-shadow: 0 0 15px rgba(102,126,234,0.4);
  transition: transform 0.3s ease;
}
.profile-image-fixed img:hover {
  transform: scale(1.05);
}

.student-info-badge {
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background: rgba(102,126,234,0.2);
  border-radius: 8px;
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.profile-form-fixed {
  flex: 1 1 400px;
}
.profile-form-fixed form {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.profile-form-fixed label {
  font-weight: 600;
  color: var(--text-secondary);
  text-align: left;
  font-size: 0.9rem;
  margin-bottom: 0.2rem;
}

.profile-form-fixed input,
.profile-form-fixed textarea,
.profile-form-fixed select {
  width: 100%;
  padding: 0.65rem 0.9rem;
  border-radius: 8px;
  border: 1px solid var(--border-glass);
  background: rgba(255,255,255,0.08);
  color: var(--text-primary);
  font-size: 0.9rem;
  outline: none;
  transition: all 0.3s ease;
}
.profile-form-fixed input:focus,
.profile-form-fixed textarea:focus,
.profile-form-fixed select:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 5px rgba(99,102,241,0.3);
}
textarea {
  resize: none;
  min-height: 60px;
}

/* Dropdown background fix */
select option {
  background: var(--secondary-bg);
  color: var(--text-primary);
}

/* Error messages */
.error-messages {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.4);
  border-radius: 10px;
  padding: 1rem;
  margin-bottom: 1rem;
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
  background: rgba(239, 68, 68, 0.05) !important;
}

/* Save button */
button[name="btn_update"] {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 0.8rem;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  margin-top: 0.5rem;
  transition: all 0.3s ease;
}
button[name="btn_update"]:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}

/* Back button */
.back-button {
  margin-bottom: 1rem;
}
.back-button a {
  background: rgba(255,255,255,0.08);
  padding: 0.5rem 0.9rem;
  border-radius: 6px;
  color: var(--gradient-1);
  text-decoration: none;
  font-weight: 600;
  transition: 0.3s;
}
.back-button a:hover {
  background: rgba(255,255,255,0.15);
}

.file-hint {
  font-size: 0.8rem;
  color: rgba(255,255,255,0.5);
  margin-top: 0.2rem;
  display: block;
}

/* Responsive adjustments */
@media(max-width:768px){
  .profile-card-fixed {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  .profile-form-fixed { width: 100%; }
}
</style>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="profile-section">
    <div class="back-button">
      <a href="<?php echo htmlspecialchars($return_url); ?>"><i class="fas fa-arrow-left"></i> Back</a>
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

    <div class="profile-card-fixed">
      <div class="profile-image-fixed">
        <img src="<?= $student_display_photo ?>?v=<?= time() ?>" id="studentPhotoPreview" alt="<?= htmlspecialchars($student['student_name']) ?>">
        <div class="student-info-badge">
          <strong><?= htmlspecialchars($student['student_name']) ?></strong><br>
         <!-- ID: <?= $student_id ?><br> -->
          <?= htmlspecialchars($student['course_name']) ?><br>
          <?= htmlspecialchars($student['class_name']) ?>
        </div>
      </div>

      <div class="profile-form-fixed">
        <form method="post" enctype="multipart/form-data" id="studentProfileForm" accept-charset="UTF-8">
          
          <label for="student_photo">Change Photo:</label>
          <input type="file" name="student_photo" id="student_photo" accept="image/jpeg,image/jpg,image/png,image/gif">
          <span class="file-hint" id="fileNameDisplay">Max 5MB (JPG, PNG, GIF)</span>

          <label for="student_name">Name: <span style="color:#f87171;">*</span></label>
          <input type="text" name="student_name" id="student_name" value="<?=htmlspecialchars($student['student_name'])?>" required>

          <label for="student_email">Email: <span style="color:#f87171;">*</span></label>
          <input type="email" name="student_email" id="student_email" value="<?=htmlspecialchars($student['student_email'])?>" required>

          <label for="student_password">Password: <span style="color:#f87171;">*</span></label>
          <input type="text" name="student_password" id="student_password" value="<?=htmlspecialchars($student['student_password'])?>" required>

          <label for="student_contact">Contact: <span style="color:#f87171;">*</span></label>
          <input type="text" name="student_contact" id="student_contact" value="<?=htmlspecialchars($student['student_contact'])?>" pattern="[0-9]{10}" maxlength="10" required>

          <label for="student_address">Address: <span style="color:#f87171;">*</span></label>
          <textarea name="student_address" id="student_address" required><?=htmlspecialchars($student['student_address'])?></textarea>

          <label for="class_id">Class: <span style="color:#f87171;">*</span></label>
          <select name="class_id" id="class_id" required>
            <option value="">-- Select Class --</option>
            <?php 
            mysqli_data_seek($classRes, 0);
            while($cl = mysqli_fetch_assoc($classRes)): 
            ?>
              <option value="<?=$cl['class_id']?>" <?=($cl['class_id']==$student['class_id'])?'selected':''?>>
                <?=htmlspecialchars($cl['course_name']." - ".$cl['class_name'])?>
              </option>
            <?php endwhile; ?>
          </select>

          <button type="submit" name="btn_update"><i class="fas fa-save"></i> Update Profile</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Keep your universal JS and then add validation script -->
<script src="../Assets/JS/universal.js"></script>

<script>
// ====== Photo preview with validation ======
const photoInput = document.getElementById('student_photo');
const photoPreview = document.getElementById('studentPhotoPreview');
const fileNameDisplay = document.getElementById('fileNameDisplay');

if(photoInput && photoPreview){
    photoInput.addEventListener('change', function(e){
        const file = this.files[0];
        
        if(!file) {
            fileNameDisplay.textContent = 'Max 5MB (JPG, PNG, GIF)';
            fileNameDisplay.style.color = 'rgba(255,255,255,0.5)';
            return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if(!allowedTypes.includes(file.type)){
            alert('❌ Please select a valid image (JPG, PNG, or GIF)');
            this.value = '';
            fileNameDisplay.textContent = 'Max 5MB (JPG, PNG, GIF)';
            fileNameDisplay.style.color = 'rgba(255,255,255,0.5)';
            return;
        }

        // Validate file size (5MB)
        if(file.size > 5 * 1024 * 1024){
            alert('❌ Photo size must be less than 5MB');
            this.value = '';
            fileNameDisplay.textContent = 'Max 5MB (JPG, PNG, GIF)';
            fileNameDisplay.style.color = 'rgba(255,255,255,0.5)';
            return;
        }

        // Show file name and size
        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
        fileNameDisplay.textContent = `Selected: ${file.name} (${fileSizeMB} MB)`;
        fileNameDisplay.style.color = '#10b981';

        // Show preview
        const url = URL.createObjectURL(file);
        photoPreview.src = url;
        
        console.log('File selected:', file.name, 'Size:', file.size, 'Type:', file.type);
    });
}

// ====== Real-time input restrictions ======
// Name: letters and spaces only
const nameEl = document.getElementById('student_name');
if(nameEl){
    nameEl.addEventListener('input', function(){
        this.value = this.value.replace(/[^A-Za-z ]/g, '').substring(0, 100);
        this.classList.remove('input-error');
    });
}

// Contact: digits only, max 10
const contactEl = document.getElementById('student_contact');
if(contactEl){
    contactEl.addEventListener('input', function(){
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10);
        this.classList.remove('input-error');
    });
}

// Password: remove spaces
const passEl = document.getElementById('student_password');
if(passEl){
    passEl.addEventListener('input', function(){
        this.value = this.value.replace(/\s/g, '').substring(0, 100);
        this.classList.remove('input-error');
    });
}

// Remove error on input for all fields
document.querySelectorAll('input, select, textarea').forEach(element => {
    element.addEventListener('input', function() {
        this.classList.remove('input-error');
    });
});

// ====== Form validation on submit ======
const form = document.getElementById('studentProfileForm');
if(form){
    form.addEventListener('submit', function(e){
        let isValid = true;
        const errors = [];

        // Clear previous error styling
        document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

        const name = nameEl ? nameEl.value.trim() : '';
        const email = document.getElementById('student_email') ? document.getElementById('student_email').value.trim() : '';
        const password = passEl ? passEl.value.trim() : '';
        const contact = contactEl ? contactEl.value.trim() : '';
        const address = document.getElementById('student_address') ? document.getElementById('student_address').value.trim() : '';
        const classId = document.getElementById('class_id') ? document.getElementById('class_id').value : '';

        // Name validation
        if(name === ''){
            errors.push('Name is required');
            if(nameEl) nameEl.classList.add('input-error');
            isValid = false;
        } else if(name.length < 3){
            errors.push('Name must be at least 3 characters');
            if(nameEl) nameEl.classList.add('input-error');
            isValid = false;
        } else if(!/^[A-Za-z ]+$/.test(name)){
            errors.push('Name may contain only letters and spaces');
            if(nameEl) nameEl.classList.add('input-error');
            isValid = false;
        }

        // Email validation
        if(email === ''){
            errors.push('Email is required');
            document.getElementById('student_email').classList.add('input-error');
            isValid = false;
        } else {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!emailPattern.test(email)){
                errors.push('Enter a valid email address');
                document.getElementById('student_email').classList.add('input-error');
                isValid = false;
            }
        }

        // Password validation
        if(password === ''){
            errors.push('Password is required');
            if(passEl) passEl.classList.add('input-error');
            isValid = false;
        } else if(password.length < 6){
            errors.push('Password must be at least 6 characters');
            if(passEl) passEl.classList.add('input-error');
            isValid = false;
        }

        // Contact validation
        if(contact === ''){
            errors.push('Contact number is required');
            if(contactEl) contactEl.classList.add('input-error');
            isValid = false;
        } else if(!/^[0-9]{10}$/.test(contact)){
            errors.push('Contact must be exactly 10 digits');
            if(contactEl) contactEl.classList.add('input-error');
            isValid = false;
        }

        // Address validation
        if(address === ''){
            errors.push('Address is required');
            document.getElementById('student_address').classList.add('input-error');
            isValid = false;
        } else if(address.length < 5){
            errors.push('Address must be at least 5 characters');
            document.getElementById('student_address').classList.add('input-error');
            isValid = false;
        }

        // Class validation
        if(!classId){
            errors.push('Please select a class');
            document.getElementById('class_id').classList.add('input-error');
            isValid = false;
        }

        // Photo validation (if file selected)
        if(photoInput && photoInput.files.length > 0){
            const file = photoInput.files[0];
            const fname = file.name.toLowerCase();
            const ext = fname.split('.').pop();
            const allowed = ['jpg','jpeg','png','gif'];
            if(!allowed.includes(ext)){
                errors.push('Photo must be JPG, JPEG, PNG, or GIF');
                isValid = false;
            }
            const maxSize = 5 * 1024 * 1024; // 5MB
            if(file.size > maxSize){
                errors.push('Photo must be less than 5MB');
                isValid = false;
            }
        }

        if(!isValid){
            e.preventDefault();
            alert('❌ Please correct the following errors:\n\n' + errors.join('\n'));
            return false;
        }

        return true;
    });
}
</script>
</body>
</html>