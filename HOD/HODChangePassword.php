<?php 
include("../Includes/HODHeader.php");
include("../Includes/HODSidebar.php");
include("../Assets/Connection/Connection.php");

if(!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true){
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}

$hod_id = (int)$_SESSION['hod_id'];
$err = $success = "";
$step = 1;

// ================= BACKEND VALIDATION: STEP 1 =================
if(isset($_POST['btn_check_current'])){
    $current = trim($_POST['current_pass']);

    if(strlen($current) < 1){
        $err = "Please enter your current password!";
    } else {
        $res = mysqli_query($con,"SELECT teacher_password FROM tbl_teacher WHERE teacher_id='$hod_id'");
        $hod = mysqli_fetch_assoc($res);

        if($current !== $hod['teacher_password']){
            $err = "Current password is incorrect!";
        } else {
            $step = 2;
        }
    }
}

// ================= BACKEND VALIDATION: STEP 2 =================
if(isset($_POST['btn_change'])){
    $new = trim($_POST['new_pass']);
    $confirm = trim($_POST['confirm_pass']);

    if(strlen($new) < 6){
        $err = "Password must be at least 6 characters long!";
        $step = 2;
    }
    elseif($new !== $confirm){
        $err = "New password and confirm password do not match!";
        $step = 2;
    }
    else {
        mysqli_query($con,"UPDATE tbl_teacher SET teacher_password='$new' WHERE teacher_id='$hod_id'");
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
<title>Change Password - HOD</title>

<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
/* PAGE WRAPPER */
.main-content {
    padding: 2rem;
}

/* CARD CONTAINER */
.password-card {
    max-width: 480px;
    margin: 0 auto;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border-glass);
    border-radius: 15px;
    padding: 2rem;
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    text-align: center;
}

/* TITLE */
.password-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--gradient-1);
    margin-bottom: 1.5rem;
}

/* ALERT MESSAGE */
.password-alert {
    padding: 0.9rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    font-weight: 600;
}
.password-alert.error {
    background: rgba(255, 80, 80, 0.15);
    border-left: 4px solid #ff4b5c;
    color: #ff707d;
}
.password-alert.success {
    background: rgba(16, 185, 129, 0.15);
    border-left: 4px solid #10b981;
    color: #6ee7b7;
}

/* FORM ELEMENTS */
.password-form {
    text-align: left;
    margin-top: 1rem;
}

.password-group {
    margin-bottom: 1rem;
}

.password-group label {
    font-weight: 600;
    color: var(--text-secondary);
    display: block;
    margin-bottom: 6px;
}

.password-group input {
    width: 100%;
    padding: 0.85rem;
    border-radius: 10px;
    border: 1px solid var(--border-glass);
    background: rgba(255,255,255,0.07);
    color: var(--text-primary);
    font-size: 0.95rem;
    outline: none;
}

.password-group input:focus {
    border-color: var(--gradient-1);
    box-shadow: 0 0 8px rgba(99,102,241,0.3);
}

/* SUBMIT BUTTON */
.password-btn {
    width: 100%;
    padding: 0.85rem;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s ease;
    margin-top: 0.5rem;
}

.password-btn:hover {
    box-shadow: 0 0 12px rgba(102,126,234,0.45);
    transform: translateY(-2px);
}

/* BACK LINK */
.back-link {
    margin-top: 1.3rem;
}
.back-link a {
    color: var(--gradient-1);
    text-decoration: none;
    font-weight: 600;
}
.back-link a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<main class="main-content">

    <div class="password-card">
        
        <h1 class="password-title"><i class="fas fa-lock"></i> Change Password</h1>

        <?php if($err): ?>
            <div class="password-alert error"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="password-alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Step 1: Verify current password -->
        <?php if($step == 1): ?>
        <form method="post" class="password-form" id="step1Form">
            <div class="password-group">
                <label>Current Password:</label>
                <input type="password" name="current_pass" id="current_pass" required>
            </div>
            <button type="submit" name="btn_check_current" class="password-btn">Verify</button>
        </form>

        <!-- Step 2: New password -->
        <?php else: ?>
        <form method="post" class="password-form" id="step2Form">
            <div class="password-group">
                <label>New Password:</label>
                <input type="password" name="new_pass" id="new_pass" required>
            </div>

            <div class="password-group">
                <label>Confirm New Password:</label>
                <input type="password" name="confirm_pass" id="confirm_pass" required>
            </div>

            <button type="submit" name="btn_change" class="password-btn">Change Password</button>
        </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="HODProfile.php">← Back to Profile</a>
        </div>

    </div>

</main>

<script src="../Assets/JS/universal.js"></script>

<script>
// ================= FRONT-END VALIDATION =================

// Step 1 – Validate current password
document.getElementById("step1Form")?.addEventListener("submit", function(e){
    let current = document.getElementById("current_pass").value.trim();

    if(current.length < 1){
        e.preventDefault();
        CampusConnect.showToast("Please enter your current password!", "error");
    }
});

// Step 2 – Validate new + confirm password
document.getElementById("step2Form")?.addEventListener("submit", function(e){

    let newP = document.getElementById("new_pass").value.trim();
    let confP = document.getElementById("confirm_pass").value.trim();

    if(newP.length < 6){
        e.preventDefault();
        CampusConnect.showToast("Password must be at least 6 characters!", "error");
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
