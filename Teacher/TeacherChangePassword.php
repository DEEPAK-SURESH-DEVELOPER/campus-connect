<?php 
include("../Assets/Connection/Connection.php");
session_start();

if(!isset($_SESSION['teacher_id'])){
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}

$teacher_id = (int)$_SESSION['teacher_id'];
$err = $success = "";
$step = 1;

// ------------------- BACKEND VALIDATION --------------------
if(isset($_POST['btn_check_current'])){
    $current = trim($_POST['current_pass']);

    if(strlen($current) < 1){
        $err = "Please enter your current password!";
    } else {
        $res = mysqli_query($con,"SELECT teacher_password FROM tbl_teacher WHERE teacher_id='$teacher_id'");
        $teacher = mysqli_fetch_assoc($res);

        if($current !== $teacher['teacher_password']){
            $err = "Current password is incorrect!";
        } else {
            $step = 2;
        }
    }
}

if(isset($_POST['btn_change'])){
    $new = trim($_POST['new_pass']);
    $confirm = trim($_POST['confirm_pass']);

    if(strlen($new) < 6){
        $err = "Password must be at least 6 characters long!";
        $step = 2;
    } elseif($new !== $confirm){
        $err = "New password and confirm password do not match!";
        $step = 2;
    } else {
        mysqli_query($con,"UPDATE tbl_teacher SET teacher_password='$new' WHERE teacher_id='$teacher_id'");
        $success = "Password changed successfully!";
        $step = 1;
    }
}

// Page info for universal header
$page_title = "Change Password";
$breadcrumb = '<span>Teacher</span> <i class="fas fa-chevron-right"></i> <span>Change Password</span>';

include("../Includes/Header.php");
include("../Includes/Sidebar.php");
?>

<div class="main-content">

    <div class="glass-card" style="max-width:520px; margin:auto; animation:fadeInUp 0.5s;">
        <h2 class="card-title" style="text-align:center;">
            <i class="fas fa-key"></i> Change Password
        </h2>

        <?php if($err): ?>
            <div class="toast-notification error" style="position:relative; margin-bottom:1rem;">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($err) ?></span>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="toast-notification success" style="position:relative; margin-bottom:1rem;">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <?php if($step == 1): ?>
            <form method="post" id="step1Form">
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_pass" id="current_pass" class="form-control" required>
                </div>

                <button type="submit" name="btn_check_current" class="btn-primary w-full">
                    Verify Password
                </button>
            </form>
        <?php else: ?>
            <form method="post" id="step2Form">
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_pass" id="new_pass" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_pass" id="confirm_pass" class="form-control" required>
                </div>

                <button type="submit" name="btn_change" class="btn-primary w-full">
                    Change Password
                </button>
            </form>
        <?php endif; ?>

        <div style="text-align:center; margin-top:1.5rem;">
            <a href="TeacherProfile.php" class="btn-outline">
                ← Back to Profile
            </a>
        </div>
    </div>

</div>

<script src="../Assets/JS/universal.js"></script>

<script>
// ==========================================================
// FRONT-END VALIDATION
// ==========================================================

// Step 1 validation — Current password
document.getElementById("step1Form")?.addEventListener("submit", function(e){
    let current = document.getElementById("current_pass").value.trim();

    if(current.length < 1){
        e.preventDefault();
        CampusConnect.showToast("Please enter your current password!", "error");
    }
});

// Step 2 validation — New + Confirm password
document.getElementById("step2Form")?.addEventListener("submit", function(e){
    let newPass = document.getElementById("new_pass").value.trim();
    let confPass = document.getElementById("confirm_pass").value.trim();

    if(newPass.length < 6){
        e.preventDefault();
        CampusConnect.showToast("Password must be at least 6 characters!", "error");
        return;
    }

    if(newPass !== confPass){
        e.preventDefault();
        CampusConnect.showToast("Passwords do not match!", "error");
        return;
    }
});

</script>

</body>
</html>
