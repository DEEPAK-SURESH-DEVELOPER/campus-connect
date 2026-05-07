<?php
include("../Includes/AdminHeader.php");
if(!isset($_SESSION['admin_id'])){
    echo "<script>alert('Please login first'); window.location='Login.php';</script>";
    exit;
}
include("../Includes/AdminSidebar.php");
$admin_id = $_SESSION['admin_id'];
$err = $success = "";
$step = 1;

// ===================== BACKEND VALIDATION STEP 1 =====================
if(isset($_POST['btn_check_current'])){
    $current = trim($_POST['current_pass']);

    if(strlen($current) < 1){
        $err = "Please enter your current password!";
    } else {
        $res = mysqli_query($con, "SELECT admin_password FROM tbl_admin WHERE admin_id='$admin_id'");
        $admin = mysqli_fetch_assoc($res);

        if($current !== $admin['admin_password']){
            $err = "Current password is incorrect!";
        } else {
            $step = 2;
        }
    }
}

// ===================== BACKEND VALIDATION STEP 2 =====================
if(isset($_POST['btn_change'])){
    $new = trim($_POST['new_pass']);
    $confirm = trim($_POST['confirm_pass']);

    if(strlen($new) < 6){
        $err = "New password must be at least 6 characters!";
        $step = 2;
    }
    elseif($new !== $confirm){
        $err = "New password and confirm password do not match!";
        $step = 2;
    }
    else {
        mysqli_query($con, "UPDATE tbl_admin SET admin_password='$new' WHERE admin_id='$admin_id'");
        $success = "Password changed successfully!";
        $step = 1;
    }
}

$page_title = "Change Password";
$breadcrumb = '<span>Account</span> <i class="fas fa-chevron-right"></i> <span>Change Password</span>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password - Admin</title>

<!-- INTERNAL CSS -->
<style>
.main-content {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem;
  min-height: 100vh;
}

.password-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid var(--border-glass);
  border-radius: 15px;
  padding: 2rem 2.5rem;
  max-width: 480px;
  width: 100%;
  color: var(--text-primary);
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.35);
  text-align: center;
  animation: fadeIn 0.4s ease;
}

.password-title {
  color: var(--gradient-1);
  font-size: 1.6rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.password-alert {
  padding: 0.9rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-weight: 500;
}
.password-alert.error {
  background: rgba(239,68,68,0.1);
  border: 1px solid rgba(239,68,68,0.3);
  color: #f87171;
}
.password-alert.success {
  background: rgba(34,197,94,0.1);
  border: 1px solid rgba(34,197,94,0.3);
  color: #4ade80;
}

.password-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.password-group label {
  display: block;
  color: var(--text-secondary);
  font-weight: 600;
  margin-bottom: 0.4rem;
}

.password-group input {
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
.password-group input:focus {
  border-color: var(--gradient-1);
  box-shadow: 0 0 6px rgba(99,102,241,0.4);
}

.password-btn {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: #fff;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  padding: 0.9rem;
  cursor: pointer;
  transition: 0.3s ease;
  margin-top: 0.5rem;
}
.password-btn:hover {
  opacity: 0.9;
  box-shadow: 0 0 10px rgba(99,102,241,0.5);
}

.back-link {
  margin-top: 1.5rem;
}
.back-link a {
  text-decoration: none;
  color: var(--gradient-1);
  font-weight: 600;
  transition: 0.3s ease;
}
.back-link a:hover {
  color: #8b5cf6;
  text-decoration: underline;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
</head>

<body>
<div class="main-content">
  <div class="password-card">
    <h1 class="password-title"><i class="fas fa-lock"></i> Change Password</h1>

    <?php if($err): ?>
      <div class="password-alert error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <?php if($success): ?>
      <div class="password-alert success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if($step == 1): ?>
      <form method="post" class="password-form" id="step1Form">
        <div class="password-group">
          <label>Current Password:</label>
          <input type="password" id="current_pass" name="current_pass" placeholder="Enter your current password" required>
        </div>
        <button type="submit" name="btn_check_current" class="password-btn">
          <i class="fas fa-check"></i> Verify
        </button>
      </form>

    <?php else: ?>
      <form method="post" class="password-form" id="step2Form">
        <div class="password-group">
          <label>New Password:</label>
          <input type="password" id="new_pass" name="new_pass" placeholder="Enter new password" required>
        </div>
        <div class="password-group">
          <label>Confirm New Password:</label>
          <input type="password" id="confirm_pass" name="confirm_pass" placeholder="Re-enter new password" required>
        </div>
        <button type="submit" name="btn_change" class="password-btn">
          <i class="fas fa-save"></i> Change Password
        </button>
      </form>
    <?php endif; ?>

    <div class="back-link">
      <a href="AdminProfile.php"><i class="fas fa-arrow-left"></i> Back to Profile</a>
    </div>
  </div>
</div>

<script src="../Assets/JS/universal.js"></script>

<!-- ================== FRONT-END VALIDATION ================== -->
<script>
// Step 1 validation
document.getElementById("step1Form")?.addEventListener("submit", function(e){
    let current = document.getElementById("current_pass").value.trim();

    if(current.length < 1){
        e.preventDefault();
        CampusConnect.showToast("Please enter your current password!", "error");
    }
});

// Step 2 validation
document.getElementById("step2Form")?.addEventListener("submit", function(e){
    let newP = document.getElementById("new_pass").value.trim();
    let confP = document.getElementById("confirm_pass").value.trim();

    if(newP.length < 6){
        e.preventDefault();
        CampusConnect.showToast("New password must be at least 6 characters!", "error");
        return;
    }

    if(newP !== confP){
        e.preventDefault();
        CampusConnect.showToast("Passwords do not match!", "error");
        return;
    }
});
</script>

</body>
</html>
