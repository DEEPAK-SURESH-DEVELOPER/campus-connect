<?php 
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");

if(isset($_POST["btn_save"])) {
    $dept     = $_POST["sel_department"];
    $desig    = $_POST["sel_designation"];
    $name     = $_POST["txt_name"];
    $gender   = $_POST["rdo_gender"];
    $address  = $_POST["txt_address"];
    $contact  = $_POST["txt_contact"];
    $email    = $_POST["txt_email"];
    $password = $_POST["txt_password"];

    $photo = $_FILES['file_photo']['name'];
    $temp  = $_FILES['file_photo']['tmp_name'];
    move_uploaded_file($temp,'../Assets/Files/Teacher/'.$photo);

    $insQry = "INSERT INTO tbl_teacher
        (department_id, designation_id, teacher_name, teacher_gender, teacher_address, teacher_contact, teacher_email, teacher_password, teacher_photo)
        VALUES ('$dept','$desig','$name','$gender','$address','$contact','$email','$password','$photo')";

    if($con->query($insQry)) {
        echo "<script>alert('Teacher Registered Successfully'); window.location='TeacherRegistration.php';</script>";
    } else {
        echo "<script>alert('Error: ".$con->error."');</script>";
    }
}

$page_title = "Teacher Registration";
$breadcrumb = '<span>Faculty</span> <i class="fas fa-chevron-right"></i> <span>Teacher Registration</span>';
?>

<div class="page-wrapper">
  <div class="main-content">
    <div class="form-card">
      <h1 class="page-title"><i class="fas fa-user-tie"></i> Teacher Registration</h1>

      <form method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label>Department</label>
          <select name="sel_department" required>
            <option value="">-- Select Department --</option>
            <?php
            $selDept="SELECT * FROM tbl_department";
            $resDept=$con->query($selDept);
            while($row=$resDept->fetch_assoc()){
                echo "<option value='".$row['department_id']."'>".$row['department_name']."</option>";
            }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>Designation</label>
          <select name="sel_designation" required>
            <option value="">-- Select Designation --</option>
            <?php
            $selDesig="SELECT * FROM tbl_designation";
            $resDesig=$con->query($selDesig);
            while($row=$resDesig->fetch_assoc()){
                echo "<option value='".$row['designation_id']."'>".$row['designation_name']."</option>";
            }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>Name</label>
          <input type="text" name="txt_name" placeholder="Enter teacher name" required />
        </div>

        <div class="form-group">
          <label>Gender</label>
          <div class="radio-group">
            <label><input type="radio" name="rdo_gender" value="Male" required> Male</label>
            <label><input type="radio" name="rdo_gender" value="Female"> Female</label>
            <label><input type="radio" name="rdo_gender" value="Other"> Other</label>
          </div>
        </div>

        <div class="form-group">
          <label>Address</label>
          <textarea name="txt_address" placeholder="Enter address" required></textarea>
        </div>

        <div class="form-group">
          <label>Contact</label>
          <input type="text" name="txt_contact" placeholder="Enter contact number" required />
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="txt_email" placeholder="Enter email address" required />
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="txt_password" placeholder="Create password" required />
        </div>

        <div class="form-group">
          <label>Photo</label>
          <input type="file" name="file_photo" accept="image/*" />
        </div>

        <div class="form-actions">
          <input type="submit" name="btn_save" value="Register" class="btn btn-primary">
          <input type="reset" value="Cancel" class="btn btn-cancel">
        </div>
      </form>
    </div>
  </div>
</div>

<!-- INTERNAL CSS -->
<style>
.page-wrapper {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  width: 100%;
}
.main-content {
  width: 100%;
  display: flex;
  justify-content: center;
  padding: 2rem;
}

/* Glass Card */
.form-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 2rem 2.5rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.3);
  color: var(--text-primary);
  width: 100%;
  max-width: 700px;
  animation: fadeIn 0.4s ease;
}

/* Title */
.page-title {
  text-align: center;
  color: var(--gradient-1);
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 2rem;
}

/* Inputs */
.form-group {
  margin-bottom: 1rem;
}
.form-group label {
  display: block;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 0.4rem;
}
.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.8rem 1rem;
  border-radius: 8px;
  border: 1px solid var(--border-glass);
  background: rgba(255,255,255,0.08);
  color: var(--text-primary);
  font-size: 0.95rem;
  outline: none;
  transition: all 0.3s ease;
  backdrop-filter: blur(5px);
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 6px rgba(99,102,241,0.4);
}
textarea {
  resize: none;
  min-height: 70px;
}

/* Radio Buttons */
.radio-group {
  display: flex;
  gap: 1.5rem;
  margin-top: 0.3rem;
}
.radio-group label {
  font-weight: 500;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

/* File Input */
input[type="file"] {
  border: 1px solid var(--border-glass);
  background: rgba(255,255,255,0.08);
  padding: 0.5rem;
  border-radius: 8px;
  color: var(--text-secondary);
  cursor: pointer;
  width: 100%;
}

/* Buttons */
.form-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 1.5rem;
}
.btn {
  padding: 0.8rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: 0.3s ease;
}
.btn-primary {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
}
.btn-primary:hover {
  box-shadow: 0 0 10px rgba(99,102,241,0.5);
  transform: scale(1.05);
}
.btn-cancel {
  background: rgba(255,255,255,0.08);
  color: var(--text-secondary);
  border: 1px solid var(--border-glass);
}
.btn-cancel:hover {
  background: rgba(255,255,255,0.15);
}

/* Animation */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Responsive */
@media(max-width:768px){
  .form-card {
    padding: 1.5rem;
  }
  .page-title {
    font-size: 1.5rem;
  }
  .radio-group {
    flex-direction: column;
    gap: 0.5rem;
  }
}
/* ------------------------------------------
   DARK THEMED DROPDOWN (Universal Style)
   For Department & Designation dropdowns
------------------------------------------- */
.form-group select,
.form-group select option {
    background-color: rgba(15, 23, 42, 0.95) !important; /* deep dark navy */
    color: #ffffff !important;                           /* readable text */
}

/* Closed dropdown look */
.form-group select {
    background: rgba(255,255,255,0.08) !important;
    border: 1px solid var(--border-glass) !important;
    border-radius: 8px;
    padding: 0.8rem 1rem;
    font-size: 0.95rem;
    cursor: pointer;
    backdrop-filter: blur(8px);
}

/* The actual dropdown list items */
.form-group select option {
    padding: 10px 12px !important;
    background: rgba(10, 14, 39, 0.95) !important;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    color: #fff !important;
}

/* Hover & selected option */
.form-group select option:hover,
.form-group select option:checked {
    background: rgba(59,130,246,0.4) !important; /* neon blue glow */
    color: #fff !important;
}

/* Focus ring */
.form-group select:focus {
    border-color: var(--gradient-1);
    box-shadow: 0 0 0 3px rgba(102,126,234,0.35) !important;
}

</style>
<script>
// ==========================
// REAL-TIME RESTRICTIONS
// ==========================

// Name – letters & spaces only
const nameEl = document.querySelector("input[name='txt_name']");
nameEl.addEventListener("input", function () {
    this.value = this.value.replace(/[^A-Za-z ]/g, "");
});

// Contact – digits only (max 10)
const contactEl = document.querySelector("input[name='txt_contact']");
contactEl.addEventListener("input", function () {
    this.value = this.value.replace(/[^0-9]/g, "").substring(0, 10);
});

// Email – no spaces
const emailEl = document.querySelector("input[name='txt_email']");
emailEl.addEventListener("input", function () {
    this.value = this.value.replace(/\s/g, "");
});

// Password – no spaces
const passEl = document.querySelector("input[name='txt_password']");
passEl.addEventListener("input", function () {
    this.value = this.value.replace(/\s/g, "");
});

// ==========================
// FORM VALIDATION
// ==========================
document.querySelector("form").addEventListener("submit", function (e) {

    const department = document.querySelector("select[name='sel_department']").value;
    const designation = document.querySelector("select[name='sel_designation']").value;
    const name = nameEl.value.trim();
    const gender = document.querySelector("input[name='rdo_gender']:checked");
    const address = document.querySelector("textarea[name='txt_address']").value.trim();
    const contact = contactEl.value.trim();
    const email = emailEl.value.trim();
    const password = passEl.value.trim();
    const photoInput = document.querySelector("input[name='file_photo']");

    // Department
    if (department === "") {
        alert("❌ Please select a Department");
        e.preventDefault();
        return false;
    }

    // Designation
    if (designation === "") {
        alert("❌ Please select a Designation");
        e.preventDefault();
        return false;
    }

    // Name
    if (name === "") {
        alert("❌ Name is required");
        e.preventDefault();
        return false;
    }
    if (!/^[A-Za-z ]+$/.test(name)) {
        alert("❌ Name must contain only letters and spaces");
        e.preventDefault();
        return false;
    }

    // Gender
    if (!gender) {
        alert("❌ Please select Gender");
        e.preventDefault();
        return false;
    }

    // Address
    if (address === "") {
        alert("❌ Address cannot be empty");
        e.preventDefault();
        return false;
    }
    if (address.length < 5) {
        alert("❌ Address must be minimum 5 characters");
        e.preventDefault();
        return false;
    }

    // Contact
    if (contact === "") {
        alert("❌ Contact is required");
        e.preventDefault();
        return false;
    }
    if (!/^[0-9]{10}$/.test(contact)) {
        alert("❌ Contact must be exactly 10 digits");
        e.preventDefault();
        return false;
    }

    // Email
    if (email === "") {
        alert("❌ Email is required");
        e.preventDefault();
        return false;
    }
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert("❌ Invalid Email Format");
        e.preventDefault();
        return false;
    }

    // Password
    if (password === "") {
        alert("❌ Password is required");
        e.preventDefault();
        return false;
    }
    if (password.length < 6) {
        alert("❌ Password must be at least 6 characters long");
        e.preventDefault();
        return false;
    }

    // Photo (optional)
    if (photoInput.files.length > 0) {
        const f = photoInput.files[0];
        const allowed = ["jpg", "jpeg", "png"];
        const ext = f.name.split(".").pop().toLowerCase();

        if (!allowed.includes(ext)) {
            alert("❌ Only JPG, JPEG, PNG allowed");
            e.preventDefault();
            return false;
        }

        const maxSize = 5 * 1024 * 1024; // 5MB
        if (f.size > maxSize) {
            alert("❌ Photo must be less than 5MB");
            e.preventDefault();
            return false;
        }
    }

    // All validations passed
    return true;
});
</script>


<script src="../Assets/JS/universal.js"></script>
</body>
</html>
