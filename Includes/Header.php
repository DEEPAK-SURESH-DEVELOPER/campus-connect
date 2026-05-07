<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($con)) {
    include("../Assets/Connection/Connection.php");
}

// Default Values
$user_name  = 'Guest';
$user_role  = 'User';
$photo_path = "../Assets/Files/Admin/default.png";
$logout_page = "../Guest/Login.php";

// Identify which module is logged in
if(isset($_SESSION['admin_id'])) {
    $uid = $_SESSION['admin_id'];
    $user_role = 'Administrator';
    $logout_page = "../Guest/Login.php";
    $qry = "SELECT admin_name AS name, admin_photo AS photo FROM tbl_admin WHERE admin_id='$uid'";
    $res = $con->query($qry);
    if($res && $row = $res->fetch_assoc()){
        $user_name = $row['name'];
        $photo = $row['photo'] ?: 'default.png';
        $photo_path = "../Assets/Files/Admin/" . $photo;
    }

} elseif(isset($_SESSION['hod_id'])) {
    $uid = $_SESSION['hod_id'];
    $user_role = 'Head of Department';
    $logout_page = "../HOD/Login.php";
    $qry = "SELECT teacher_name AS name, teacher_photo AS photo FROM tbl_teacher WHERE teacher_id='$uid'";
    $res = $con->query($qry);
    if($res && $row = $res->fetch_assoc()){
        $user_name = $row['name'];
        $photo = $row['photo'] ?: 'default.png';
        $photo_path = "../Assets/Files/Teacher/" . $photo;
    }

} elseif(isset($_SESSION['teacher_id'])) {
    $uid = $_SESSION['teacher_id'];
    $user_role = 'Teacher';
    $logout_page = "../Teacher/Login.php";
    $qry = "SELECT teacher_name AS name, teacher_photo AS photo FROM tbl_teacher WHERE teacher_id='$uid'";
    $res = $con->query($qry);
    if($res && $row = $res->fetch_assoc()){
        $user_name = $row['name'];
        $photo = $row['photo'] ?: 'default.png';
        $photo_path = "../Assets/Files/Teacher/" . $photo;
    }

} elseif(isset($_SESSION['student_id'])) {
    $uid = $_SESSION['student_id'];
    $user_role = 'Student';
    $logout_page = "../Student/Login.php";
    $qry = "SELECT student_name AS name, student_photo AS photo FROM tbl_student WHERE student_id='$uid'";
    $res = $con->query($qry);
    if($res && $row = $res->fetch_assoc()){
        $user_name = $row['name'];
        $photo = $row['photo'] ?: 'default.png';
        $photo_path = "../Assets/Files/Student/" . $photo;
    }
}

// fallback if image not found
if(!file_exists($photo_path)) {
    $photo_path = "../Assets/Files/Admin/default.png";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Campus Connect</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../Assets/CSS/universal.css">
</head>
<body>

<div class="animated-gradient-bg"></div>
<div id="particles-background"></div>

<header class="main-header">
    <div class="header-left">
        <button class="menu-toggle"><i class="fas fa-bars"></i></button>
        <div class="header-title">
            <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
            <?php if(isset($breadcrumb)): ?>
                <div class="breadcrumb"><?php echo $breadcrumb; ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="header-right">
        
        <!-- User Profile Trigger -->
        <div class="user-profile" id="profileTrigger">
            <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="User" class="user-avatar">
            <div class="user-info">
                <h4><?php echo htmlspecialchars($user_name); ?></h4>
                <p><?php echo htmlspecialchars($user_role); ?></p>
            </div>
            <i class="fas fa-chevron-down"></i>
        </div>
    </div>
</header>

<!-- Profile Popup Panel -->
<div class="profile-popup" id="profilePopup">
    <div class="profile-popup-content">
        <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="User" class="popup-avatar">
        <h3><?php echo htmlspecialchars($user_name); ?></h3>
        <p><?php echo htmlspecialchars($user_role); ?></p>
        <button class="logout-btn" onclick="logoutUser()">Logout</button>
    </div>
</div>

<script>
const trigger = document.getElementById('profileTrigger');
const popup = document.getElementById('profilePopup');
let open = false;

// Toggle profile popup
trigger.addEventListener('click', () => {
    popup.classList.toggle('active');
    open = !open;
});

// Close when clicking outside
window.addEventListener('click', (e) => {
    if(open && !trigger.contains(e.target) && !popup.contains(e.target)){
        popup.classList.remove('active');
        open = false;
    }
});

// Logout function

function logoutUser() {
    if(confirm("Are you sure you want to logout?")) {
        window.location.href = "../Logout.php";
    }
}

</script>

<style>
/* ===== Profile Popup Styling ===== */
.profile-popup {
    position: fixed;
    top: 70px;
    right: 20px;
    width: 260px;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border-glass);
    border-radius: 15px;
    backdrop-filter: blur(10px);
    box-shadow: 0 5px 25px rgba(0,0,0,0.3);
    padding: 1rem;
    text-align: center;
    transform: translateY(-15px);
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s ease;
    z-index: 999;
}
.profile-popup.active {
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto;
}

.profile-popup-content .popup-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 2px solid var(--gradient-1);
    object-fit: cover;
    box-shadow: 0 0 10px rgba(99,102,241,0.4);
}
.profile-popup-content h3 {
    color: var(--text-primary);
    margin-top: 0.6rem;
    font-size: 1.1rem;
}
.profile-popup-content p {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

/* Logout Button */
.logout-btn {
    background: #ff4b5c;
    border: none;
    color: white;
    font-weight: 600;
    border-radius: 8px;
    padding: 0.6rem 1rem;
    cursor: pointer;
    transition: 0.3s ease;
    width: 100%;
}
.logout-btn:hover {
    background: #e04352;
    box-shadow: 0 0 10px rgba(255,75,92,0.5);
}

/* Avatar Alignment in Header */
.user-profile {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    cursor: pointer;
}
.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--gradient-1);
}
.user-info h4 {
    margin: 0;
    color: var(--text-primary);
    font-size: 0.95rem;
}
.user-info p {
    margin: 0;
    font-size: 0.8rem;
    color: var(--text-secondary);
}
</style>