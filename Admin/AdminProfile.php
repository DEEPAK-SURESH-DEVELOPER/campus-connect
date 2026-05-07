<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");
$admin_id = $_SESSION['admin_id'];

// Fetch admin info
$qry = "SELECT * FROM tbl_admin WHERE admin_id='$admin_id'";
$res = mysqli_query($con, $qry);
$data = mysqli_fetch_assoc($res);

$admin_name = $data['admin_name'];
$admin_email = $data['admin_email'];
$admin_photo = $data['admin_photo'] ?? '';
$photo_path = (!empty($admin_photo) && file_exists("../Assets/Files/Admin/".$admin_photo))
  ? "../Assets/Files/Admin/".$admin_photo
  : "../Assets/Files/Admin/default.png";

// Update logic
if(isset($_POST['btn_update'])){
    $name = $_POST['txt_name'];
    $email = $_POST['txt_email'];

    $filename = $admin_photo;
    if(isset($_FILES['file_photo']['name']) && $_FILES['file_photo']['name'] != ""){
        $filename = time()."_".$_FILES['file_photo']['name'];
        move_uploaded_file($_FILES['file_photo']['tmp_name'], "../Assets/Files/Admin/".$filename);
    }

    $update = "UPDATE tbl_admin SET admin_name='$name', admin_email='$email', admin_photo='$filename' WHERE admin_id='$admin_id'";
    if(mysqli_query($con, $update)){
        $_SESSION['admin_name'] = $name;
        echo "<script>alert('Profile updated successfully'); window.location='AdminProfile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile');</script>";
    }
}

$page_title = "Admin Profile";
$breadcrumb = '<span>Account</span> <i class="fas fa-chevron-right"></i> <span>Profile</span>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Profile</title>

<!-- INTERNAL CSS -->
<style>
.main-content {
  padding: 2rem;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.profile-container {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 2rem 2.5rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.3);
  color: var(--text-primary);
  max-width: 700px;
  width: 100%;
}

.profile-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}
.profile-header h1 {
  color: var(--gradient-1);
  font-size: 1.6rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.profile-actions {
  display: flex;
  gap: 0.6rem;
  flex-wrap: wrap;
}
.profile-actions .edit-btn,
.profile-actions .change-pass-link {
  border: none;
  padding: 0.6rem 1rem;
  border-radius: 8px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: 0.3s ease;
}
.edit-btn {
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  color: #fff;
}
.edit-btn:hover {
  box-shadow: 0 0 10px rgba(99,102,241,0.4);
}
.change-pass-link {
  background: rgba(255,255,255,0.08);
  border: 1px solid var(--border-glass);
  color: var(--gradient-1);
}
.change-pass-link:hover {
  background: rgba(255,255,255,0.15);
}

/* Profile Form */
.profile-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  text-align: center;
}
.profile-photo {
  position: relative;
  margin-bottom: 1rem;
}
.profile-photo img {
  width: 160px;
  height: 160px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--gradient-1);
  box-shadow: 0 0 25px rgba(99,102,241,0.3);
  transition: transform 0.3s ease;
}
.profile-photo img:hover {
  transform: scale(1.05);
}
.profile-photo input[type="file"] {
  display: block;
  margin: 0.5rem auto;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

/* Form Fields */
.form-group {
  text-align: left;
}
.form-group label {
  display: block;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 0.3rem;
}
.form-group input {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  border: 1px solid var(--border-glass);
  background: rgba(255,255,255,0.08);
  color: var(--text-primary);
  font-size: 0.95rem;
  outline: none;
  transition: 0.3s ease;
}
.form-group input:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 6px rgba(99,102,241,0.4);
}

/* Save Button */
.save-btn {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 0.8rem 1.4rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
  margin-top: 1rem;
}
.save-btn:hover {
  box-shadow: 0 0 10px rgba(99,102,241,0.5);
  transform: scale(1.02);
}

/* Responsive */
@media(max-width:768px){
  .profile-container {
    padding: 1.5rem;
  }
  .profile-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.8rem;
  }
  .profile-photo img {
    width: 130px;
    height: 130px;
  }
}
</style>
</head>

<body>
<div class="main-content">
  <div class="profile-container">
    <div class="profile-header">
      <h1><i class="fas fa-user-circle"></i> Admin Profile</h1>
      <div class="profile-actions">
        <button type="button" id="editBtn" class="edit-btn"><i class="fas fa-edit"></i> Edit</button>
        <a href="AdminChangePassword.php" class="change-pass-link"><i class="fas fa-lock"></i> Change Password</a>
        <a href="AdminHome.php" class="change-pass-link"><i class="fas fa-home"></i> Back</a>
      </div>
    </div>

    <form method="post" enctype="multipart/form-data" class="profile-form" id="profileForm">
      <div class="profile-photo">
        <img src="<?= htmlspecialchars($photo_path); ?>" alt="Admin Photo" id="photoPreview">
        <input type="file" name="file_photo" accept="image/*" disabled>
      </div>

      <div class="form-group">
        <label>Name</label>
        <input type="text" name="txt_name" value="<?= htmlspecialchars($admin_name); ?>" disabled required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" name="txt_email" value="<?= htmlspecialchars($admin_email); ?>" disabled required>
      </div>

      <button type="submit" name="btn_update" class="save-btn" id="saveBtn" style="display:none;">
        <i class="fas fa-save"></i> Save Changes
      </button>
    </form>
  </div>
</div>

<script>
// ==========================
// ENABLE EDIT MODE
// ==========================
const editBtn = document.getElementById("editBtn");
const saveBtn = document.getElementById("saveBtn");
const formInputs = document.querySelectorAll("#profileForm input");

editBtn.addEventListener("click", () => {
  formInputs.forEach(i => i.disabled = false); // enable all inputs
  editBtn.style.display = "none";
  saveBtn.style.display = "inline-block";
});

// ==========================
// PHOTO PREVIEW (Optional)
// ==========================
document.querySelector("input[name='file_photo']").addEventListener("change", function(e) {
  const file = this.files[0];
  if (file) {
    document.getElementById("photoPreview").src = URL.createObjectURL(file);
  }
});

// ==========================
// REAL-TIME RESTRICTIONS
// ==========================

// Name — only letters and spaces
const nameInput = document.querySelector("input[name='txt_name']");
nameInput.addEventListener("input", function() {
  this.value = this.value.replace(/[^A-Za-z ]/g, '');
});

// Email — cannot contain spaces
const emailInput = document.querySelector("input[name='txt_email']");
emailInput.addEventListener("input", function() {
  this.value = this.value.replace(/\s/g, '');
});

// ==========================
// FORM VALIDATION
// ==========================
document.getElementById("profileForm").addEventListener("submit", function(e) {

  let name = nameInput.value.trim();
  let email = emailInput.value.trim();
  let fileInput = document.querySelector("input[name='file_photo']");

  // --- NAME VALIDATION ---
  if (name === "") {
    alert("❌ Name cannot be empty!");
    e.preventDefault();
    return false;
  }
  if (!/^[A-Za-z ]+$/.test(name)) {
    alert("❌ Name must contain only letters and spaces!");
    e.preventDefault();
    return false;
  }

  // --- EMAIL VALIDATION ---
  if (email === "") {
    alert("❌ Email cannot be empty!");
    e.preventDefault();
    return false;
  }
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    alert("❌ Please enter a valid email address!");
    e.preventDefault();
    return false;
  }

  // --- PHOTO VALIDATION (if uploaded) ---
  if (fileInput.files.length > 0) {
    const f = fileInput.files[0];
    const allowed = ["jpg","jpeg","png"];
    const ext = f.name.split(".").pop().toLowerCase();

    if (!allowed.includes(ext)) {
      alert("❌ Only JPG, JPEG, PNG files are allowed!");
      e.preventDefault();
      return false;
    }

    if (f.size > 5 * 1024 * 1024) { // 5MB
      alert("❌ Photo size must be less than 5MB!");
      e.preventDefault();
      return false;
    }
  }

  // ✔ ALL OK — allow form submit
  return true;
});
</script>


<script src="../Assets/JS/universal.js"></script>
</body>
</html>
