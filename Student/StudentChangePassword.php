<?php 
include("../Assets/Connection/Connection.php");
session_start();

// Check if student is logged in
if(!isset($_SESSION['student_id'])){
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}

$student_id = (int)$_SESSION['student_id'];
$err = $success = "";
$step = 1; // Step 1: verify current password

// ====================== BACKEND VALIDATION STEP 1 ======================
if(isset($_POST['btn_check_current'])){
    $current = trim($_POST['current_pass']);

    if(strlen($current) < 1){
        $err = "Please enter your current password!";
    } else {
        $res = mysqli_query($con,"SELECT student_password FROM tbl_student WHERE student_id='$student_id'");
        $student = mysqli_fetch_assoc($res);

        if($current !== $student['student_password']){
            $err = "Current password is incorrect!";
        } else {
            $step = 2;
        }
    }
}

// ====================== BACKEND VALIDATION STEP 2 ======================
if(isset($_POST['btn_change'])){
    $new = trim($_POST['new_pass']);
    $confirm = trim($_POST['confirm_pass']);

    if(strlen($new) < 6){
        $err = "Password must be at least 6 characters!";
        $step = 2;
    }
    elseif($new !== $confirm){
        $err = "New password and confirm password do not match!";
        $step = 2;
    }
    else {
        mysqli_query($con,"UPDATE tbl_student SET student_password='$new' WHERE student_id='$student_id'");
        $success = "Password changed successfully!";
        $step = 1;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password - Student</title>

<!-- UNIVERSAL THEME CSS -->
<link rel="stylesheet" href="../Assets/CSS/universal.css">

<style>
/* --- PAGE LAYOUT FIX --- */
.student-main {
    margin-left: 280px;
    margin-top: 90px;
    padding: 2rem;
}

/* --- CARD --- */
.cc-card {
    max-width: 550px;
    margin: auto;
    background: var(--card-bg);
    border: 1px solid var(--border-glass);
    border-radius: 20px;
    padding: 2.5rem;
    backdrop-filter: blur(18px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.35);
    animation: fadeInUp 0.6s ease;
}

/* Title */
.cc-title {
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 1.6rem;
}

/* Alerts */
.cc-alert {
    padding: 1rem 1.3rem;
    border-radius: 14px;
    margin-bottom: 1.2rem;
    display: flex;
    gap: 0.7rem;
    align-items: center;
    font-weight: 600;
}
.cc-error {
    background: rgba(239, 68, 68, 0.15);
    color: var(--error);
    border-left: 4px solid var(--error);
}
.cc-success {
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
    border-left: 4px solid var(--success);
}

/* Input Fields */
.cc-input-group {
    margin-bottom: 1.3rem;
}
.cc-label {
    font-size: 0.95rem;
    color: var(--text-secondary);
    margin-bottom: 0.4rem;
    display: block;
}
.cc-input {
    width: 100%;
    padding: 1rem;
    border-radius: 14px;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border-glass);
    color: var(--text-primary);
    transition: 0.25s;
}
.cc-input:focus {
    border-color: var(--gradient-1);
    box-shadow: 0 0 0 3px rgba(102,126,234,0.25);
}

/* Buttons (B1 theme) */
.cc-btn {
    padding: 0.85rem 2rem;
    border-radius: 50px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: 0.25s;
    margin-top: 0.5rem;
}
.cc-btn-primary {
    background: linear-gradient(135deg, var(--gradient-1), var(--gradient-2));
    color: white;
    box-shadow: 0 6px 20px rgba(102,126,234,0.45);
}
.cc-btn-primary:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 12px 35px rgba(102,126,234,0.75);
}

.cc-btn-center {
    text-align: center;
}

.cc-back {
    text-align: center;
    margin-top: 1.2rem;
}
.cc-back a {
    color: var(--text-secondary);
    transition: 0.25s;
}
.cc-back a:hover {
    color: var(--gradient-1);
}

</style>

</head>

<body>

<?php include("../Includes/StudentSidebar.php"); ?>

<div class="student-main">

    <div class="cc-card">

        <h2 class="cc-title"><i class="fas fa-key"></i> Change Password</h2>

        <?php if($err): ?>
            <div class="cc-alert cc-error"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="cc-alert cc-success"><i class="fas fa-check-circle"></i><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- STEP 1: VERIFY CURRENT PASSWORD -->
        <?php if($step == 1): ?>
        <form method="post" id="step1Form">
            <div class="cc-input-group">
                <label class="cc-label">Current Password</label>
                <input type="password" name="current_pass" class="cc-input" id="current_pass" required>
            </div>

            <div class="cc-btn-center">
                <button type="submit" name="btn_check_current" class="cc-btn cc-btn-primary">
                    Verify Password
                </button>
            </div>
        </form>

        <!-- STEP 2: ENTER NEW PASSWORD -->
        <?php else: ?>
        <form method="post" id="step2Form">
            <div class="cc-input-group">
                <label class="cc-label">New Password</label>
                <input type="password" name="new_pass" class="cc-input" id="new_pass" required>
            </div>

            <div class="cc-input-group">
                <label class="cc-label">Confirm Password</label>
                <input type="password" name="confirm_pass" class="cc-input" id="confirm_pass" required>
            </div>

            <div class="cc-btn-center">
                <button type="submit" name="btn_change" class="cc-btn cc-btn-primary">
                    Change Password
                </button>
            </div>
        </form>
        <?php endif; ?>

        <div class="cc-back">
            <a href="StudentProfile.php">← Back to Profile</a>
        </div>

    </div>
</div>

<script src="../Assets/JS/universal.js"></script>

<script>
// =======================================================
// FRONT-END VALIDATION
// =======================================================

// STEP 1 – validate current password
document.getElementById("step1Form")?.addEventListener("submit", function(e){
    let current = document.getElementById("current_pass").value.trim();

    if(current.length < 1){
        e.preventDefault();
        CampusConnect.showToast("Please enter your current password!", "error");
    }
});

// STEP 2 – validate new and confirm password
document.getElementById("step2Form")?.addEventListener("submit", function(e){
    let newP = document.getElementById("new_pass").value.trim();
    let cpass = document.getElementById("confirm_pass").value.trim();

    if(newP.length < 6){
        e.preventDefault();
        CampusConnect.showToast("Password must be at least 6 characters!", "error");
        return;
    }

    if(newP !== cpass){
        e.preventDefault();
        CampusConnect.showToast("Passwords do not match!", "error");
        return;
    }
});
</script>

</body>
</html>
