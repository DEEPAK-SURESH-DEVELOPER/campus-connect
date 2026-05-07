<?php
include("../Includes/HODHeader.php"); 
if (!isset($_SESSION['is_hod']) || $_SESSION['is_hod'] !== true) {
    echo "<script>alert('Access denied!'); window.location='../Guest/Login.php';</script>";
    exit;
}
include("../Includes/HODSidebar.php"); 
// Validate Teacher ID
if(!isset($_GET['teacher_id'])) {
    header("location: TeacherList.php");
    exit;
}

$teacher_id = (int)$_GET['teacher_id'];

// Fetch teacher details
$teacherQry = "
    SELECT t.*, d.designation_name 
    FROM tbl_teacher t
    JOIN tbl_designation d ON t.designation_id = d.designation_id
    WHERE t.teacher_id = '$teacher_id'
";
$teacherRes = mysqli_query($con, $teacherQry);
if(mysqli_num_rows($teacherRes) == 0){
    header("location: TeacherList.php");
    exit;
}

$teacher = mysqli_fetch_assoc($teacherRes);

// Photo handling
$photo = $teacher['teacher_photo'];
$photoPath = "../Assets/Files/Teacher/".$photo;
if(empty($photo) || !file_exists($photoPath)){
    $photoPath = "../Assets/Images/default.png";
}

// Messages
$success = $err = "";

// Update designation
if(isset($_POST['btn_update'])){
    $desig = (int)$_POST['designation_id'];
    if($desig > 0){
        mysqli_query($con,"UPDATE tbl_teacher SET designation_id='$desig' WHERE teacher_id='$teacher_id'");
        $success = "Designation updated successfully!";
        header("Refresh:0");
    } else {
        $err = "Please select a valid designation.";
    }
}

// Fetch all designations
$designationRes = mysqli_query($con,"SELECT * FROM tbl_designation");
?>
<!DOCTYPE html>
<html>
<head>
<title>Teacher Profile</title>

<link rel="stylesheet" href="../Assets/CSS/universal.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

/* -------------------------
   PERFECT OVERRIDES
--------------------------- */

/* MAIN CONTAINER */
#teacherProfileBox {
    display: flex !important;
    flex-direction: row !important;
    align-items: flex-start !important;
    gap: 2.5rem !important;
    width: 100% !important;
    padding-top: 1rem !important;
}

/* PROFILE PHOTO */
#teacherPhoto {
    width: 170px !important;
    height: 170px !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    border: 4px solid var(--gradient-1) !important;
    flex-shrink: 0 !important;
    box-shadow: 0 0 22px rgba(102,126,234,0.45) !important;
}
#teacherPhoto img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}

/* RIGHT INFO */
#teacherInfo {
    flex: 1 !important;
    min-width: 350px !important;
}

/* TEACHER NAME */
#teacherInfo h2 {
    margin: 0 0 .8rem 0 !important;
    font-size: 1.7rem !important;
    text-align: left !important;
}

/* INFO TEXT */
#teacherInfo p {
    text-align: left !important;
    margin: 4px 0 !important;
}
#teacherInfo strong {
    color: var(--gradient-1) !important;
}

/* DESIGNATION FORM */
#designationForm {
    margin-top: 1.8rem !important;
    max-width: 350px !important;
}

/* LABEL */
#designationForm label {
    display: block !important;
    margin-bottom: .35rem !important;
    text-align: left !important;
    color: var(--text-secondary) !important;
}

/* DROPDOWN */
#designationForm select {
    width: 100% !important;
    padding: .85rem !important;
    border-radius: 10px !important;
    background: rgba(255,255,255,.08) !important;
    border: 1px solid var(--border-glass) !important;
    color: var(--text-primary) !important;
    backdrop-filter: blur(6px) !important;
}
#designationForm select option {
    background: rgb(12,13,36) !important;
    color: white !important;
}

/* BUTTON */
.update-btn {
    margin-top: 1rem !important;
    width: 230px !important;
    padding: .8rem !important;
    font-size: .95rem !important;
}

/* MOBILE */
@media(max-width: 768px){
    #teacherProfileBox {
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
    }
    #teacherInfo p,
    #teacherInfo h2,
    #designationForm label {
        text-align: center !important;
    }
    #designationForm {
        margin-left: auto !important;
        margin-right: auto !important;
    }
    .update-btn {
        width: 100% !important;
    }
}

</style>

</head>
<body>
<main class="main-content">

<div class="glass-card profile-card">

    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user"></i> Teacher Profile</h3>
        <button class="btn-outline" onclick="location.href='TeacherList.php'">← Back</button>
    </div>

    <!-- FIXED WRAPPER -->
    <div id="teacherProfileBox">

        <!-- PHOTO -->
        <div id="teacherPhoto">
            <img src="<?= $photoPath ?>">
        </div>

        <!-- INFO -->
        <div id="teacherInfo">

            <?php if($err){ ?>
                <div class="toast-notification error">
                    <i class="fa fa-exclamation-circle"></i> <?= $err ?>
                </div>
            <?php } ?>

            <?php if($success){ ?>
                <div class="toast-notification success">
                    <i class="fa fa-check-circle"></i> <?= $success ?>
                </div>
            <?php } ?>

            <h2 class="text-gradient"><?= $teacher['teacher_name'] ?></h2>

            <p><strong>Gender:</strong> <?= $teacher['teacher_gender'] ?></p>
            <p><strong>Email:</strong> <?= $teacher['teacher_email'] ?></p>
            <p><strong>Contact:</strong> <?= $teacher['teacher_contact'] ?></p>
            <p><strong>Address:</strong> <?= $teacher['teacher_address'] ?></p>

            <!-- DESIGNATION UPDATE -->
            <form method="post" id="designationForm">

                <label><strong>Designation:</strong></label>

                <select name="designation_id" required>
                    <option value="">-- Select Designation --</option>
                    <?php while($d = mysqli_fetch_assoc($designationRes)){ ?>
                        <option value="<?= $d['designation_id'] ?>"
                            <?= ($teacher['designation_id'] == $d['designation_id']) ? "selected" : "" ?>>
                            <?= $d['designation_name'] ?>
                        </option>
                    <?php } ?>
                </select>

                <button name="btn_update" class="btn-primary update-btn">
                    <i class="fa fa-save"></i> Update Designation
                </button>

            </form>

        </div>
    </div>
</div>

</main>

<script src="../Assets/JS/universal.js"></script>
</body>
</html>
