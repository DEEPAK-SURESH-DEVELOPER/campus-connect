<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($con)) {
    include("../Assets/Connection/Connection.php");
}

/* --------------------------
   TEACHER USER DETAILS
--------------------------- */

$user_name  = 'Teacher';
$user_role  = 'Teacher';
$photo_path = "../Assets/Files/Teacher/default.png";

if(isset($_SESSION['teacher_id'])) {
    $uid = $_SESSION['teacher_id'];
    $qry = "SELECT teacher_name AS name, teacher_photo AS photo 
            FROM tbl_teacher WHERE teacher_id='$uid'";
    $res = $con->query($qry);
    if($res && $row = $res->fetch_assoc()) {
        $user_name = $row['name'];
        $photo     = $row['photo'] ?: 'default.png';
        $photo_path = "../Assets/Files/Teacher/" . $photo;
    }
}

if(!file_exists($photo_path)) {
    $photo_path = "../Assets/Files/Teacher/default.png";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo ($page_title ?? ''); ?> Teacher - Campus Connect</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../Assets/CSS/universal.css">
</head>
<body>

<!-- ===========================
     CUSTOM LOGOUT CONFIRM BOX
=========================== -->
<div id="teacherConfirmOverlay" class="custom-confirm-overlay" style="display:none;">
    <div class="custom-confirm-box">
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to logout?</p>

        <div class="confirm-buttons">
            <button class="btn-cancel" onclick="closeTeacherConfirm()">Cancel</button>
            <button class="btn-logout" onclick="proceedTeacherLogout()">Yes, Logout</button>
        </div>
    </div>
</div>
<!-- =========================== -->

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

        <div class="user-profile" id="profileTrigger">
            <img src="<?php echo htmlspecialchars($photo_path); ?>" class="user-avatar">

            <div class="user-info">
                <h4><?php echo htmlspecialchars($user_name); ?></h4>
                <p><?php echo htmlspecialchars($user_role); ?></p>
            </div>

            <i class="fas fa-chevron-down"></i>
        </div>
    </div>

</header>

<!-- Profile Dropdown -->
<div class="profile-popup" id="profilePopup">
    <div class="profile-popup-content">
        <img src="<?php echo htmlspecialchars($photo_path); ?>" class="popup-avatar">
        <h3><?php echo htmlspecialchars($user_name); ?></h3>
        <p><?php echo htmlspecialchars($user_role); ?></p>

        <button class="logout-btn" onclick="teacherLogoutPopup()">Logout</button>
    </div>
</div>


<script>
const trigger = document.getElementById('profileTrigger');
const popup   = document.getElementById('profilePopup');
let open = false;

// Toggle popup
trigger.addEventListener('click', () => {
    popup.classList.toggle('active');
    open = !open;
});

// Close popup on outside click
window.addEventListener('click', (e) => {
    if(open && !trigger.contains(e.target) && !popup.contains(e.target)) {
        popup.classList.remove('active');
        open = false;
    }
});

/* ===========================
      TEACHER LOGOUT MODAL
=========================== */

function teacherLogoutPopup() {
    document.getElementById("teacherConfirmOverlay").style.display = "flex";
}

function closeTeacherConfirm() {
    document.getElementById("teacherConfirmOverlay").style.display = "none";
}

function proceedTeacherLogout() {
    window.location.href = "../Logout.php";
}
</script>

<style>
/* ===== SAME CUSTOM CONFIRM BOX FROM ADMIN ===== */

.custom-confirm-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.55);
    backdrop-filter: blur(4px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 100000;
}

.custom-confirm-box {
    background: linear-gradient(135deg, #0a1229, #101c39);
    border-left: 5px solid #1e90ff;
    padding: 25px;
    border-radius: 15px;
    width: 330px;
    color: #dce7ff;
    text-align: center;
    box-shadow: 0 0 25px rgba(30,144,255,0.5);
    animation: popIn 0.3s ease-out;
}

.custom-confirm-box h3 {
    margin-bottom: 10px;
    font-size: 1.3rem;
    color: #ffffff;
}

.custom-confirm-box p {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 20px;
}

.confirm-buttons {
    display: flex;
    justify-content: space-between;
}

.btn-cancel {
    background: #444;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    color: #fff;
    cursor: pointer;
    transition: 0.3s ease;
}
.btn-cancel:hover {
    background: #555;
}

.btn-logout {
    background: #ff1744;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    color: #fff;
    cursor: pointer;
    transition: 0.3s ease;
    box-shadow: 0 0 10px rgba(255,23,68,0.5);
}
.btn-logout:hover {
    background: #e31339;
    box-shadow: 0 0 15px rgba(255,23,68,0.8);
}

@keyframes popIn {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

/* ===== DROPDOWN MATCHED WITH ADMIN ===== */

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

.popup-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 2px solid var(--gradient-1);
    object-fit: cover;
    box-shadow: 0 0 10px rgba(99,102,241,0.4);
}

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
</style>

</body>
</html>
