<?php
include("../Includes/StudentHeader.php");
if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('Please login first'); window.location='../Guest/Login.php';</script>";
    exit;
}

include("../Includes/StudentSidebar.php");
$student_id = $_SESSION['student_id'];

// Fetch class_id and department_id of student
$qry = $con->prepare("
    SELECT c.class_id, co.department_id 
    FROM tbl_student s 
    INNER JOIN tbl_class c ON s.class_id = c.class_id
    INNER JOIN tbl_course co ON c.course_id = co.course_id
    WHERE s.student_id = ?");
$qry->bind_param("i", $student_id);
$qry->execute();
$res = $qry->get_result()->fetch_assoc();
$class_id = $res['class_id'];
$department_id = $res['department_id'];

$message = "";

if (isset($_POST['btn_submit'])) {
    $target_type = $_POST['target_type'];
    $subject = trim($_POST['subject']);
    $details = trim($_POST['details']);
    $is_anonymous = isset($_POST['anonymous']) ? 1 : 0;

    $target_id = null;

    // Routing logic
    if ($target_type == 'Class Teacher') {
        $stmt = $con->prepare("SELECT teacher_id FROM tbl_class WHERE class_id=?");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $target_id = $stmt->get_result()->fetch_assoc()['teacher_id'];
    } elseif ($target_type == 'HOD') {
        $stmt = $con->prepare("SELECT hod_teacher_id FROM tbl_department WHERE department_id=?");
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $target_id = $stmt->get_result()->fetch_assoc()['hod_teacher_id'];
    } elseif ($target_type == 'Admin') {
        $stmt = $con->prepare("SELECT admin_id FROM tbl_admin LIMIT 1");
        $stmt->execute();
        $target_id = $stmt->get_result()->fetch_assoc()['admin_id'];
    }

    $ins = $con->prepare("
        INSERT INTO tbl_complaint 
        (sender_type, sender_id, target_type, target_id, department_id, class_id, complaint_subject, complaint_details, is_anonymous) 
        VALUES ('Student', ?, ?, ?, ?, ?, ?, ?, ?)");
    $ins->bind_param("isiiissi", $student_id, $target_type, $target_id, $department_id, $class_id, $subject, $details, $is_anonymous);

    if ($ins->execute()) {
        echo "<script>
                alert('✅ Complaint sent successfully!');
                window.location = 'StudentViewComplaint.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('❌ Error sending complaint');</script>";
    }
}

/* --- Universal page info --- */
$page_title = "Submit Complaint";
$breadcrumb = '<span>Home</span> <i class="fas fa-chevron-right"></i> <span>Complaint</span>';
?>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Header -->
    <div class="glass-card fade-in text-center">
        <h2 class="text-gradient"><i class="fas fa-comment-dots"></i> Submit Complaint</h2>
        <p style="color: var(--text-secondary);">Raise an issue or feedback directly to your Class Teacher, HOD, or Admin</p>
    </div>

    <!-- Complaint Form -->
    <div class="glass-card slide-up" style="max-width: 700px; margin: 0 auto;">
        <form method="post">
            
            <!-- Recipient -->
            <div class="form-group">
                <label class="form-label"><i class="fas fa-user-tie"></i> Send Complaint To</label>
                <select name="target_type" class="form-control" required>
                    <option value="">-- Select Recipient --</option>
                    <option value="Class Teacher">Class Teacher</option>
                    <option value="HOD">HOD</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>

            <!-- Subject -->
            <div class="form-group">
                <label class="form-label"><i class="fas fa-heading"></i> Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="Enter complaint subject" required>
            </div>

            <!-- Details -->
            <div class="form-group">
                <label class="form-label"><i class="fas fa-align-left"></i> Details</label>
                <textarea name="details" class="form-control" rows="5" placeholder="Describe your complaint or issue" required></textarea>
            </div>

            <!-- Anonymous Checkbox -->
            <div class="form-group d-flex align-center gap-2">
                <input type="checkbox" id="anonymous" name="anonymous" style="width:18px;height:18px;">
                <label for="anonymous" style="color: var(--text-secondary); cursor:pointer;">
                    Submit Anonymously (Your name will be hidden)
                </label>
            </div>

            <!-- Submit -->
            <div class="text-center mt-4">
                <button type="submit" name="btn_submit" class="btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Complaint
                </button>
            </div>

        </form>
    </div>

    <!-- Back Button -->
    <div class="text-center mt-4">
      <!--  <a href="StudentHome.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Back to Home</a> -->
    </div>

</div>

<!-- INTERNAL STYLE OVERRIDES -->
<style>
textarea.form-control {
    resize: vertical;
    min-height: 120px;
}
select.form-control {
    background-color: rgba(255,255,255,0.05);
    color: var(--text-primary);
    border-radius: 12px;
}
option {
    background-color: var(--secondary-bg);
    color: var(--text-primary);
}
</style>

<!-- UNIVERSAL JS -->
<script src="../Assets/JS/universal.js"></script>
</body>
</html>
