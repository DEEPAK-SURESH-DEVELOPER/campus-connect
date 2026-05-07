<?php
include("../Includes/HODHeader.php"); 

date_default_timezone_set('Asia/Kolkata');

if(!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true){
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}

include("../Includes/HODSidebar.php");

$teacher_id = (int)$_SESSION['hod_id'];

// Function to fetch teacher record
function getTeacherData($con, $teacher_id) {
    $qry = "SELECT * FROM tbl_teacher WHERE teacher_id = ?";
    $stmt = $con->prepare($qry);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row;
}

$teacher = getTeacherData($con, $teacher_id);

if(!$teacher){
    echo "<script>alert('Profile not found'); window.location='../Guest/Login.php';</script>";
    exit;
}

$photo = $teacher['teacher_photo'] ?: '';
$photo_path = (!empty($photo) && file_exists("../Assets/Files/Teacher/".$photo))
    ? "../Assets/Files/Teacher/".$photo
    : "../Assets/Files/Teacher/default.png";

$err = "";
$success = "";
$errors = [];

// --------------------------------------------------
//                SAVE PROFILE UPDATE
// --------------------------------------------------
if(isset($_POST['btn_save'])){

    $name    = trim($_POST['teacher_name']);
    $gender  = $_POST['teacher_gender'];
    $address = trim($_POST['teacher_address']);
    $contact = trim($_POST['teacher_contact']);

    // Server-side Validation
    if(empty($name)){
        $errors[] = "Name is required";
    } elseif(strlen($name) < 3){
        $errors[] = "Name must be at least 3 characters";
    }

    if(empty($gender) || !in_array($gender, ['Male', 'Female', 'Other'])){
        $errors[] = "Please select a valid gender";
    }

    if(empty($contact)){
        $errors[] = "Contact is required";
    } elseif(!preg_match('/^[0-9]{10}$/', $contact)){
        $errors[] = "Contact must be exactly 10 digits";
    }

    if(empty($address)){
        $errors[] = "Address is required";
    } elseif(strlen($address) < 10){
        $errors[] = "Address must be at least 10 characters";
    }

    // Fetch latest data BEFORE update
    $fresh = getTeacherData($con, $teacher_id);

    // default keep existing photo
    $photoName = $fresh['teacher_photo'];

    // ------------------------------------------
    //          Handle Photo Upload
    // ------------------------------------------
    if(isset($_FILES['teacher_photo']) && $_FILES['teacher_photo']['error'] === 0){
        
        $allowed = ['jpg','jpeg','png','gif'];
        $fileName = $_FILES['teacher_photo']['name'];
        $fileTmp  = $_FILES['teacher_photo']['tmp_name'];
        $fileSize = $_FILES['teacher_photo']['size'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file type
        if(!in_array($ext, $allowed)){
            $errors[] = "Invalid file type! Upload JPG, JPEG, PNG or GIF only.";
        }
        // Validate file size (2MB max)
        elseif($fileSize > 2 * 1024 * 1024){
            $errors[] = "Photo size must be less than 2MB";
        }
        else {
            $newName = "teacher_" . $teacher_id . "_" . time() . "." . $ext;
            $uploadPath = "../Assets/Files/Teacher/" . $newName;

            if(move_uploaded_file($fileTmp, $uploadPath)){

                // delete old photo (if not default)
                if(!empty($fresh['teacher_photo']) && 
                   $fresh['teacher_photo'] != 'default.png' &&
                   file_exists("../Assets/Files/Teacher/" . $fresh['teacher_photo'])){
                        
                        @unlink("../Assets/Files/Teacher/" . $fresh['teacher_photo']);
                }

                $photoName = $newName;

            } else {
                $errors[] = "Error uploading photo! Please check folder permissions.";
            }
        }
    }

    // ------------------------------------------
    //     Update database only if no errors
    // ------------------------------------------
    if(empty($errors)){

        $name = mysqli_real_escape_string($con, $name);
        $address = mysqli_real_escape_string($con, $address);
        $contact = mysqli_real_escape_string($con, $contact);

        $updateQry = "UPDATE tbl_teacher SET 
                        teacher_name=?, 
                        teacher_gender=?, 
                        teacher_address=?, 
                        teacher_contact=?, 
                        teacher_photo=?
                      WHERE teacher_id=?";

        $stmt = $con->prepare($updateQry);
        $stmt->bind_param("sssssi",
            $name, $gender, $address, $contact, $photoName, $teacher_id
        );

        if($stmt->execute()){
            echo "<script>
                alert('Profile updated successfully!');
                window.location.href = '".$_SERVER['PHP_SELF']."?v=' + new Date().getTime();
            </script>";
            exit;
        } else {
            $errors[] = "Update failed! Please try again.";
        }
        $stmt->close();
    } else {
        $err = implode("<br>", $errors);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>HOD Profile</title>

<!-- Font & icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Include universal theme so header/sidebar match -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
/* Layout */
.main-content {
  padding: 2rem;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}
.profile-container {
  background: rgba(255,255,255,0.03);
  border: 1px solid var(--border-glass);
  border-radius: 14px;
  padding: 1.6rem;
  box-shadow: 0 8px 25px rgba(0,0,0,0.28);
  color: var(--text-primary);
  max-width: 900px;
  width: 100%;
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 1.4rem;
  align-items: start;
}

/* Photo column */
.profile-image {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding-top: 10px;
}
.profile-image img {
  width: 165px;
  height: 165px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid var(--gradient-1);
  box-shadow: 0 0 25px rgba(102,126,234,0.45);
}
.photo-note {
  font-size:0.9rem;
  color:var(--text-secondary);
  text-align:center;
}
.change-photo {
  display:none; /* shown only in edit mode via JS */
  margin-top:8px;
  text-align: center;
}
.change-photo input[type=file] {
  display:block;
  background:rgba(255,255,255,0.04);
  padding:8px;
  border-radius:8px;
  color:var(--text-primary);
  margin-bottom: 0.5rem;
}
.file-hint {
  font-size: 0.8rem;
  color: rgba(255,255,255,0.5);
  display: block;
  margin-top: 0.3rem;
}

/* Info column */
.profile-info {
  display:flex;
  flex-direction:column;
}
.profile-header {
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:0.6rem;
  gap:12px;
}
.profile-header h1 {
  margin:0;
  font-size:1.4rem;
  color:var(--gradient-1);
  display:flex;
  align-items:center;
  gap:0.5rem;
}
.action-buttons {
  display:flex;
  gap:8px;
  align-items:center;
}
.btn {
  padding:8px 12px;
  border-radius:8px;
  font-weight:600;
  border:none;
  cursor:pointer;
}
.edit-btn { background: linear-gradient(135deg,#3b82f6,#6366f1); color:#fff; }
.cancel-btn { background: rgba(255,255,255,0.06); color:var(--text-primary); border:1px solid var(--border-glass); }
.save-btn { background: linear-gradient(135deg,#10b981,#059669); color:#fff; }

/* Form */
.profile-form {
  display:grid;
  grid-template-columns: 1fr 1fr;
  gap:12px 16px;
  margin-top:8px;
}
.form-row { display:flex; flex-direction:column; }
.form-row label {
  font-size:0.9rem;
  color:var(--text-secondary);
  margin-bottom:6px;
  font-weight:600;
}
.form-row input[type="text"],
.form-row input[type="email"],
.form-row select,
.form-row textarea {
  padding:10px 12px;
  border-radius:8px;
  border:1px solid var(--border-glass);
  background: rgba(255,255,255,0.03);
  color:var(--text-primary);
  font-size:0.95rem;
  outline:none;
  transition: all 0.3s ease;
}
.form-row input:focus,
.form-row select:focus,
.form-row textarea:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
}
.form-row textarea { min-height:120px; resize:vertical; grid-column: span 2; }

/* Make labels left-aligned and fields full width */
.form-row input:disabled,
.form-row select:disabled,
.form-row textarea:disabled { opacity:0.9; }

/* single-column full width elements */
.full-row { grid-column: 1 / -1; }

/* Error styling */
.error-box {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.4);
  border-radius: 8px;
  padding: 1rem;
  margin-bottom: 1rem;
  color: #fca5a5;
  font-size: 0.9rem;
  line-height: 1.6;
}
.input-error {
  border-color: rgba(239, 68, 68, 0.6) !important;
  background: rgba(239, 68, 68, 0.05) !important;
}

/* Responsive */
@media(max-width:880px){
  .profile-container { grid-template-columns: 1fr; }
  .profile-image { order: -1; }
  .form-row textarea { grid-column: auto; }
}
/* FORCE DARK DROPDOWN for HOD Profile */
.profile-form select,
.profile-form select:disabled,
#teacher_gender {
    background: rgba(255,255,255,0.08) !important;
    color: var(--text-primary) !important;
    border: 1px solid var(--border-glass) !important;
    border-radius: 10px !important;
    padding: 10px 12px !important;
    font-weight: 600 !important;
    backdrop-filter: blur(6px) !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
}

/* dropdown items */
.profile-form select option {
    background: rgba(10,14,39,0.95) !important;
    color: #ffffff !important;
    padding: 8px 10px !important;
}

/* focus effect */
.profile-form select:focus {
    border-color: var(--gradient-1) !important;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.35) !important;
}

</style>
</head>
<body>
<main class="main-content">
  <div class="profile-container">

    <!-- photo column -->
    <div class="profile-image"
         style="
            width:100%;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:flex-start;
            text-align:center;
         ">

      <!-- Circular photo wrapper (centered) -->
      <div class="profile-photo-wrap" 
           style="
              width:150px;
              height:150px;
              border-radius:50%;
              overflow:hidden;
              border:4px solid var(--gradient-1);
              box-shadow:0 0 22px rgba(102,126,234,0.45);
              margin-bottom:12px;
           ">
        <img id="photoPreview" 
             src="<?= htmlspecialchars($photo_path) ?>?v=<?= time() ?>" 
             alt="Profile Photo"
             style="width:100%;height:100%;object-fit:cover;">
      </div>

      <div class="photo-note">Profile photo</div>

      <div class="change-photo" id="changePhotoWrap">
        <input type="file" name="teacher_photo" id="teacher_photo" accept="image/jpeg,image/jpg,image/png,image/gif">
        <span class="file-hint">Max 2MB (JPG, PNG, GIF)</span>
      </div>

      <?php if($err): ?>
        <div class="error-box"><?= $err ?></div>
      <?php endif; ?>
      <?php if($success): ?>
        <div style="color:#b7ffcc;font-weight:600;text-align:center;"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

    </div>

    <!-- info column -->
    <div class="profile-info">
      <div class="profile-header">
        <h1><i class="fas fa-user-circle"></i> My Profile</h1>
        <div class="action-buttons">
          <button type="button" id="editBtn" class="btn edit-btn"><i class="fas fa-edit"></i> Edit</button>
          <button type="button" id="cancelBtn" class="btn cancel-btn" style="display:none;"><i class="fas fa-times"></i> Cancel</button>
          <button type="button" id="saveBtn" class="btn save-btn" style="display:none;"><i class="fas fa-save"></i> Save</button>
          <a href="HODChangePassword.php" class="btn" style="background:transparent;border:1px solid var(--border-glass);color:var(--text-primary);">Change Password</a>
        </div>
      </div>

      <form id="profileForm" method="post" enctype="multipart/form-data" class="profile-form">
        <div class="form-row">
          <label for="teacher_name">Name <span style="color:#f87171;">*</span></label>
          <input type="text" name="teacher_name" id="teacher_name" value="<?= htmlspecialchars($teacher['teacher_name']) ?>" disabled required>
        </div>

        <div class="form-row">
          <label for="teacher_email">Email</label>
          <input type="email" name="teacher_email" id="teacher_email" value="<?= htmlspecialchars($teacher['teacher_email']) ?>" disabled readonly>
        </div>

        <div class="form-row">
          <label for="teacher_gender">Gender <span style="color:#f87171;">*</span></label>
          <select name="teacher_gender" id="teacher_gender" disabled required>
            <option value="">-- Select Gender --</option>
            <option value="Male" <?= ($teacher['teacher_gender']=='Male') ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= ($teacher['teacher_gender']=='Female') ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= ($teacher['teacher_gender']=='Other') ? 'selected' : '' ?>>Other</option>
          </select>
        </div>

        <div class="form-row">
          <label for="teacher_contact">Contact <span style="color:#f87171;">*</span></label>
          <input type="text" name="teacher_contact" id="teacher_contact" value="<?= htmlspecialchars($teacher['teacher_contact']) ?>" pattern="[0-9]{10}" maxlength="10" disabled required>
        </div>

        <div class="form-row full-row">
          <label for="teacher_address">Address <span style="color:#f87171;">*</span></label>
          <textarea name="teacher_address" id="teacher_address" disabled required><?= htmlspecialchars($teacher['teacher_address']) ?></textarea>
        </div>
      </form>
    </div>

  </div>
</main>

<script>
/* Behaviour:
 - Edit: enable fields, show change photo input and save/cancel
 - Cancel: restore original values and hide photo input
 - Save: validate then submit
*/

// store original values
const originals = {
  name: document.getElementById('teacher_name').value,
  email: document.getElementById('teacher_email').value,
  gender: document.getElementById('teacher_gender').value,
  contact: document.getElementById('teacher_contact').value,
  address: document.getElementById('teacher_address').value,
  photoSrc: document.getElementById('photoPreview').src
};

const editBtn = document.getElementById('editBtn');
const cancelBtn = document.getElementById('cancelBtn');
const saveBtn = document.getElementById('saveBtn');
const changePhotoWrap = document.getElementById('changePhotoWrap');
const fileInput = document.getElementById('teacher_photo');
const photoPreview = document.getElementById('photoPreview');
const form = document.getElementById('profileForm');

// ► ENTER EDIT MODE
function enterEditMode(){
  document.getElementById('teacher_name').disabled = false;
  document.getElementById('teacher_gender').disabled = false;
  document.getElementById('teacher_contact').disabled = false;
  document.getElementById('teacher_address').disabled = false;

  changePhotoWrap.style.display = 'block';

  editBtn.style.display    = 'none';
  cancelBtn.style.display  = 'inline-block';
  saveBtn.style.display    = 'inline-block';
}

// ► EXIT EDIT MODE
function exitEditMode(restore = false){
  if(restore){
    document.getElementById('teacher_name').value = originals.name;
    document.getElementById('teacher_email').value = originals.email;
    document.getElementById('teacher_gender').value = originals.gender;
    document.getElementById('teacher_contact').value = originals.contact;
    document.getElementById('teacher_address').value = originals.address;

    // revert photo
    photoPreview.src = originals.photoSrc;

    // reset file input
    fileInput.value = "";
    
    // Clear error styling
    document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
  }

  document.getElementById('teacher_name').disabled = true;
  document.getElementById('teacher_gender').disabled = true;
  document.getElementById('teacher_contact').disabled = true;
  document.getElementById('teacher_address').disabled = true;

  changePhotoWrap.style.display = 'none';

  editBtn.style.display    = 'inline-block';
  cancelBtn.style.display  = 'none';
  saveBtn.style.display    = 'none';
}

editBtn.addEventListener('click', enterEditMode);
cancelBtn.addEventListener('click', function(){ exitEditMode(true); });

// ► PREVIEW NEW IMAGE
fileInput.addEventListener('change', function(){
  const file = this.files[0];
  if(!file) return;

  // Validate file type
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
  if(!allowedTypes.includes(file.type)){
    alert('Please select a valid image (JPG, PNG, or GIF)');
    this.value = '';
    return;
  }

  // Validate file size (2MB)
  if(file.size > 2 * 1024 * 1024){
    alert('Photo size must be less than 2MB');
    this.value = '';
    return;
  }

  const url = URL.createObjectURL(file);
  photoPreview.src = url;
});

// ► VALIDATION AND SAVE
saveBtn.addEventListener('click', function(e){
  let isValid = true;
  const errors = [];

  // Clear previous errors
  document.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));

  // Name validation
  const name = document.getElementById('teacher_name').value.trim();
  if(name.length < 3){
    errors.push('Name must be at least 3 characters');
    document.getElementById('teacher_name').classList.add('input-error');
    isValid = false;
  }

  // Gender validation
  const gender = document.getElementById('teacher_gender').value;
  if(!gender){
    errors.push('Please select gender');
    document.getElementById('teacher_gender').classList.add('input-error');
    isValid = false;
  }

  // Contact validation
  const contact = document.getElementById('teacher_contact').value.trim();
  const contactRegex = /^[0-9]{10}$/;
  if(!contactRegex.test(contact)){
    errors.push('Contact must be exactly 10 digits');
    document.getElementById('teacher_contact').classList.add('input-error');
    isValid = false;
  }

  // Address validation
  const address = document.getElementById('teacher_address').value.trim();
  if(address.length < 10){
    errors.push('Address must be at least 10 characters');
    document.getElementById('teacher_address').classList.add('input-error');
    isValid = false;
  }

  if(!isValid){
    alert('Please correct the following errors:\n\n' + errors.join('\n'));
    return;
  }

  // Submit form
  form.submit();
});

// Remove error styling on input
document.querySelectorAll('input, select, textarea').forEach(element => {
  element.addEventListener('input', function() {
    this.classList.remove('input-error');
  });
});
</script>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>